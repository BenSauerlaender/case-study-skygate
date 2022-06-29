const { request, expect } = require("../config");
const { makeSuite, notAllowed } = require("../helper");
let jwt = require("jsonwebtoken");

/**
 * Tests for the /users/length route
 */
makeSuite(["3roles", "100Users"], "/users/length", {
  PUT: notAllowed(),
  DELETE: notAllowed(),
  PATCH: notAllowed(),
  POST: notAllowed(),
  GET: {
    "without accessToken": () => {
      it("makes api call", async () => {
        this.response = await request.get("/users");
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
            perm: "",
            exp: Math.floor(Date.now() / 1000) + 30,
          },
          process.env.ACCESS_TOKEN_SECRET
        );
        this.response = await request
          .get("/users")
          .set("Authorization", "Bearer " + token);
      });

      it("returns Forbidden", async () => {
        expect(this.response.statusCode).to.eql(403);
      });
    },
    "Without a query string": () => {
      it("makes api call", async () => {
        let token = jwt.sign(
          {
            id: 2,
            perm: "getAllUsers",
            exp: Math.floor(Date.now() / 1000) + 30,
          },
          process.env.ACCESS_TOKEN_SECRET
        );
        this.response = await request
          .get("/users/length")
          .set("Authorization", "Bearer " + token);
      });

      it("returns OK", async () => {
        expect(this.response.statusCode).to.eql(200);
      });

      it("includes the length", async () => {
        expect(this.response.body["length"]).to.eql(100);
      });
    },
    "With invalid query string": () => {
      it("makes api call", async () => {
        let token = jwt.sign(
          {
            id: 2,
            perm: "getAllUsers",
            exp: Math.floor(Date.now() / 1000) + 30,
          },
          process.env.ACCESS_TOKEN_SECRET
        );
        this.response = await request
          .get("/users/length?quatsch")
          .set("Authorization", "Bearer " + token);
      });

      it("returns Bad Request", async () => {
        expect(this.response.statusCode).to.eql(400);
      });

      it("includes a code", async () => {
        expect(this.response.body["errorCode"]).to.eql(111);
      });

      it("includes a message", async () => {
        expect(this.response.body["msg"]).to.include(
          "There are parts of the query string that are invalid"
        );
      });
    },
    "With invalid search string": () => {
      it("makes api call", async () => {
        let token = jwt.sign(
          {
            id: 2,
            perm: "getAllUsers",
            exp: Math.floor(Date.now() / 1000) + 30,
          },
          process.env.ACCESS_TOKEN_SECRET
        );
        this.response = await request
          .get("/users/length?name=abs%")
          .set("Authorization", "Bearer " + token);
      });

      it("returns Bad Request", async () => {
        expect(this.response.statusCode).to.eql(400);
      });

      it("includes a code", async () => {
        expect(this.response.body["errorCode"]).to.eql(111);
      });
      it("includes a message", async () => {
        expect(this.response.body["msg"]).to.include(
          "There are parts of the query string that are invalid"
        );
      });
    },
    "With filter": () => {
      it("makes api call", async () => {
        let token = jwt.sign(
          {
            id: 2,
            perm: "getAllUsers",
            exp: Math.floor(Date.now() / 1000) + 30,
          },
          process.env.ACCESS_TOKEN_SECRET
        );
        this.response = await request
          .get("/users/length?name=w&city=se")
          .set("Authorization", "Bearer " + token);
      });

      it("returns OK", async () => {
        expect(this.response.statusCode).to.eql(200);
      });

      it("includes the length", async () => {
        expect(this.response.body["length"]).to.eql(3);
      });
    },
    "With all": () => {
      it("makes api call", async () => {
        let token = jwt.sign(
          {
            id: 2,
            perm: "getAllUsers",
            exp: Math.floor(Date.now() / 1000) + 30,
          },
          process.env.ACCESS_TOKEN_SECRET
        );
        this.response = await request
          .get("/users/length?name=w&city=se&sortby=phone&desc&page=13&index=5")
          .set("Authorization", "Bearer " + token);
      });

      it("returns OK", async () => {
        expect(this.response.statusCode).to.eql(200);
      });

      it("includes the length", async () => {
        expect(this.response.body["length"]).to.eql(3);
      });
    },
  },
});
