const { request, expect } = require("../config");
const { makeSuite, notAllowed } = require("../helper");

/**
 * Tests for the /users/{x}/emailChange/{code} route
 */
makeSuite(
  ["3roles", "2Users", "1EmailChangeRequest"],
  "/users/{userID}/emailChange/{code}",
  {
    PUT: notAllowed(),
    DELETE: notAllowed(),
    PATCH: notAllowed(),
    POST: notAllowed(),
    GET: {
      "No open change request for this user": () => {
        it("makes api call", async () => {
          this.response = await request.get("/users/2/emailChange/123");
        });

        it("returns Bad Request", async () => {
          expect(this.response.statusCode).to.eql(400);
        });

        it("includes a code", async () => {
          expect(this.response.body["errorCode"]).to.eql(212);
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
          expect(this.response.body["errorCode"]).to.eql(211);
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

        it("returns no content ", async () => {
          expect(this.response.statusCode).to.eql(201);
        });
      },
    },
  }
);
