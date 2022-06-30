const { request, expect } = require("../config");
const { makeSuite, notAllowed } = require("../helper");
let jwt = require("jsonwebtoken");

/**
 * Tests for the /users/{x}/email-change-privileged route
 */
makeSuite(["3roles", "2Users"], "/users/{x}/email-change-privileged", {
  PUT: notAllowed(),
  DELETE: notAllowed(),
  PATCH: notAllowed(),
  GET: notAllowed(),
  POST: {
    "without accessToken": () => {
      it("makes api call", async () => {
        this.response = await request.post("/users/1/email-change-privileged");
      });

      it("returns Unauthorized", async () => {
        expect(this.response.statusCode).to.eql(401);
      });
    },

    "without permission": () => {
      it("makes api call", async () => {
        let token = jwt.sign(
          {
            id: 1,
            perm: "changeOwnEmail",
            exp: Math.floor(Date.now() / 1000) + 30,
          },
          process.env.ACCESS_TOKEN_SECRET
        );
        this.response = await request
          .post("/users/1/email-change-privileged")
          .set("Authorization", "Bearer " + token);
      });

      it("returns Forbidden", async () => {
        expect(this.response.statusCode).to.eql(403);
      });
    },
    "without an email": () => {
      it("makes api call", async () => {
        let token = jwt.sign(
          {
            id: 1,
            perm: "changeAllUsersEmailPrivileged",
            exp: Math.floor(Date.now() / 1000) + 30,
          },
          process.env.ACCESS_TOKEN_SECRET
        );
        this.response = await request
          .post("/users/1/email-change-privileged")
          .set("Authorization", "Bearer " + token);
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
        expect(this.response.body["missingProperties"]).to.has.keys(["email"]);
      });
    },
    "with invalid email": () => {
      it("makes api call", async () => {
        let token = jwt.sign(
          {
            id: 1,
            perm: "changeAllUsersEmailPrivileged",
            exp: Math.floor(Date.now() / 1000) + 30,
          },
          process.env.ACCESS_TOKEN_SECRET
        );
        this.response = await request
          .post("/users/1/email-change-privileged")
          .set("Authorization", "Bearer " + token)
          .send({ email: "Password111" });
      });
      it("returns Bad Request", async () => {
        expect(this.response.statusCode).to.eql(400);
      });

      it("includes a code", async () => {
        expect(this.response.body["errorCode"]).to.eql(102);
      });

      it("includes a message", async () => {
        expect(this.response.body["msg"]).to.include("invalid");
      });

      it("includes a list of invalid properties", async () => {
        expect(this.response.body["invalidProperties"]["email"][0]).to.eq(
          "NO_EMAIL"
        );
      });
    },
    "with taken email": () => {
      it("makes api call", async () => {
        let token = jwt.sign(
          {
            id: 1,
            perm: "changeAllUsersEmailPrivileged",
            exp: Math.floor(Date.now() / 1000) + 30,
          },
          process.env.ACCESS_TOKEN_SECRET
        );
        this.response = await request
          .post("/users/1/email-change-privileged")
          .set("Authorization", "Bearer " + token)
          .send({ email: "user2@mail.de" });
      });
      it("returns Bad Request", async () => {
        expect(this.response.statusCode).to.eql(400);
      });

      it("includes a code", async () => {
        expect(this.response.body["errorCode"]).to.eql(102);
      });

      it("includes a message", async () => {
        expect(this.response.body["msg"]).to.include("invalid");
      });

      it("includes a list of invalid properties", async () => {
        expect(this.response.body["invalidProperties"]["email"][0]).to.eq(
          "IS_TAKEN"
        );
      });
    },
    successful: () => {
      it("makes api call", async () => {
        let token = jwt.sign(
          {
            id: 1,
            perm: "changeAllUsersEmailPrivileged",
            exp: Math.floor(Date.now() / 1000) + 30,
          },
          process.env.ACCESS_TOKEN_SECRET
        );
        this.response = await request
          .post("/users/1/email-change-privileged")
          .set("Authorization", "Bearer " + token)
          .send({ email: process.env.TEST_MAIL_RECEIVER });
      }).timeout(30000);

      it("returns Created", async () => {
        expect(this.response.statusCode).to.eql(201);
      });

      it("includes no body", async () => {
        expect(this.response.body).to.be.empty;
      });
    },
  },
});
