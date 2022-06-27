const { request, expect } = require("../config");
const { makeSuite, notAllowed } = require("../helper");

/**
 * Tests for the /users/{x}/email-change-verify route
 */
makeSuite(
  ["3roles", "2Users", "1EmailChangeRequest"],
  "/users/{userID}/email-change-verify",
  {
    PUT: notAllowed(),
    DELETE: notAllowed(),
    PATCH: notAllowed(),
    GET: notAllowed(),
    POST: {
      "no code provided": () => {
        it("makes api call", async () => {
          this.response = await request
            .post("/users/5/email-change-verify")
            .send({});
        });

        it("returns Bad Request", async () => {
          expect(this.response.statusCode).to.eql(400);
        });

        it("includes a code", async () => {
          expect(this.response.body["errorCode"]).to.eql(101);
        });

        it("includes a message", async () => {
          expect(this.response.body["msg"]).to.include("require");
        });

        it("includes a list of required properties", async () => {
          expect(this.response.body["missingProperties"]).to.contains.members([
            "code",
          ]);
        });
      },
      "No open change request for this user": () => {
        it("makes api call", async () => {
          this.response = await request
            .post("/users/2/email-change-verify")
            .send({ code: "123" });
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
          this.response = await request
            .post("/users/1/email-change-verify")
            .send({ code: "123" });
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
          this.response = await request
            .post("/users/1/email-change-verify")
            .send({ code: "1234567899" });
        });

        it("returns no content ", async () => {
          expect(this.response.statusCode).to.eql(204);
        });
      },
    },
  }
);
