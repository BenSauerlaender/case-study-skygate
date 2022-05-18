const { request, expect } = require("../config");
const { makeSuite, notAllowed } = require("../helper");

/**
 * Tests for the /users/{id}/emailChange/{code} route
 */
makeSuite(
  ["3roles", "2Users", "1EmailChangeRequest"],
  "/users/{userID}/verify/{code}",
  {
    PUT: notAllowed(),
    DELETE: notAllowed(),
    PATCH: notAllowed(),
    POST: notAllowed(),
    GET: {
      "user not exists": () => {
        it("makes api call", async () => {
          this.response = await request.get("/users/5/emailChange/123");
        });

        it("returns Bad Request", async () => {
          expect(this.response.statusCode).to.eql(400);
        });

        it("includes a code", async () => {
          expect(this.response.body["code"]).to.eql(201);
        });

        it("includes a message", async () => {
          expect(this.response.body["msg"]).to.include("The user not exists");
        });
      },
      "No open change request": () => {
        it("makes api call", async () => {
          this.response = await request.get("/users/2/emailChange/123");
        });

        it("returns Bad Request", async () => {
          expect(this.response.statusCode).to.eql(400);
        });

        it("includes a code", async () => {
          expect(this.response.body["code"]).to.eql(210);
        });

        it("includes a message", async () => {
          expect(this.response.body["msg"]).to.include(
            "The user has no open email change request"
          );
        });
      },
      "with invalid code": () => {
        it("makes api call", async () => {
          this.response = await request.get("/users/1/emailChange/123");
        });

        it("returns Bad Request", async () => {
          expect(this.response.statusCode).to.eql(400);
        });

        it("includes a code", async () => {
          expect(this.response.body["code"]).to.eql(211);
        });

        it("includes a message", async () => {
          expect(this.response.body["msg"]).to.include(
            "The verification code is invalid"
          );
        });
      },
      "with valid code": () => {
        it("makes api call", async () => {
          this.response = await request.get("/users/1/emailChange/1234567899");
        });

        it("returns redirection ", async () => {
          expect(this.response.statusCode).to.eql(303);
        });
        it("includes the correct Location", async () => {
          expect(this.response.headers.location).eql(
            `${process.env.API_PROD_DOMAIN}/email-changed`
          );
        });
      },
    },
  }
);
