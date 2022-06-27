const { request, expect } = require("../config");
const { makeSuite, notAllowed } = require("../helper");

/**
 * Tests for the /users/{x}/verify/{code} route
 */
makeSuite(
  ["3roles", "1User", "1unverifiedUser"],
  "/users/{userID}/verify/{code}",
  {
    PUT: notAllowed(),
    DELETE: notAllowed(),
    PATCH: notAllowed(),
    POST: notAllowed(),
    GET: {
      "user not exists": () => {
        it("makes api call", async () => {
          this.response = await request.get("/users/5/verify/123");
        });

        it("returns Bad Request", async () => {
          expect(this.response.statusCode).to.eql(400);
        });

        it("includes a code", async () => {
          expect(this.response.body["errorCode"]).to.eql(201);
        });

        it("includes a message", async () => {
          expect(this.response.body["msg"]).to.include("id=5");
        });
      },
      "user is already verified": () => {
        it("makes api call", async () => {
          this.response = await request.get("/users/1/verify/123");
        });

        it("returns Bad Request", async () => {
          expect(this.response.statusCode).to.eql(400);
        });

        it("includes a code", async () => {
          expect(this.response.body["errorCode"]).to.eql(210);
        });

        it("includes a message", async () => {
          expect(this.response.body["msg"]).to.include(
            "The user is already verified"
          );
        });
      },
      "with invalid code": () => {
        it("makes api call", async () => {
          this.response = await request.get("/users/2/verify/123");
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
          this.response = await request.get("/users/2/verify/1234567899");
        });

        it("returns no Content ", async () => {
          expect(this.response.statusCode).to.eql(201);
        });
      },
    },
  }
);
