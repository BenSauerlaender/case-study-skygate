<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

use Objects\Interfaces\RequestInterface;
use Controller\Interfaces\AuthenticationControllerInterface;
use Controller\Interfaces\UserControllerInterface;
use DbAccessors\Interfaces\RefreshTokenAccessorInterface;
use DbAccessors\Interfaces\RoleAccessorInterface;
use DbAccessors\Interfaces\UserQueryInterface;
use Exceptions\DBExceptions\FieldNotFoundExceptions\EcrNotFoundException;
use Exceptions\DBExceptions\FieldNotFoundExceptions\UserNotFoundException;
use Exceptions\TokenExceptions\ExpiredTokenException;
use Exceptions\TokenExceptions\InvalidTokenException;
use Exceptions\ValidationExceptions\InvalidPropertyException;
use Exceptions\ValidationExceptions\MissingPropertiesException;
use Objects\Cookies\RefreshTokenCookie;
use Objects\Responses\ClientErrorResponses\BadRequestResponses\BadRequestResponse;
use Objects\Responses\ClientErrorResponses\BadRequestResponses\InvalidPropertyResponse;
use Objects\Responses\ClientErrorResponses\BadRequestResponses\InvalidQueryResponse;
use Objects\Responses\ClientErrorResponses\BadRequestResponses\MissingPropertyResponse;
use Objects\Responses\ClientErrorResponses\BadRequestResponses\UserNotFoundResponse;
use Objects\Responses\SuccessfulResponses\CreatedResponse;
use Objects\Responses\SuccessfulResponses\DataResponse;
use Objects\Responses\SuccessfulResponses\NoContentResponse;
use Objects\Responses\SuccessfulResponses\SetCookieResponse;
use Utilities\MailSender;

/**
 * Class to hold the api-route definitions.
 */
