const { request, expect } = require("../config");
const { makeSuite, notAllowed } = require("../helper");
let jwt = require("jsonwebtoken");

/**
 * Tests for the  /users/{x}/role route
 */
makeSuite(["3roles", "1User"], "/users/{userID}/role", {
  DELETE: notAllowed(),
  PATCH: notAllowed(),
  POST: notAllowed(),
  GET: notAllowed(),
  PUT: {
    "without accessToken": () => {
      it("makes api call", async () => {
        this.response = await request.put("/users/1/role");
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
            perm: "",
            exp: Math.floor(Date.now() / 1000) + 30,
          },
          process.env.ACCESS_TOKEN_SECRET
        );
        this.response = await request
          .put("/users/1/role")
          .set("Authorization", "Bearer " + token);
      });

      it("returns Forbidden", async () => {
        expect(this.response.statusCode).to.eql(403);
      });
    },
    "user not exists": () => {
      it("makes api call", async () => {
        let token = jwt.sign(
          {
            id: 3,
            perm: "changeAllUsersRoles",
            exp: Math.floor(Date.now() / 1000) + 30,
          },
          process.env.ACCESS_TOKEN_SECRET
        );
        this.response = await request
          .put("/users/3/role")
          .set("Authorization", "Bearer " + token)
          .send({ role: "admin" });
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
    "with invalid body": () => {
      it("makes api call", async () => {
        let token = jwt.sign(
          {
            id: 1,
            perm: "changeAllUsersRoles",
            exp: Math.floor(Date.now() / 1000) + 30,
          },
          process.env.ACCESS_TOKEN_SECRET
        );
        this.response = await request
          .put("/users/1/role")
          .set("Authorization", "Bearer " + token)
          .send({ quatsch: "fhjksdgbajk" });
      });

      it("returns Bad Request", async () => {
        expect(this.response.statusCode).to.eql(400);
      });

      it("includes a code", async () => {
        expect(this.response.body["errorCode"]).to.eql(101);
      });

      it("includes a message", async () => {
        expect(this.response.body["msg"]).to.include("No role provided");
      });
    },
    "with invalid role": () => {
      it("makes api call", async () => {
        let token = jwt.sign(
          {
            id: 1,
            perm: "changeAllUsersRoles",
            exp: Math.floor(Date.now() / 1000) + 30,
          },
          process.env.ACCESS_TOKEN_SECRET
        );
        this.response = await request
          .put("/users/1/role")
          .set("Authorization", "Bearer " + token)
          .send({ role: "Papst" });
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
        expect(this.response.body["invalidProperties"]["role"][0]).to.eq(
          "INVALID_ROLE"
        );
      });
    },
    successful: () => {
      it("makes api call", async () => {
        let token = jwt.sign(
          {
            id: 1,
            perm: "changeAllUsersRoles",
            exp: Math.floor(Date.now() / 1000) + 30,
          },
          process.env.ACCESS_TOKEN_SECRET
        );
        this.response = await request
          .put("/users/1/role")
          .set("Authorization", "Bearer " + token)
          .send({ role: "guest" });
      });

      it("returns OK", async () => {
        expect(this.response.statusCode).to.eql(200);
      });

      it("includes a list of updated properties", async () => {
        expect(this.response.body["updated"]).to.eql({
          role: "guest",
        });
      });
    },
  },
});
