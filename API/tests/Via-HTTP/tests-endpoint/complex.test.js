const { request, expect } = require("../config");
const { clearDB, createTable, seedDB, tables, godToken } = require("../helper");
const { getEmail } = require("../emailHelper.js");
let jwt = require("jsonwebtoken");

/**
 * Tests all API routes in a natural way
 */
describe("complex Test", () => {
  describe("Prerequisites", () => {
    it("resets the DB", async () => {
      await clearDB();
      for (let i = 0; i < tables.length; i++) {
        await createTable(tables[i]);
      }
    });
    it("seeds the DB", async () => {
      await seedDB("1role");
    });
    it("Contains no users", async () => {
      let response = await request
        .get("/users/length")
        .set("Authorization", "Bearer " + godToken);
      expect(response.body["length"]).to.eql(0);
    });
  });
  describe("Register first user", () => {
    it("register user", async () => {
      await request.post("/register").send({
        email: process.env.TEST_MAIL_RECEIVER,
        name: "First User",
        phone: "123456789",
        city: "City",
        postcode: "12345",
        password: "Password1",
      });
    }).timeout(10000);

    it("gets an email", async () => {
      //sleep a minute
      await new Promise((r) => setTimeout(r, 20000));
      //get the newest unread email
      this.email = await getEmail();

      //expect mail not older than 60 secs
      expect(new Date(this.email.date).getTime()).to.be.closeTo(
        new Date().getTime(),
        60000
      );
      const splitLink = this.email.text.split("link: ")[1].trim().split("/");
      this.userID = splitLink[6];
      this.code = splitLink[8];
    }).timeout(30000);

    it("founds still 0 users", async () => {
      let response = await request
        .get("/users/length")
        .set("Authorization", "Bearer " + godToken);
      expect(response.body["length"]).to.eql(0);
    });

    it("verifies user", async () => {
      await request.get(`/users/${this.userID}/verify/${this.code}`);
    });

    it("founds 1 user", async () => {
      const response = await request
        .get("/users/length")
        .set("Authorization", "Bearer " + godToken);
      expect(response.body["length"]).to.eql(1);
    });
  });
  describe("Login first user", () => {
    it("logs in", async () => {
      const response = await request.post("/login").send({
        email: process.env.TEST_MAIL_RECEIVER,
        password: "Password1",
      });
      this.refreshToken = response.headers["set-cookie"][0]
        .split(";")[0]
        .split("=")[1];
    });
    it("gets accessToken", async () => {
      const response = await request
        .get("/token")
        .set("Cookie", ["skygatecasestudy.refreshtoken=" + this.refreshToken]);
      this.accessToken = response.body.accessToken;
      this.userID = jwt.decode(this.accessToken).id;
    });
    it("can get user data", async () => {
      const response = await request
        .get(`/users/${this.userID}`)
        .set("Authorization", "Bearer " + this.accessToken);
      expect(response.body["email"]).to.eql(process.env.TEST_MAIL_RECEIVER);
      expect(response.body["name"]).to.eql("First User");
      expect(response.body["postcode"]).to.eql("12345");
      expect(response.body["city"]).to.eql("City");
      expect(response.body["phone"]).to.eql("123456789");
      expect(response.body["role"]).to.eql("user");
    });
  });
  describe("Update first user", () => {});
  describe("Logout/in first user", () => {});
  describe("Try to create second user", () => {});
  describe("Delete second user", () => {});
});
