const { request, expect } = require("../config");
const { makeSuite, notAllowed } = require("../helper");
let jwt = require("jsonwebtoken");

/**
 * Tests for the /logout route
 */
makeSuite(["3roles", "1User"], "/users/{userID}/logout", {
  PUT: notAllowed(),
  DELETE: notAllowed(),
  PATCH: notAllowed(),
  GET: notAllowed(),
  POST: {
    "without accessToken": () => {
      it("makes api call", async () => {
        this.response = await request.post("/users/1/logout");
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
            perm: "logoutSelf",
            exp: Math.floor(Date.now() / 1000) + 30,
          },
          process.env.ACCESS_TOKEN_SECRET
        );
        this.response = await request
          .post("/users/1/logout")
          .set("Authorization", "Bearer " + token);
      });
    },
    "user not exists": () => {
      it("makes api call", async () => {
        let token = jwt.sign(
          {
            id: 3,
            perm: "logoutSelf",
            exp: Math.floor(Date.now() / 1000) + 30,
          },
          process.env.ACCESS_TOKEN_SECRET
        );
        this.response = await request
          .post("/users/3/logout")
          .set("Authorization", "Bearer " + token);
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
    successful: (path) => {
      it("makes api call", async () => {
        let token = jwt.sign(
          {
            id: 1,
            perm: "logoutSelf",
            exp: Math.floor(Date.now() / 1000) + 30,
          },
          process.env.ACCESS_TOKEN_SECRET
        );
        this.response = await request
          .post("/users/1/logout")
          .set("Authorization", "Bearer " + token);
      });

      it("returns no content", async () => {
        expect(this.response.statusCode).to.eql(204);
      });
    },
  },
});
