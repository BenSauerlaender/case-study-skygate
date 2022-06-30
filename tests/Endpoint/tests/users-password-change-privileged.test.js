const { request, expect } = require("../config");
const { makeSuite, notAllowed } = require("../helper");
let jwt = require("jsonwebtoken");

/**
 * Tests for the PUT /users/{x}/password-change-privileged route
 */
makeSuite(["3roles", "1User"], "/users/{userID}/password-change-privileged", {
  GET: notAllowed(),
  POST: notAllowed(),
  DELETE: notAllowed(),
  PATCH: notAllowed(),
  PUT: {
    "without accessToken": () => {
      it("makes api call", async () => {
        this.response = await request.put(
          "/users/1/password-change-privileged"
        );
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
            perm: "changeAllUsersPasswordsPrivileged",
            exp: Math.floor(Date.now() / 1000) + 30,
          },
          process.env.ACCESS_TOKEN_SECRET
        );
        this.response = await request
          .put("/users/1/password-change-privileged")
          .set("Authorization", "Bearer " + token);
      });

      it("returns Forbidden", async () => {
        expect(this.response.statusCode).to.eql(403);
      });
    },
    "without a body": () => {
      it("makes api call", async () => {
        let token = jwt.sign(
          {
            id: 1,
            perm: "changeAllUsersPasswordsPrivileged",
            exp: Math.floor(Date.now() / 1000) + 30,
          },
          process.env.ACCESS_TOKEN_SECRET
        );
        this.response = await request
          .put("/users/1/password-change-privileged")
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
        expect(this.response.body["missingProperties"]).to.contains.members([
          "newPassword",
        ]);
      });
    },
    "with invalid new password": () => {
      it("makes api call", async () => {
        let token = jwt.sign(
          {
            id: 1,
            perm: "changeAllUsersPasswordsPrivileged",
            exp: Math.floor(Date.now() / 1000) + 30,
          },
          process.env.ACCESS_TOKEN_SECRET
        );
        this.response = await request
          .put("/users/1/password-change-privileged")
          .set("Authorization", "Bearer " + token)
          .send({ newPassword: "te" });
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
        expect(this.response.body["invalidProperties"]["password"][0]).to.eq(
          "TO_SHORT"
        );
      });
    },
    "user not exists": () => {
      it("makes api call", async () => {
        let token = jwt.sign(
          {
            id: 3,
            perm: "changeAllUsersPasswordsPrivileged",
            exp: Math.floor(Date.now() / 1000) + 30,
          },
          process.env.ACCESS_TOKEN_SECRET
        );
        this.response = await request
          .put("/users/3/password-change-privileged")
          .set("Authorization", "Bearer " + token)
          .send({ newPassword: "tet123ABCxyz" });
      });

      it("returns Bad Request", async () => {
        expect(this.response.statusCode).to.eql(400);
      });

      it("includes a code", async () => {
        expect(this.response.body["errorCode"]).to.eql(201);
      });

      it("includes a message", async () => {
        expect(this.response.body["msg"]).to.include("id=3");
      });
    },
    successful: () => {
      it("makes api call", async () => {
        let token = jwt.sign(
          {
            id: 1,
            perm: "changeAllUsersPasswordsPrivileged",
            exp: Math.floor(Date.now() / 1000) + 30,
          },
          process.env.ACCESS_TOKEN_SECRET
        );
        this.response = await request
          .put("/users/1/password-change-privileged")
          .set("Authorization", "Bearer " + token)
          .send({ newPassword: "tet123ABCxyz" });
      });

      it("returns No Content", async () => {
        expect(this.response.statusCode).to.eql(204);
      });
    },
  },
});
