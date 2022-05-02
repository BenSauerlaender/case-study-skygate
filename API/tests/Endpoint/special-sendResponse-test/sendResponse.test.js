const { request, expect } = require("../config");

describe("sendResponse", () => {
  it("test1", async () => {
    const response = await request.get("/response");
    expect(response.statusCode).to.eql(123);
  });
});
