const { request, expect } = require("../config");
const { makeSuite, notAllowed } = require("../helper");

/**
 * Tests for the /users/length route
 */
makeSuite(["3roles", "100Users"], "/users/length", {
  PUT: notAllowed(),
  DELETE: notAllowed(),
  PATCH: notAllowed(),
  POST: notAllowed(),
  GET: {
    "Without a query string": () => {
      it("makes api call", async () => {
        this.response = await request.get("/users/length");
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
        this.response = await request.get("/users/length?quatsch");
      });

      it("returns Bad Request", async () => {
        expect(this.response.statusCode).to.eql(400);
      });

      it("includes a code", async () => {
        expect(this.response.body["code"]).to.eql(111);
      });

      it("includes a message", async () => {
        expect(this.response.body["msg"]).to.include("invalid query key");
      });

      it("includes a list of invalid keys", async () => {
        expect(this.response.body["invalidKeys"]).to.eq(["quatsch"]);
      });
    },
    "With invalid search string": () => {
      it("makes api call", async () => {
        this.response = await request.get("/users/length?name=abs%");
      });

      it("returns Bad Request", async () => {
        expect(this.response.statusCode).to.eql(400);
      });

      it("includes a code", async () => {
        expect(this.response.body["code"]).to.eql(111);
      });

      it("includes a message", async () => {
        expect(this.response.body["msg"]).to.include(
          "There is an invalid search string."
        );
      });
    },
    "With filter": () => {
      it("makes api call", async () => {
        this.response = await request.get("/users/length?name=w&city=se");
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
        this.response = await request.get(
          "/users/length?name=w&city=se&sortby=phone&desc&page=13&index=5&sensitive"
        );
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
