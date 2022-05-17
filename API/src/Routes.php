<?php

/**
 * @author Ben Sauerlaender <Ben.Sauerlaender@Student.HTW-Berlin.de>
 */

//activate strict mode
declare(strict_types=1);

namespace BenSauer\CaseStudySkygateApi;

use BadMethodCallException;
use BenSauer\CaseStudySkygateApi\ApiComponents\ApiRequests\Interfaces\ApiRequestInterface;
use BenSauer\CaseStudySkygateApi\ApiComponents\ApiResponses\BadRequestResponses\BadRequestResponse;
use BenSauer\CaseStudySkygateApi\ApiComponents\ApiResponses\BadRequestResponses\InvalidPropertyResponse;
use BenSauer\CaseStudySkygateApi\ApiComponents\ApiResponses\BadRequestResponses\MissingPropertyResponse;
use BenSauer\CaseStudySkygateApi\ApiComponents\ApiResponses\BadRequestResponses\UserNotFoundResponse;
use BenSauer\CaseStudySkygateApi\ApiComponents\ApiResponses\CreatedResponse;
use BenSauer\CaseStudySkygateApi\ApiComponents\ApiResponses\DataResponse;
use BenSauer\CaseStudySkygateApi\ApiComponents\ApiResponses\RedirectionResponse;
use BenSauer\CaseStudySkygateApi\ApiComponents\ApiResponses\RefreshTokenCookie;
use BenSauer\CaseStudySkygateApi\ApiComponents\ApiResponses\SetCookieResponse;
use BenSauer\CaseStudySkygateApi\Controller\Interfaces\AuthenticationControllerInterface;
use BenSauer\CaseStudySkygateApi\Controller\Interfaces\UserControllerInterface;
use BenSauer\CaseStudySkygateApi\DbAccessors\Interfaces\UserAccessorInterface;
use BenSauer\CaseStudySkygateApi\Exceptions\DBExceptions\FieldNotFoundExceptions\UserNotFoundException;
use BenSauer\CaseStudySkygateApi\Exceptions\ValidationExceptions\InvalidFieldException;
use BenSauer\CaseStudySkygateApi\Exceptions\ValidationExceptions\RequiredFieldException;
use BenSauer\CaseStudySkygateApi\Utilities\MailUtilities;

class Routes
{
    public static function getRoutes(): array
    {
        return [
            "/register" => [
                "POST" => [
                    "ids" => [],
                    "requireAuth" => false,
                    "permissions" => [],
                    "function" => function (ApiRequestInterface $req, array $ids) {
                        /** @var UserControllerInterface */
                        $uc = $this->controller["user"];

                        $fields = $req->getBody();
                        $fields["role"] = "user";

                        try {
                            $ret = $uc->createUser($fields);

                            MailUtilities::sendVerificationRequest($fields["email"], $fields["name"], $ret["id"], $ret["verificationCode"]);

                            return new CreatedResponse();
                        } catch (RequiredFieldException $e) {
                            return new MissingPropertyResponse($e->getMissing());
                        } catch (InvalidFieldException $e) {
                            return new InvalidPropertyResponse($e->getInvalidField());
                        }
                    }
                ]
            ],
            "/users/{id}/verify/{id}" => [
                "GET" => [
                    "ids" => ["userID", "verificationCode"],
                    "requireAuth" => false,
                    "permissions" => [],
                    "function" => function (ApiRequestInterface $req, array $ids) {
                        /** @var UserControllerInterface */
                        $uc = $this->controller["user"];

                        try {
                            if ($uc->verifyUser($ids["userID"], "{$ids["verificationCode"]}")) {
                                return new RedirectionResponse("{$_ENV['API_PROD_DOMAIN']}/login");
                            } else {
                                return new BadRequestResponse("The verification code is invalid.", 211);
                            }
                        } catch (BadMethodCallException $e) {
                            return new BadRequestResponse("The user is already verified.", 210);
                        } catch (UserNotFoundException $e) {
                            return new UserNotFoundResponse();
                        }
                    }
                ]
            ],
            "/login" => [
                "POST" => [
                    "ids" => [],
                    "requireAuth" => false,
                    "permissions" => [],
                    "function" => function (ApiRequestInterface $req, array $ids) {

                        $fields = $req->getBody();

                        $missingFields = array_diff_key(["email" => "email", "password" => "password"], $fields ?? []);

                        $email = strtolower($fields["email"] ?? "");
                        $pass = $fields["password"] ?? "";

                        if (sizeOf($missingFields) !== 0) {
                            return new MissingPropertyResponse($missingFields);
                        }

                        /** @var UserControllerInterface */
                        $uc = $this->controller["user"];

                        try {
                            if ($uc->checkEmailPassword($email, $pass)) {
                                /** @var AuthenticationControllerInterface */
                                $auth = $this->auth;
                                $token = $auth->getNewRefreshToken($email);
                                return new SetCookieResponse(new RefreshTokenCookie($token));
                            } else {
                                return new BadRequestResponse("The password is incorrect", 215);
                            }
                        } catch (UserNotFoundException $e) {
                            return new UserNotFoundResponse();
                        }
                    }
                ]
            ],
            "/users/{id}" => [
                "GET" => [
                    "ids" => ["userID"],
                    "requireAuth" => true,
                    "permissions" => ["user:read:these"],
                    "function" => function (ApiRequestInterface $req, array $ids) {
                        return null;
                    }
                ]
            ]
        ];
    }
}
