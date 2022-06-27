# Case Study at SkyGate - API

I'm creating a simple web app as part of my internship at SkyGate internetworking GmbH

This is the Backend REST Api.

Its "plain" PHP. No framework.

A generated documentation can be found in /docs/index.html

# Mini-Documentation

- [Api Routes](#api-routes)
- [Development Environment](#development-environment)
  - [Testing](#testing)
- [Architecture](#architecture)
  - [index.php](#indexphp)
  - [ApiController](#apicontroller)
  - [Controllers](#controllers)
  - [DbAccessors](#dbaccessors)
  - [Routes](#routes-1)

<a href='http://ecotrust-canada.github.io/markdown-toc/'></a>

# Api Routes

The API provides multiple routes.
Here is a brief summary:

### POST /api/v1/register

creates a new user and sends a confirmation email

### POST /api/v1/users/<user_id>/verify

verifies the new user (link is send via confirmation email)

### POST /api/v1/login

returns a refreshToken (user need to be verified)

### GET /api/v1/token

returns a accessToken

### GET /api/v1/users/<user_id>

returns user data. (requires accessToken)

### PUT /api/v1/users/<user_id>

updates user data. (requires accessToken)

### DELETES /api/v1/users/<user_id>

deletes user. (requires accessToken)

### PUT /api/v1/users/<user_id>/password

updates user password. (requires accessToken)

### POST /api/v1/users/<user_id>/email-change

request to change the email (sends confirmation email). (requires accessToken)

### POST /api/v1/users/<user_id>/email-change-verify

confirm to change the email. (link is send via confirmation email)

### POST /api/v1/users/<user_id>/logout

makes refreshToken invalid. (so the user is forced to /login again) (requires accessToken)

### GET /api/v1/users

returns the data of multiple users. (Can be narrowed down via http query) (requires accessToken)

### GET /api/v1/users/length

returns the size of users, that would be returned with the same query by /users (requires accessToken)

### GET /api/v1/roles

returns a list of all available roles

# Development Environment

The development environment is a docker-compose (defined in /docker). <br>
It contains an mysql-database and an apache-server. <br>
The /src folder is directly mounted into the apache-server so all changes are applied instantly without restarting the environment. <br>
The environment is also used for testing. Unit/Integration/Database tests only use the mysql-db; Endpoint test also use the apache-server.

Attention: you need to set the .env first

- Start the environment

    ```
    composer start
    ```

- Stop the environment

    ```
    composer stop
    ```

- See the logs

    ```
    composer logs
    ```

## Testing

I was trying to do Test Driven Development. So there are a lot of tests. <br>

- The Unit tests have a 100% test coverage and are together with the endpoint tests the most important ones.
- Integration tests are kinda deprecated. I have they wrote only for the userController but found it redundant with endpoint-tests. So i don't have updated them.
- The Database tests only tests, that the mysql tables are created correctly.
- The endpoint tests use javascript to test the api from the outside. They test the whole functionality

<br>

- Start the Unit tests

    ```
    composer run-unit-tests
    ```

- Start the Integration tests

    ```
    composer run-integration-tests
    ```

- Start the Database tests

    ```
    composer run-database-tests
    ```

- Start the Endpoint tests (first start the dev-environment)

    ```
    composer run-endpoint-tests
    ```

- Run only a specific endpoint test file
  - Start the dev-environment
  - cd into tests/Endpoint/
  - run:

        ```
        npm run endpoint-test-only -- <relative/path/to/file>
        ```

- Create a Unit test code-coverage analyses

    ```
    composer run-unit-test-code-coverage
    ```

- Open the code-coverage analyses

    ```
    composer open-code-coverage
    ```

# Architecture

My goal was to make the architecture good maintainable, extendible and testable.

## index.php

Its the entrypoint of every request to the api.

It:

- connects to the Database
- initialize the API controller
- let the API controller process the request
- handle some exceptions
- close the database connection

## ApiController

Its the interface for the Api. Its hold together all other Controllers.

It:

- fetches an api request via http from the client
- lets the RoutingController find the correct Route
- lets the authorizationController authorize the request
- lets the route process the request (The ApiController provides access to additional controllers e.g. userController)
- sends the response via http to the client

## Controllers

Controllers are stateless classes that contain a specific set of functions. They can depend on other Controllers or DbAccessors

## DbAccessors

Are stateless classes that provide functions to interact with the Database. All sql statements are inside Accessors.

## Routes

Is a class that contains one static array of predefined routes. The define the concrete functionality of each api-route.

# Security

The api only accepts secure (HTTPS) connections

## Passwords

All passwords are stored bcrypt encrypted.

## Authorization

For most of the Api-Calls it is required to Authorize.
To makes this secure I use a similar approach as OAUTH.

### accessToken

Authorization is implemented through an JWT accessToken that holds the userID and a list of permissions. <br>
So the Api-call can be authorized without an database call and is truly stateless. (The list of permissions is defined by the users role) <br>
The accessToken is only valid for 15 minutes after that the user need to get a new one.

### refreshToken

To get an accessToken the user need a refreshToken. <br>
Its also an JWT but stored in the cookies and it can exists always only one valid refreshToken per user. <br>
To get one the user need to be log in via email and password.
