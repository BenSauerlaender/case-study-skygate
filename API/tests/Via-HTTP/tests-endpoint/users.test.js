const { request, expect } = require("../config");
const { makeSuite, notAllowed } = require("../helper");

/**
 * Tests for the /users route
 */
makeSuite(["3roles", "100Users"], "/users", {
  PUT: notAllowed(),
  DELETE: notAllowed(),
  PATCH: notAllowed(),
  POST: notAllowed(),
  GET: {
    "Without a query string": () => {
      it("makes api call", async () => {
        this.response = await request.get("/users/");
      });

      it("returns OK", async () => {
        expect(this.response.statusCode).to.eql(200);
      });

      it("includes the users", async () => {
        expect(this.response.body.length).to.eql(100);
      });

      it("users in correct format", async () => {
        expect(this.response.body[0]).to.contains.members([
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
    "With filter case sensitive": () => {
      it("makes api call", async () => {
        this.response = await request.get("/users/length?city=se&sensitive");
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
        this.response = await request.get("/users/length?city=se");
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
        this.response = await request.get("/users/length?city=se&name=w");
      });

      it("returns OK", async () => {
        expect(this.response.statusCode).to.eql(200);
      });

      it("returns the correct size", async () => {
        expect(this.response.body.length).to.eql(3);
      });
    },
    "With sort ASC 1": () => {
      it("makes api call", async () => {
        this.response = await request.get("/users/length?sortby=postcode");
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
        this.response = await request.get("/users/length?sortby=postcode&ASC");
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
        this.response = await request.get("/users/length?sortby=email&DESC");
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
        this.response = await request.get("/users/length?page=10");
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
        this.response = await request.get("/users/length?page=60&index=1");
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
        this.response = await request.get("/users/length?page=10&index=11");
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
        this.response = await request.get(
          "/users/length?city=se&sortBy=phone&page=3&index=2"
        );
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
