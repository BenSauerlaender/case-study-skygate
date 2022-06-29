const { request, expect } = require("../config");
const { makeSuite, notAllowed } = require("../helper");
let jwt = require("jsonwebtoken");

/**
 * Tests for the /users route
 */
makeSuite(["3roles", "100Users"], "/users", {
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
          .get("/users/")
          .set("Authorization", "Bearer " + token);
      });

      it("returns OK", async () => {
        expect(this.response.statusCode).to.eql(200);
      });

      it("includes the users", async () => {
        expect(this.response.body.length).to.eql(100);
      });

      it("users in correct format", async () => {
        expect(this.response.body[0]).to.has.keys([
          "id",
          "name",
          "phone",
          "postcode",
          "email",
          "city",
        ]);
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
          .get("/users?quatsch")
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
          .get("/users?name=abs%")
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
    "With filter case sensitive": () => {
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
          .get("/users?city=se&sensitive")
          .set("Authorization", "Bearer " + token);
      });

      it("returns OK", async () => {
        expect(this.response.statusCode).to.eql(200);
      });

      it("returns the correct size", async () => {
        expect(this.response.body.length).to.eql(11);
      });
    },
    "With filter case insensitive": () => {
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
          .get("/users?city=se")
          .set("Authorization", "Bearer " + token);
      });

      it("returns OK", async () => {
        expect(this.response.statusCode).to.eql(200);
      });

      it("returns the correct size", async () => {
        expect(this.response.body.length).to.eql(12);
      });
    },
    "With 2 filters": () => {
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
          .get("/users?city=se&name=w")
          .set("Authorization", "Bearer " + token);
      });

      it("returns OK", async () => {
        expect(this.response.statusCode).to.eql(200);
      });

      it("returns the correct size", async () => {
        expect(this.response.body.length).to.eql(3);
      });
    },
    "With filters with space": () => {
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
          .get("/users?name=sophia+wick")
          .set("Authorization", "Bearer " + token);
      });

      it("returns OK", async () => {
        expect(this.response.statusCode).to.eql(200);
      });

      it("returns the correct user", async () => {
        expect(this.response.body[0].id).to.eql(33);
      });
    },
    "With sort ASC 1": () => {
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
          .get("/users?sortby=postcode")
          .set("Authorization", "Bearer " + token);
      });

      it("returns OK", async () => {
        expect(this.response.statusCode).to.eql(200);
      });

      it("returns in correct order", async () => {
        expect(this.response.body[0].name).to.eql("Jonathan Bührmann");
      });
    },
    "With sort ASC 1": () => {
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
          .get("/users?sortby=postcode&ASC")
          .set("Authorization", "Bearer " + token);
      });

      it("returns OK", async () => {
        expect(this.response.statusCode).to.eql(200);
      });

      it("returns in correct order", async () => {
        expect(this.response.body[0].name).to.eql("Jonathan Bührmann");
      });
    },
    "With sort DESC": () => {
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
          .get("/users?sortby=email&DESC")
          .set("Authorization", "Bearer " + token);
      });

      it("returns OK", async () => {
        expect(this.response.statusCode).to.eql(200);
      });

      it("returns in correct order", async () => {
        expect(this.response.body[0].postcode).to.eql("54552");
      });
    },
    "With Pagination 1": () => {
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
          .get("/users?page=10")
          .set("Authorization", "Bearer " + token);
      });

      it("returns OK", async () => {
        expect(this.response.statusCode).to.eql(200);
      });

      it("returns the correct size", async () => {
        expect(this.response.body.length).to.eql(10);
      });
    },
    "With Pagination 2": () => {
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
          .get("/users?page=60&index=1")
          .set("Authorization", "Bearer " + token);
      });

      it("returns OK", async () => {
        expect(this.response.statusCode).to.eql(200);
      });

      it("returns the correct size", async () => {
        expect(this.response.body.length).to.eql(40);
      });
    },
    "With Pagination 3": () => {
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
          .get("/users?page=10&index=11")
          .set("Authorization", "Bearer " + token);
      });

      it("returns OK", async () => {
        expect(this.response.statusCode).to.eql(200);
      });

      it("returns 0 users", async () => {
        expect(this.response.body).to.eql({});
      });
    },
    "With Combination": () => {
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
          .get("/users?city=se&sortBy=phone&page=3&index=2&desc")
          .set("Authorization", "Bearer " + token);
      });

      it("returns OK", async () => {
        expect(this.response.statusCode).to.eql(200);
      });

      it("returns the correct size", async () => {
        expect(this.response.body.length).to.eql(3);
      });

      it("returns in correct order", async () => {
        expect(this.response.body[0].id).to.eql(46);
      });
    },
  },
});
