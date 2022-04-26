const { request, expect } = require("../config");

describe("Overall check", function () {
  it("/ returns with 404", async function () {
    const response = await request.get("/");
    expect(response.status).to.eql(404);
  });
});
