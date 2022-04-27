const { request, expect } = require("../config");
const { clearDB } = require("../helper");

describe("/register", () => {
  beforeEach((done) => {
    clearDB(done);
  });

  describe("POST /register", () => {
    it("returns Bad Request if no data are given", async () => {
      const response = await request.post("/register");

      expect(response.statusCode).to.eql(400);
      expect(response.body.data.length).to.eql(0);
    });

    it("returns Bad Request if not all attributes are given", async () => {
      const response = await request
        .post("/register")
        .send({ email: "email@mail.de" });

      expect(response.statusCode).to.eql(400);
      expect(response.body.data.length).to.eql(0);
    });

    it("returns Bad Request if not all attributes are valid", async () => {
      const response = await request.post("/register").send({
        email: "email@mail.de",
        name: "Name",
        phone: "123456789",
        city: "City",
        postcode: 12345,
        password: "Password1",
      });

      expect(response.statusCode).to.eql(400);
      expect(response.body.data.length).to.eql(0);
    });

    it("returns Created if everything is correct", async () => {
      const response = await request.post("/register").send({
        email: "email@mail.de",
        name: "Name",
        phone: "123456789",
        city: "City",
        postcode: "12345",
        password: "Password1",
      });

      expect(response.statusCode).to.eql(201);
      expect(response.body.data.length).to.eql(0);
    });
  });

  describe("Get /register", () => {
    it("returns Method Not Allowed", async () => {
      const response = await request.get("/register");

      expect(response.statusCode).to.eql(405);
      expect(response.body.data.length).to.eql(0);
    });
  });

  describe("Put /register", () => {
    it("returns Method Not Allowed", async () => {
      const response = await request.put("/register");

      expect(response.statusCode).to.eql(405);
      expect(response.body.data.length).to.eql(0);
    });
  });

  describe("Delete /register", () => {
    it("returns Method Not Allowed", async () => {
      const response = await request.delete("/register");

      expect(response.statusCode).to.eql(405);
      expect(response.body.data.length).to.eql(0);
    });
  });

  describe("Patch /register", () => {
    it("returns Method Not Allowed", async () => {
      const response = await request.patch("/register");

      expect(response.statusCode).to.eql(405);
      expect(response.body.data.length).to.eql(0);
    });
  });
});
