const { request, expect } = require("../config");
const { makeSuite, notAllowed } = require("../helper");
var jwt = require("jsonwebtoken");

/**
 * Tests for the /token route
 */
makeSuite(["3roles", "1User"], "/token", {
  PUT: notAllowed(),
  DELETE: notAllowed(),
  PATCH: notAllowed(),
  POST: notAllowed(),
  GET: {
    "without a cookie": (path) => {
      it("makes api call", async () => {
        this.response = await request.get(path);
      });

      it("returns Bad Request", async () => {
        expect(this.response.statusCode).to.eql(400);
      });

      it("includes a code", async () => {
        expect(this.response.body["code"]).to.eql(301);
      });

      it("includes a message", async () => {
        expect(this.response.body["msg"]).to.include(
          "No refreshToken provided"
        );
      });
    },
    "without a parsable jwt as cookie data ": (path) => {
      it("makes api call", async () => {
        this.response = await request
          .get(path)
          .set("Cookie", ["skygatecasestudy.refreshtoken=abcd123"]);
      });

      it("returns Bad Request", async () => {
        expect(this.response.statusCode).to.eql(400);
      });

      it("includes a code", async () => {
        expect(this.response.body["code"]).to.eql(302);
      });

      it("includes a message", async () => {
        expect(this.response.body["msg"]).to.include(
          "The refreshToken is invalid"
        );
      });
      it("includes a reason", async () => {
        expect(this.response.body.reason).to.eql("NOT_PARSABLE");
      });
    },
    "with a unverifiable jwt": (path) => {
      it("makes api call", async () => {
        var token = jwt.sign({ foo: "bar" }, "shhhhh");
        this.response = await request
          .get(path)
          .set("Cookie", ["skygatecasestudy.refreshtoken=" + token]);
      });

      it("returns Bad Request", async () => {
        expect(this.response.statusCode).to.eql(400);
      });

      it("includes a code", async () => {
        expect(this.response.body["code"]).to.eql(302);
      });

      it("includes a message", async () => {
        expect(this.response.body["msg"]).to.include(
          "The refreshToken is invalid"
        );
      });
      it("includes a reason", async () => {
        expect(this.response.body.reason).to.eql("NOT_VERIFIABLE");
      });
    },
    "with a expired jwt": (path) => {
      it("makes api call", async () => {
        var token = jwt.sign(
          { foo: "bar", iat: Math.floor(Date.now() / 1000) - 30 },
          process.env.REFRESH_TOKEN_SECRET
        );
        this.response = await request
          .get(path)
          .set("Cookie", ["skygatecasestudy.refreshtoken=" + token]);
      });

      it("returns Bad Request", async () => {
        expect(this.response.statusCode).to.eql(400);
      });

      it("includes a code", async () => {
        expect(this.response.body["code"]).to.eql(302);
      });

      it("includes a message", async () => {
        expect(this.response.body["msg"]).to.include(
          "The refreshToken is invalid"
        );
      });
      it("includes a reason", async () => {
        expect(this.response.body.reason).to.eql("EXPIRED");
      });
    },
    "with an invalid jwt": (path) => {
      it("makes api call", async () => {
        var token = jwt.sign(
          { cnt: 10, id: 1 },
          process.env.REFRESH_TOKEN_SECRET
        );
        this.response = await request
          .get(path)
          .set("Cookie", ["skygatecasestudy.refreshtoken=" + token]);
      });

      it("returns Bad Request", async () => {
        expect(this.response.statusCode).to.eql(400);
      });

      it("includes a code", async () => {
        expect(this.response.body["code"]).to.eql(302);
      });

      it("includes a message", async () => {
        expect(this.response.body["msg"]).to.include(
          "The refreshToken is invalid"
        );
      });
      it("includes a reason", async () => {
        expect(this.response.body.reason).to.eql("OLD_TOKEN");
      });
    },
    "with completely valid jwt": (path) => {
      it("makes api call", async () => {
        var token = jwt.sign(
          { cnt: 0, id: 1 },
          process.env.REFRESH_TOKEN_SECRET
        );
        this.response = await request
          .get(path)
          .set("Cookie", ["skygatecasestudy.refreshtoken=" + token]);
      });

      it("returns OK", async () => {
        expect(this.response.statusCode).to.eql(400);
      });

      it("includes the accessToken", async () => {
        expect(this.response.body).to.contain("accessToken");
        let token = jwt.decode(this.response.body.accessToken);
        expect(token.payload).contains("id");
        expect(token.payload).contains("perm");
      });
    },
  },
});
