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
  },
  "without all properties": (path) => {
    it("makes api call", async () => {
      this.response = await request.post(path).send({ email: "email@mail.de" });
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
      expect(this.response.body["missingProperties"]).to.has.keys(["password"]);
    });
  },
  "with invalid properties": (path) => {
    it("makes api call", async () => {
      this.response = await request.post(path).send({
        email: 123,
        password: "Password1",
      });
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
      expect(this.response.body["invalidProperties"]["email"][0]).to.eq(
        "INVALID_TYPE"
      );
    });
  },
  "with unknown email address": (path) => {
    it("makes api call", async () => {
      this.response = await request.post(path).send({
        email: "email@mail.de",
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
        email: "email@mail.de",
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
      expect(this.response.body["msg"]).to.include("The password is incorrect");
    });
  },
  successful: (path) => {
    it("makes api call", async () => {
      this.response = await request.post(path).send({
        email: "email@mail.de",
        password: "Password1",
      });
    });

    it("return OK", async () => {
      expect(this.response.statusCode).to.eql(400);
    });

    it("includes the refreshToken", async () => {
      var token = jwt_decode(this.response.body["refreshToken"]);
      expect(token.id).to.eql(2);
    });
  },
});
