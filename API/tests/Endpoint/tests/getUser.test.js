const { request, expect } = require("../config");
const { makeSuite, notAllowed } = require("../helper");
let jwt = require("jsonwebtoken");

/**
 * Tests for the GET /users/{x} route
 */
makeSuite(["3roles", "1User"], "/users/{userID}", {
  PATCH: notAllowed(),
  POST: notAllowed(),
  GET: {
    "without accessToken": () => {
      it("makes api call", async () => {
        this.response = await request.get("/users/1");
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
            perm: "user:{all}:2",
            exp: Math.floor(Date.now() / 1000) + 30,
          },
          process.env.ACCESS_TOKEN_SECRET
        );
        this.response = await request
          .get("/users/1")
          .set("Authorization", "Bearer " + token);
      });

      it("returns Forbidden", async () => {
        expect(this.response.statusCode).to.eql(403);
      });

      it("includes requiredPermissions", async () => {
        expect(this.response.body.requiredPermissions).to.eql(["user:read:1"]);
      });
    },
    "user not exists": () => {
      it("makes api call", async () => {
        let token = jwt.sign(
          {
            id: 3,
            perm: "user:{all}:3",
            exp: Math.floor(Date.now() / 1000) + 30,
          },
          process.env.ACCESS_TOKEN_SECRET
        );
        this.response = await request
          .get("/users/3")
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
    successful: () => {
      it("makes api call", async () => {
        let token = jwt.sign(
          {
            id: 1,
            perm: "user:{all}:1",
            exp: Math.floor(Date.now() / 1000) + 30,
          },
          process.env.ACCESS_TOKEN_SECRET
        );
        this.response = await request
          .get("/users/1")
          .set("Authorization", "Bearer " + token);
      });

      it("returns OK", async () => {
        expect(this.response.statusCode).to.eql(200);
      });

      it("includes a all data", async () => {
        expect(this.response.body["email"]).to.eql("user1@mail.de");
        expect(this.response.body["name"]).to.eql("user1");
        expect(this.response.body["postcode"]).to.eql("00000");
        expect(this.response.body["city"]).to.eql("usertown");
        expect(this.response.body["phone"]).to.eql("015937839");
        expect(this.response.body["role"]).to.eql("user");
      });
    },
  },
});
