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
      await seedDB("3roles");
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
      email = await getEmail();

      //expect mail not older than 60 secs
      expect(new Date(email.date).getTime()).to.be.closeTo(
        new Date().getTime(),
        60000
      );
      const splitLink = email.text.split("link: ")[1].trim().split("/");
      this.userID = splitLink[4];
      this.code = splitLink[6];
    }).timeout(30000);

    it("founds still 0 users", async () => {
      let response = await request
        .get("/users/length")
        .set("Authorization", "Bearer " + godToken);
      expect(response.body["length"]).to.eql(0);
    });

    it("verifies user", async () => {
      let response = await request.get(`/users/${this.userID}/verify/${this.code}`);
      expect(response.statusCode).to.eql(303);
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
  describe("Update first user", () => {
    it("updates name, postcode, city, phone", async () => {
      const response = await request
        .put("/users/1")
        .set("Authorization", "Bearer " + this.accessToken)
        .send({
          postcode: "00000",
          name: "New Name",
          city: "new city",
          phone: "0000000000",
        });
      expect(response.body["updated"]).to.has.keys([
        "postcode",
        "name",
        "city",
        "phone",
      ]);
    });

    it("updates password", async () => {
      await request
        .put("/users/1/password")
        .set("Authorization", "Bearer " + this.accessToken)
        .send({ oldPassword: "Password1", newPassword: "Password2" });
    });

    it("cant get new accessToken", async () => {
      const response = await request
        .get("/token")
        .set("Cookie", ["skygatecasestudy.refreshtoken=" + this.refreshToken]);
      expect(response.statusCode).to.eql(400);
    });

    it("logs in again", async () => {
      let response = await request.post("/login").send({
        email: process.env.TEST_MAIL_RECEIVER,
        password: "Password2",
      });
      this.refreshToken = response.headers["set-cookie"][0]
        .split(";")[0]
        .split("=")[1];

      response = await request
        .get("/token")
        .set("Cookie", ["skygatecasestudy.refreshtoken=" + this.refreshToken]);
      this.accessToken = response.body.accessToken;
      this.userID = jwt.decode(this.accessToken).id;
    });

    it("gets user new data", async () => {
      const response = await request
        .get(`/users/${this.userID}`)
        .set("Authorization", "Bearer " + this.accessToken);
      expect(response.body["email"]).to.eql(process.env.TEST_MAIL_RECEIVER);
      expect(response.body["name"]).to.eql("New Name");
      expect(response.body["postcode"]).to.eql("00000");
      expect(response.body["city"]).to.eql("new city");
      expect(response.body["phone"]).to.eql("0000000000");
    });
  });
  describe("Try to create second user", () => {
    it("cant register second user with same email", async () => {
      const response = await request.post("/register").send({
        email: process.env.TEST_MAIL_RECEIVER,
        name: "Second User",
        phone: "123456789",
        city: "City",
        postcode: "12345",
        password: "Password1",
      });
      expect(response.statusCode).to.eql(400);
    });
    it("change first users email", async () => {
      await request
        .post("/users/1/emailChange")
        .set("Authorization", "Bearer " + this.accessToken)
        .send({ email: process.env.TEST_MAIL_RECEIVER2 });
      //sleep a minute
      await new Promise((r) => setTimeout(r, 20000));
      //get the newest unread email
      email = await getEmail("email2");
      //expect mail not older than 60 secs
      expect(new Date(email.date).getTime()).to.be.closeTo(
        new Date().getTime(),
        60000
      );
      this.emailCode = email.text.split("link: ")[1].trim().split("/")[6];
    }).timeout(30000);
    it("cant register second user with one of the 2 emails", async () => {
      let response = await request.post("/register").send({
        email: process.env.TEST_MAIL_RECEIVER,
        name: "Second User",
        phone: "123456789",
        city: "City",
        postcode: "12345",
        password: "Password1",
      });
      expect(response.statusCode).to.eql(400);
      response = await request.post("/register").send({
        email: process.env.TEST_MAIL_RECEIVER2,
        name: "Second User",
        phone: "123456789",
        city: "City",
        postcode: "12345",
        password: "Password1",
      });
      expect(response.statusCode).to.eql(400);
    });

    it("verifies first users email change", async () => {
      await request.get(`/users/1/emailChange/${this.emailCode}`);
    });
    it("still can get accessToken", async () => {
      const response = await request
        .get("/token")
        .set("Cookie", ["skygatecasestudy.refreshtoken=" + this.refreshToken]);
      this.accessToken = response.body.accessToken;
    });
    it("logs out", async () => {
      await request
        .post("/users/1/logout")
        .set("Authorization", "Bearer " + this.accessToken);
    });
    it("cant get accessToken", async () => {
      const response = await request
        .get("/token")
        .set("Cookie", ["skygatecasestudy.refreshtoken=" + this.refreshToken]);
      expect(response.statusCode).to.eql(400);
    });
    it("logs in with new email", async () => {
      let response = await request.post("/login").send({
        email: process.env.TEST_MAIL_RECEIVER2,
        password: "Password2",
      });
      this.refreshToken = response.headers["set-cookie"][0]
        .split(";")[0]
        .split("=")[1];

      response = await request
        .get("/token")
        .set("Cookie", ["skygatecasestudy.refreshtoken=" + this.refreshToken]);
      this.accessToken = response.body.accessToken;
      this.userID = jwt.decode(this.accessToken).id;
    });
    it("can now register and verify second user", async () => {
      const response = await request.post("/register").send({
        email: process.env.TEST_MAIL_RECEIVER,
        name: "Second User",
        phone: "123456789",
        city: "City",
        postcode: "12345",
        password: "Password1",
      });
      expect(response.statusCode).to.eql(201); //sleep a minute
      await new Promise((r) => setTimeout(r, 20000));
      //get the newest unread email
      let email = await getEmail();

      //expect mail not older than 60 secs
      expect(new Date(email.date).getTime()).to.be.closeTo(
        new Date().getTime(),
        60000
      );
      const splitLink = email.text.split("link: ")[1].trim().split("/");
      this.userID2 = splitLink[4];
      this.code2 = splitLink[6];
      await request.get(`/users/${this.userID2}/verify/${this.code2}`);
    }).timeout(30000);

    it("founds 2 user", async () => {
      const response = await request
        .get("/users/length")
        .set("Authorization", "Bearer " + godToken);
      expect(response.body["length"]).to.eql(2);
    });
  }),
    describe("Delete second user", () => {
      it("deletes second user", async () => {
        await request
          .delete("/users/2")
          .set("Authorization", "Bearer " + godToken);
      });
      it("founds 1 user", async () => {
        const response = await request
          .get("/users/length")
          .set("Authorization", "Bearer " + godToken);
        expect(response.body["length"]).to.eql(1);
      });
      it("second user cant log in", async () => {
        let response = await request.post("/login").send({
          email: process.env.TEST_MAIL_RECEIVER,
          password: "Password1",
        });
        expect(response.statusCode).to.eql(400);
      });
    });
});