class Routes
{
    /**
     * Returns a list of all rote definitions in an convenient array
     * 
     * @return array<string,array<string,array<string,array<string>|bool|Closure>>>    $routes = [
     *      <route_path> => [
     *          <route_method> => [
     *              "params"        => (array<string>)  A list of parameters to set in the path-placeholders ({x})
     *              "requireAuth"   => (bool)           True if the route need authorization to access.
     *              "permissions"   => (array<string>)  A list of permission-strings. Take only an effect if requireAuth is set to true.
     *              "function"      => (Closure)        The closure to execute to process the Request and return a Response.
     *          ]
     *      ]
     * ]
     * 
     */
    public static function getRoutes(): array
    {
        return [
            "/register" => [
                "POST" => [ //To register a new user
                    "params" => [],
                    "requireAuth" => false,
                    "permissions" => [],
                    "function" => function (RequestInterface $req, array $params) {
                        /** @var UserControllerInterface */
                        $uc = $this->controller["user"];

                        $properties = $req->getBody();
                        $properties["role"] = "user";

                        try {
                            $ret = $uc->createUser($properties);

                            MailSender::sendVerificationRequest($properties["email"], $properties["name"], $ret["id"], $ret["verificationCode"]);

                            return new CreatedResponse();
                        } catch (MissingPropertiesException $e) {
                            return new MissingPropertyResponse($e->getMissing());
                        } catch (InvalidPropertyException $e) {
                            return new InvalidPropertyResponse($e->getInvalidProperties());
                        }
                    }
                ]
            ],
            "/users/{x}/verify/{x}" => [
                "GET" => [ //To verify a new user
                    "params" => ["userID", "verificationCode"],
                    "requireAuth" => false,
                    "permissions" => [],
                    "function" => function (RequestInterface $req, array $params) {
                        /** @var UserControllerInterface */
                        $uc = $this->controller["user"];

                        try {
                            if ($uc->verifyUser($params["userID"], "{$params["verificationCode"]}")) {
                                return new NoContentResponse();
                            } else {
                                return new BadRequestResponse("The verification code is invalid.", 211);
                            }
                        } catch (BadMethodCallException $e) {
                            return new BadRequestResponse("The user is already verified.", 210);
                        } catch (UserNotFoundException $e) {
                            return new UserNotFoundResponse($e);
                        }
                    }
                ]
            ],
            "/login" => [
                "POST" => [ //To get a refreshToken
                    "params" => [],
                    "requireAuth" => false,
                    "permissions" => [],
                    "function" => function (RequestInterface $req, array $params) {

                        $properties = $req->getBody();

                        $missingProperties = array_diff_key(array_flip(["email", "password"]), $properties ?? []);

                        if (sizeOf($missingProperties) !== 0) {
                            return new MissingPropertyResponse(array_keys($missingProperties));
                        }

                        $email = strtolower($properties["email"] ?? "");
                        $pass = $properties["password"] ?? "";

                        /** @var UserControllerInterface */
                        $uc = $this->controller["user"];

                        try {
                            if ($uc->checkEmailPassword($email, $pass)) {
                                /** @var AuthenticationControllerInterface */
                                $auth = $this->controller["auth"];
                                $token = $auth->getNewRefreshToken($email);
                                return new SetCookieResponse(new RefreshTokenCookie($token));
                            } else {
                                return new BadRequestResponse("The password is incorrect", 215);
                            }
                        } catch (UserNotFoundException $e) {
                            return new UserNotFoundResponse($e);
                        }
                    }
                ]
            ],
            "/token" => [
                "GET" => [ //To get a new accessToken
                    "params" => [],
                    "requireAuth" => false,
                    "permissions" => [],
                    "function" => function (RequestInterface $req, array $params) {

                        $refreshJWT = $req->getCookie("skygatecasestudy.refreshtoken");
                        if (is_null($refreshJWT)) {
                            return new BadRequestResponse("No refreshToken provided! POST /login to get one.", 301);
                        }

                        /** @var AuthenticationControllerInterface */
                        $auth = $this->controller["auth"];

                        try {
                            $accessToken = $auth->getNewAccessToken($refreshJWT);
                            return new DataResponse(["accessToken" => $accessToken]);
                        } catch (InvalidArgumentException $e) {
                            return new BadRequestResponse("The refreshToken is invalid!", 302, ["reason" => "NOT_VERIFIABLE"]);
                        } catch (ExpiredTokenException $e) {
                            return new BadRequestResponse("The refreshToken is invalid!", 302, ["reason" => "EXPIRED"]);
                        } catch (InvalidTokenException $e) {
                            return new BadRequestResponse("The refreshToken is invalid!", 302, ["reason" => "OLD_TOKEN"]);
                        } catch (UserNotFoundException $e) {
                            return new UserNotFoundResponse($e);
                        }
                    }
                ]
            ],
            "/users/{x}" => [
                "GET" => [ //To get user information of a single user
                    "params" => ["userID"],
                    "requireAuth" => true,
                    "permissions" => ["user:read:{userID}"],
                    "function" => function (RequestInterface $req, array $params) {
                        /** @var UserControllerInterface */
                        $uc = $this->controller["user"];

                        try {
                            $user = $uc->getUser($params["userID"]);
                            return new DataResponse($user);
                        } catch (UserNotFoundException $e) {
                            return new UserNotFoundResponse($e);
                        }
                    }
                ],
                "PUT" => [ //To update a single user
                    "params" => ["userID"],
                    "requireAuth" => true,
                    "permissions" => ["user:update:{userID}"],
                    "function" => function (RequestInterface $req, array $params) {
                        $supportedProperties = ["name" => null, "postcode" => null, "city" => null, "phone" => null];

                        /** @var UserControllerInterface */
                        $uc = $this->controller["user"];

                        $properties = array_intersect_key($req->getBody() ?? [], $supportedProperties);

                        if (sizeOf($properties) === 0) return new BadRequestResponse("No supported properties provided.", 101, ["supportedProperties" => array_keys($supportedProperties)]);

                        try {
                            $uc->updateUser($params["userID"], $properties);
                            return new DataResponse(["updated" => $properties]);
                        } catch (UserNotFoundException $e) {
                            return new UserNotFoundResponse($e);
                        } catch (InvalidPropertyException $e) {
                            return new InvalidPropertyResponse($e->getInvalidProperties());
                        }
                    }
                ],
                "DELETE" => [ //To delete a single user
                    "params" => ["userID"],
                    "requireAuth" => true,
                    "permissions" => ["user:delete:{userID}"],
                    "function" => function (RequestInterface $req, array $params) {
                        /** @var UserControllerInterface */
                        $uc = $this->controller["user"];

                        try {
                            $uc->deleteUser($params["userID"]);
                            return new NoContentResponse();
                        } catch (UserNotFoundException $e) {
                            return new UserNotFoundResponse($e);
                        }
                    }
                ]
            ],
            "/users/{x}/password" => [ //To change a users password
                "PUT" => [
                    "params" => ["userID"],
                    "requireAuth" => true,
                    "permissions" => ["user:update:{userID}"],
                    "function" => function (RequestInterface $req, array $params) {

                        $properties = $req->getBody();

                        $missingProperties = array_diff_key(array_flip(["oldPassword", "newPassword"]), $properties ?? []);

                        if (sizeOf($missingProperties) !== 0) {
                            return new MissingPropertyResponse(array_keys($missingProperties));
                        }
                        /** @var UserControllerInterface */
                        $uc = $this->controller["user"];

                        try {
                            if ($uc->updateUsersPassword($params["userID"], $properties["newPassword"], $properties["oldPassword"])) {
                                /** @var RefreshTokenAccessorInterface*/
                                $acc = $this->accessors["refreshToken"];
                                $acc->increaseCount($params["userID"]);
                                return new NoContentResponse();
                            } else {
                                return new BadRequestResponse("The password is incorrect", 215);
                            }
                        } catch (UserNotFoundException $e) {
                            return new UserNotFoundResponse($e);
                        } catch (InvalidPropertyException $e) {
                            return new InvalidPropertyResponse($e->getInvalidProperties());
                        }
                    }
                ]
            ],
            "/users/{x}/emailchange" => [
                "POST" => [ //To request a users email change
                    "params" => ["userID"],
                    "requireAuth" => true,
                    "permissions" => ["user:update:{userID}"],
                    "function" => function (RequestInterface $req, array $params) {

                        $properties = $req->getBody();

                        $missingProperties = array_diff_key(array_flip(["email"]), $properties ?? []);

                        if (sizeOf($missingProperties) !== 0) {
                            return new MissingPropertyResponse($missingProperties);
                        }
                        /** @var UserControllerInterface */
                        $uc = $this->controller["user"];

                        try {
                            $code = $uc->requestUsersEmailChange($params["userID"], $properties["email"]);

                            $user = $uc->getUser($params["userID"]);

                            MailSender::sendEmailChangeVerificationRequest($properties["email"], $user["name"], $params["userID"], $code);
                            return new CreatedResponse();
                        } catch (UserNotFoundException $e) {
                            return new UserNotFoundResponse($e);
                        } catch (InvalidPropertyException $e) {
                            return new InvalidPropertyResponse($e->getInvalidProperties());
                        }
                    }
                ]
            ],
            "/users/{x}/emailchange/{x}" => [
                "GET" => [ //To verify a users email change
                    "params" => ["userID", "verificationCode"],
                    "requireAuth" => false,
                    "permissions" => [],
                    "function" => function (RequestInterface $req, array $params) {
                        /** @var UserControllerInterface */
                        $uc = $this->controller["user"];

                        try {
                            if ($uc->verifyUsersEmailChange($params["userID"], "{$params["verificationCode"]}")) {
                                /** @var RefreshTokenAccessorInterface*/
                                $acc = $this->accessors["refreshToken"];
                                $acc->increaseCount($params["userID"]);
                                return new NoContentResponse();
                            } else {
                                return new BadRequestResponse("The verification code is invalid.", 211);
                            }
                        } catch (EcrNotFoundException $e) {
                            return new BadRequestResponse("The user has no open email change request.", 212);
                        } catch (UserNotFoundException $e) {
                            return new UserNotFoundResponse($e);
                        }
                    }
                ]
            ],
            "/users/{x}/logout" => [
                "POST" => [ //To make a users refreshToken invalid
                    "params" => ["userID"],
                    "requireAuth" => true,
                    "permissions" => ["user:delete:{userID}"],
                    "function" => function (RequestInterface $req, array $params) {
                        /** @var RefreshTokenAccessorInterface*/
                        $acc = $this->accessors["refreshToken"];
                        try {
                            $acc->increaseCount($params["userID"]);
                            return new NoContentResponse();
                        } catch (UserNotFoundException $e) {
                            return new UserNotFoundResponse($e);
                        }
                    }
                ]
            ],
            "/users" => [
                "GET" => [ //To get multiple users defined by a query
                    "params" => [],
                    "requireAuth" => true,
                    "permissions" => ["user:read:{all}"],
                    "function" => function (RequestInterface $req, array $params) {

                        $queryConfig = $req->getQuery();

                        /** @var UserQueryInterface */
                        $uq = $this->accessors["userQuery"];

                        try {
                            $uq->configureByArray($queryConfig, ["page", "index"]);
                        } catch (InvalidPropertyException $e) {
                            return new InvalidQueryResponse();
                        }

                        $pagesize = $queryConfig["page"] ?? null;
                        if (!is_null($pagesize)) {
                            $index = $queryConfig["index"] ?? 0;
                            $ret = $uq->getResultsPaginated($pagesize, $index);
                        } else {
                            $ret = $uq->getResults();
                        }

                        return new DataResponse($ret);
                    }
                ]
            ],
            "/users/length" => [
                "GET" => [ //To get the number of users matching a query
                    "params" => [],
                    "requireAuth" => true,
                    "permissions" => ["user:read:{all}"],
                    "function" => function (RequestInterface $req, array $params) {
                        $queryConfig = $req->getQuery();

                        /** @var UserQueryInterface */
                        $uq = $this->accessors["userQuery"];

                        try {
                            $uq->configureByArray($queryConfig, ["page", "index"]);
                        } catch (InvalidPropertyException $e) {
                            return new InvalidQueryResponse();
                        }
                        return new DataResponse(["length" => $uq->getLength()]);
                    }
                ]
            ],
            "/roles" => [
                "GET" => [ //To get a list of available roles
                    "params" => [],
                    "requireAuth" => false,
                    "permissions" => [],
                    "function" => function (RequestInterface $req, array $params) {
                        /** @var RoleAccessorInterface*/
                        $acc = $this->accessors["role"];

                        return new DataResponse($acc->getList());
                    }
                ]
            ],
        ];
    }
}
