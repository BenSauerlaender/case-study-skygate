const { request, expect } = require("../config");
const { makeSuite, notAllowed } = require("../helper");
let jwt = require("jsonwebtoken");
const { getEmail } = require("../emailHelper.js");

/**
 * Tests for the /users/{x}/emailChange route
 */
makeSuite(["3roles", "2Users"], "/users/{x}/emailChange", {
  PUT: notAllowed(),
  DELETE: notAllowed(),
  PATCH: notAllowed(),
  GET: notAllowed(),
  POST: {
    "without accessToken": () => {
      it("makes api call", async () => {
        this.response = await request.post("/users/1/emailChange");
      });

      it("returns Unauthorized", async () => {
        expect(this.response.statusCode).to.eql(401);
      });
    },

    "without permission": () => {
      it("makes api call", async () => {
        let token = jwt.sign(
          {
            id: 2,
            perm: "user:{all}:2",
            exp: Math.floor(Date.now() / 1000) + 30,
          },
          process.env.ACCESS_TOKEN_SECRET
        );
        this.response = await request
          .post("/users/1/emailChange")
          .set("Authorization", "Bearer " + token);
      });

      it("returns Forbidden", async () => {
        expect(this.response.statusCode).to.eql(403);
      });

      it("includes requiredPermissions", async () => {
        expect(this.response.body.requiredPermissions).to.eql([
          "user:update:1",
        ]);
      });
    },
    "without an email": () => {
      it("makes api call", async () => {
        let token = jwt.sign(
          {
            id: 1,
            perm: "user:{all}:1",
            exp: Math.floor(Date.now() / 1000) + 30,
          },
          process.env.ACCESS_TOKEN_SECRET
        );
        this.response = await request
          .post("/users/1/emailChange")
          .set("Authorization", "Bearer " + token);
      });
      it("returns Bad Request", async () => {
        expect(this.response.statusCode).to.eql(400);
      });

      it("includes a code", async () => {
        expect(this.response.body["errorCode"]).to.eql(101);
      });

      it("includes a message", async () => {
        expect(this.response.body["msg"]).to.include("require");
      });

      it("includes a list of required properties", async () => {
        expect(this.response.body["missingProperties"]).to.has.keys(["email"]);
      });
    },
    "with invalid email": () => {
      it("makes api call", async () => {
        let token = jwt.sign(
          {
            id: 1,
            perm: "user:{all}:1",
            exp: Math.floor(Date.now() / 1000) + 30,
          },
          process.env.ACCESS_TOKEN_SECRET
        );
        this.response = await request
          .post("/users/1/emailChange")
          .set("Authorization", "Bearer " + token)
          .send({ email: "Password111" });
      });
      it("returns Bad Request", async () => {
        expect(this.response.statusCode).to.eql(400);
      });

      it("includes a code", async () => {
        expect(this.response.body["errorCode"]).to.eql(102);
      });

      it("includes a message", async () => {
        expect(this.response.body["msg"]).to.include("invalid");
      });

      it("includes a list of invalid properties", async () => {
        expect(this.response.body["invalidProperties"]["email"][0]).to.eq(
          "NO_EMAIL"
        );
      });
    },
    "with taken email": () => {
      it("makes api call", async () => {
        let token = jwt.sign(
          {
            id: 1,
            perm: "user:{all}:1",
            exp: Math.floor(Date.now() / 1000) + 30,
          },
          process.env.ACCESS_TOKEN_SECRET
        );
        this.response = await request
          .post("/users/1/emailChange")
          .set("Authorization", "Bearer " + token)
          .send({ email: "user2@mail.de" });
      });
      it("returns Bad Request", async () => {
        expect(this.response.statusCode).to.eql(400);
      });

      it("includes a code", async () => {
        expect(this.response.body["errorCode"]).to.eql(102);
      });

      it("includes a message", async () => {
        expect(this.response.body["msg"]).to.include("invalid");
      });

      it("includes a list of invalid properties", async () => {
        expect(this.response.body["invalidProperties"]["email"][0]).to.eq(
          "IS_TAKEN"
        );
      });
    },
    successful: () => {
      it("makes api call", async () => {
        let token = jwt.sign(
          {
            id: 1,
            perm: "user:{all}:1",
            exp: Math.floor(Date.now() / 1000) + 30,
          },
          process.env.ACCESS_TOKEN_SECRET
        );
        this.response = await request
          .post("/users/1/emailChange")
          .set("Authorization", "Bearer " + token)
          .send({ email: process.env.TEST_MAIL_RECEIVER });
      });

      it("returns Created", async () => {
        expect(this.response.statusCode).to.eql(201);
      });

      it("includes no body", async () => {
        expect(this.response.body).to.be.empty;
      });

      it("sends an email", async () => {
        //sleep a minute
        await new Promise((r) => setTimeout(r, 20000));
        //get the newest unread email
        this.email = await getEmail();
        //expect getEmail finds an email
        expect(this.email).not.be.false;
        //expect mail not older than 60 secs
        expect(new Date(this.email.date).getTime()).to.be.closeTo(
          new Date().getTime(),
          60000
        );
      }).timeout(30000);

      it("sets 'to:' correctly", async () => {
        expect(this.email.to.text).to.eql(
          `user1 <${process.env.TEST_MAIL_RECEIVER}>`
        );
      });
      it("sets 'from:' correctly", async () => {
        expect(this.email.from.text).to.eql(
          `SkyGateCaseStudy <no-reply@test.de>`
        );
      });

      it("sets 'subject' correctly", async () => {
        expect(this.email.subject).to.eql(`Verify your new Email!`);
      });

      it("includes the link in plain text", async () => {
        splitStr = this.email.text.split("link: ");
        expect(splitStr[0]).to.eql(
          `Please verify your new email by following this `
        );
        this.plainLink = splitStr[1].trim();
      });
      it("The link is correct", async () => {
        splitLink = this.plainLink.split("/");
        expect(splitLink[0]).to.eql(`www.${process.env.API_PROD_DOMAIN}`);
        expect(splitLink[1]).to.eql("api");
        expect(splitLink[2]).to.eql("v1");
        expect(splitLink[3]).to.eql("users");
        expect(splitLink[4]).to.eql("1");
        expect(splitLink[5]).to.eql("emailChange");
        //the verification code
        expect(splitLink[6]).to.match(/^[0-9a-f]{10}$/);
      });
    },
  },
});
