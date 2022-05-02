const { request, expect } = require("../config");

describe("sendResponse", () => {
  it("testResponseCode200", async () => {
    const response = await request.get("/testResponseCode200");

    //status code 200
    expect(response.statusCode).to.eql(200);
  });

  it("testResponseContainsDefaultHeadersAndNoBody", async () => {
    const response = await request.get("/testResponseCode204");

    //contains the following headers
    expect(response.headers).to.have.keys("connection", "date", "server");
    //contain no body
    expect(response.body).to.eql({});

    //status code 204
    expect(response.statusCode).to.eql(204);
  });

  it("testHeader", async () => {
    const response = await request.get("/testHeader");

    //contain header: "content-type: test-value"
    expect(response.headers).to.contain.key("content-type");
    expect(response.headers["content-type"]).to.eql("test-value");
  });

  it("testTwoHeaders", async () => {
    const response = await request.get("/testTwoHeaders");

    //contain headers: "content-type: test-value","last-modified: test-value2"
    expect(response.headers).to.contain.key("content-type");
    expect(response.headers).to.contain.key("last-modified");
    expect(response.headers["content-type"]).to.eql("test-value");
    expect(response.headers["last-modified"]).to.eql("test-value2");
  });

  it("testCookie", async () => {
    const response = await request.get("/testCookie");

    //contains cookie
    expect(response.headers).to.contain.key("set-cookie");
    expect(response.headers["set-cookie"].length).to.eql(1);

    //contains the right metadata
    const cookie = response.headers["set-cookie"][0].split(";");
    expect(cookie.length).to.eql(6);
    expect(cookie[0]).to.eql("cookie=value");
    expect(cookie[1].split("=")[0]).to.eql(" expires");
    expect(cookie[2]).to.eql(" Max-Age=60");
    expect(cookie[3]).to.eql(" path=api/v1/path");
    expect(cookie[4]).to.eql(" domain=domain");
    expect(cookie[5]).to.eql(" secure");
  });

  it("testCookie2", async () => {
    const response = await request.get("/testCookie2");

    //contains cookie
    expect(response.headers).to.contain.key("set-cookie");
    expect(response.headers["set-cookie"].length).to.eql(1);

    //contains the right metadata
    const cookie = response.headers["set-cookie"][0].split(";");
    expect(cookie.length).to.eql(4);
    expect(cookie[0]).to.eql("cookie=value");
    expect(cookie[1]).to.eql(" path=api/v1/");
    expect(cookie[2]).to.eql(" domain=domain");
    expect(cookie[3]).to.eql(" HttpOnly");
  });

  it("testData", async () => {
    const response = await request.get("/testData");

    expect(response.headers).to.contains.keys("content-type", "content-length");

    //contains the right body
    expect(response.body).to.have.keys("testData", "testObj");
    expect(response.body.testData).to.eql("test");
    expect(response.body.testObj.num1).to.eql(1);
    expect(response.body.testObj.num2).to.eql(2);
  });

  it("testAll", async () => {
    const response = await request.get("/testAll");

    //status code 204
    expect(response.statusCode).to.eql(200);

    //contains cookie
    expect(response.headers).to.contain.key("set-cookie");
    expect(response.headers["set-cookie"].length).to.eql(1);

    //contains the following headers
    expect(response.headers).to.have.keys(
      "connection",
      "date",
      "server",
      "content-type",
      "content-length",
      "last-modified",
      "set-cookie"
    );

    //contains the right body
    expect(response.body).to.have.keys("testData", "testObj");
    expect(response.body.testData).to.eql("test");
    expect(response.body.testObj.num1).to.eql(1);
    expect(response.body.testObj.num2).to.eql(2);
  });
});
