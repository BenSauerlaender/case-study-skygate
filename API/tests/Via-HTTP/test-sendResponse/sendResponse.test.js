const { request, expect } = require("../config");

describe("sendResponse", () => {
  it("testResponseCode200", async () => {
    const response = await request.get("/testResponseCode200");
    expect(response.statusCode).to.eql(200);
  });
  it("testResponseCode401", async () => {
    const response = await request.get("/testResponseCode401");
    expect(response.statusCode).to.eql(401);
  });
  it("testResponseCode500", async () => {
    const response = await request.get("/testResponseCode500");
    expect(response.statusCode).to.eql(500);
  });
  it("testHeader", async () => {
    const response = await request.get("/testHeader");
    expect(response).to.have.header("test-header", "test-value");
  });
  it("testTwoHeaders", async () => {
    const response = await request.get("/testTwoHeaders");
    expect(response).to.have.header("test-header", "test-value");
    expect(response).to.have.header("test-header2", "test-value2");
  });
  it("testCookie", async () => {
    const response = await request.get("/testCookie");
    expect(response).to.have.header("test-header", "test-value");
    expect(response).to.have.header("test-header2", "test-value2");
  });
});
