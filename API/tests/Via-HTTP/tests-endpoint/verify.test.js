const { request, expect } = require("../config");
const { makeSuite, notAllowed } = require("../helper");
const { getEmail } = require("../emailHelper.js");

/**
 * Tests for the /users/{id}/verify/{code} route
 */
makeSuite(["3roles", "1unverifiedUser"], "/users/{userID}/verify/{code}", {
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
        expect(this.response.body["code"]).to.eql(201);
      });

      it("includes a message", async () => {
        expect(this.response.body["msg"]).to.include("The user not exists");
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
        expect(this.response.body["code"]).to.eql(210);
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
        this.response = await request.get("/users/2/verify/0123456789");
      });

      it("returns OK ", async () => {
        expect(this.response.statusCode).to.eql(200);
      });

      it("includes a body", async () => {
        expect(this.response.body).to.eql(
          `<p>Your Account has been verified. Please <a href="${process.env.PROD_DOMAIN}/login">log in</a>!</p>`
        );
      });
    },
  },
});
