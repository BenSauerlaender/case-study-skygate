const { request, expect } = require("../config");
const { makeSuite, notAllowed } = require("../helper");
const jwt_decode = require("jwt-decode");

/**
 * Tests for the /login route
 */
makeSuite(["3roles", "1User"], "/login", {
  PUT: notAllowed(),
  DELETE: notAllowed(),
  PATCH: notAllowed(),
  GET: notAllowed(),
  POST: {
    "without a body": (path) => {
      it("makes api call", async () => {
        this.response = await request.post(path);
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

      it("includes a list of required properties", async () => {
        expect(this.response.body["missingProperties"]).to.has.keys([
          "email",
          "password",
        ]);
      });
    },
    "without all properties": (path) => {
      it("makes api call", async () => {
        this.response = await request
          .post(path)
          .send({ email: "email@mail.de" });
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

      it("includes a list of required properties", async () => {
        expect(this.response.body["missingProperties"]).to.has.keys([
          "password",
        ]);
      });
    },
    "with unknown email address": (path) => {
      it("makes api call", async () => {
        this.response = await request.post(path).send({
          email: null,
          password: "Password1",
        });
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
    "with incorrect Password": (path) => {
      it("makes api call", async () => {
        this.response = await request.post(path).send({
          email: "user1@mail.de",
          password: "pass",
        });
      });

      it("returns Bad Request", async () => {
        expect(this.response.statusCode).to.eql(400);
      });

      it("includes a code", async () => {
        expect(this.response.body["code"]).to.eql(215);
      });

      it("includes a message", async () => {
        expect(this.response.body["msg"]).to.include(
          "The password is incorrect"
        );
      });
    },
    successful: (path) => {
      it("makes api call", async () => {
        this.response = await request.post(path).send({
          email: "user1@mail.de",
          password: "Password111",
        });
      });

      it("returns no content", async () => {
        expect(this.response.statusCode).to.eql(204);
      });

      it("sets the refreshToken as cookie", async () => {
        //Set-Cookie: <cookie-name>=<cookie-value>; Domain=<domain-value>; Secure; HttpOnly
        cookie = this.response.headers["set-cookie"][0];
        cookieSplit = cookie.split(";");

        expect(cookieSplit[0].split("=")[0]).to.eql(
          "skygatecasestudy.refreshtoken"
        );
        expect(cookieSplit[1].split("=")[0]).to.eql(" expires");
        expect(cookieSplit[2]).to.eql(" Max-Age=" + 60 * 60 * 24 * 30);
        expect(cookieSplit[3]).to.eql(
          " path=" + process.env.API_PATH_PREFIX + "/"
        );
        expect(cookieSplit[4]).to.eql(" domain=" + process.env.API_PROD_DOMAIN);
        expect(cookieSplit[5]).to.eql(" secure");
        expect(cookieSplit[6]).to.eql(" HttpOnly");

        var token = jwt_decode(cookieSplit[0].split("=")[1]);
        expect(token.id).to.eql(1);
        expect(token.cnt).to.eql(0);
      });
    },
  },
});
