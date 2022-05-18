const { request, expect } = require("../config");
const { makeSuite, notAllowed } = require("../helper");
let jwt = require("jsonwebtoken");

/**
 * Tests for the GET /users/{id} route
 */
makeSuite(["3roles", "1User"], "/users/{userID}", {
  PUT: {
    "without accessToken": () => {
      it("makes api call", async () => {
        this.response = await request.put("/users/1");
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
          .put("/users/1")
          .set("Authorization", "Bearer " + token);
      });

      it("returns Forbidden", async () => {
        expect(this.response.statusCode).to.eql(403);
      });

      it("includes requiredPermissions", async () => {
        expect(this.response.body.requiredPermissions).to.eql([
          "user:write:{userID}",
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
          .put("/users/3")
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
    "without any available property": () => {
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
          .set("Authorization", "Bearer " + token)
          .send({ quatsch: "newEmailmail.de" });
      });

      it("returns Bad Request", async () => {
        expect(this.response.statusCode).to.eql(400);
      });

      it("includes a code", async () => {
        expect(this.response.body["code"]).to.eql(101);
      });

      it("includes a message", async () => {
        expect(this.response.body["msg"]).to.include("require");
      });

      it("includes a list of available properties", async () => {
        expect(this.response.body["availableProperties"]).to.has.keys([
          "name",
          "name",
          "phone",
          "city",
          "postcode",
          "role",
        ]);
      });
    },
    "with invalid data": () => {
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
          .set("Authorization", "Bearer " + token)
          .send({ postcode: "newEmailmail.de", name: 123 });
      });

      it("returns Bad Request", async () => {
        expect(this.response.statusCode).to.eql(400);
      });

      it("includes a code", async () => {
        expect(this.response.body["code"]).to.eql(102);
      });

      it("includes a message", async () => {
        expect(this.response.body["msg"]).to.include("invalid");
      });

      it("includes a list of invalid properties", async () => {
        expect(this.response.body["invalidProperties"]["name"][0]).to.eq(
          "INVALID_TYPE"
        );
        expect(this.response.body["invalidProperties"]["postcode"][0]).to.eq(
          "NO_EMAIL"
        );
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
          .set("Authorization", "Bearer " + token)
          .send({ postcode: "00000", name: "New Name" });
      });

      it("returns OK", async () => {
        expect(this.response.statusCode).to.eql(200);
      });

      it("includes a list of updated fields", async () => {
        expect(this.response.body["updated"]).to.eql({
          postcode: "00000",
          name: "New Name",
        });
      });
    },
  },
});
