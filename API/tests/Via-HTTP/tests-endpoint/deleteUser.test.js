const { request, expect } = require("../config");
const { makeSuite, notAllowed } = require("../helper");
let jwt = require("jsonwebtoken");

/**
 * Tests for the DELETE /users/{id} route
 */
makeSuite(["3roles", "1User"], "/users/{userID}", {
  DELETE: {
    "without accessToken": () => {
      it("makes api call", async () => {
        this.response = await request.delete("/users/1");
      });

      it("returns Unauthorized", async () => {
        expect(this.response.statusCode).to.eql(401);
      });
    },
    "without permission": () => {
      it("makes api call", async () => {
        let token = jwt.sign(
          {
            id: 2,
            perm: "user:{all}:{userID}",
            exp: Math.floor(Date.now() / 1000) + 30,
          },
          process.env.ACCESS_TOKEN_SECRET
        );
        this.response = await request
          .delete("/users/1")
          .set("Authorization", "Bearer " + token);
      });

      it("returns Forbidden", async () => {
        expect(this.response.statusCode).to.eql(403);
      });

      it("includes requiredPermissions", async () => {
        expect(this.response.body.requiredPermissions).to.eql([
          "user:update:{userID}",
        ]);
      });
    },
    "user not exists": () => {
      it("makes api call", async () => {
        let token = jwt.sign(
          {
            id: 3,
            perm: "user:{all}:{userID}",
            exp: Math.floor(Date.now() / 1000) + 30,
          },
          process.env.ACCESS_TOKEN_SECRET
        );
        this.response = await request
          .delete("/users/3")
          .set("Authorization", "Bearer " + token);
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
    successful: () => {
      it("makes api call", async () => {
        let token = jwt.sign(
          {
            id: 1,
            perm: "user:{all}:{userID}",
            exp: Math.floor(Date.now() / 1000) + 30,
          },
          process.env.ACCESS_TOKEN_SECRET
        );
        this.response = await request
          .put("/users/1")
          .set("Authorization", "Bearer " + token);
      });

      it("returns No Content", async () => {
        expect(this.response.statusCode).to.eql(204);
      });

      it("includes no body", async () => {
        expect(this.response.body).to.eql(null);
      });
    },
  },
});
