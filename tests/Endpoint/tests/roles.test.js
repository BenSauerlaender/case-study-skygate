const { request, expect } = require("../config");
const { makeSuite, notAllowed } = require("../helper");

/**
 * Tests for the /roles route
 */
makeSuite(["3roles"], "/roles", {
  PUT: notAllowed(),
  DELETE: notAllowed(),
  PATCH: notAllowed(),
  POST: notAllowed(),
  GET: {
    successful: (path) => {
      it("makes api call", async () => {
        this.response = await request.get(path);
      });

      it("returns OK", async () => {
        expect(this.response.statusCode).to.eql(200);
      });

      it("response with list of roles", async () => {
        expect(this.response.body).to.eql(["admin", "guest", "user"]);
      });
    },
  },
});
