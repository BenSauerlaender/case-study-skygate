const { request, expect } = require("../config");
const { makeSuite, notAllowed } = require("../helper");
const { getEmail } = require("../emailHelper.js");

/**
 * Tests for the /register route
 */
makeSuite(["3roles"], "/register", {
  GET: notAllowed(),
  PUT: notAllowed(),
  DELETE: notAllowed(),
  PATCH: notAllowed(),
  //The only valid method
  POST: {
    "without a body": (path) => {
      it("makes api call", async () => {
        this.response = await request.post(path);
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
        expect(this.response.body["missingProperties"]).to.has.keys([
          "email",
          "name",
          "phone",
          "city",
          "postcode",
          "password",
        ]);
      });
    },
    "without all properties": (path) => {
      it("makes api call", async () => {
        this.response = await request
          .post(path)
          .send({ email: "email@mail.de" });
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
        expect(this.response.body["missingProperties"]).to.has.keys([
          "name",
          "phone",
          "city",
          "postcode",
          "password",
        ]);
      });
    },
    "with invalid properties": (path) => {
      it("makes api call", async () => {
        this.response = await request.post(path).send({
          email: "email@mail.de",
          name: "Name",
          phone: "123456789",
          city: "City",
          postcode: 12345,
          password: "Password1",
        });
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
        expect(this.response.body["invalidProperties"]["postcode"][0]).to.eq(
          "INVALID_TYPE"
        );
      });
    },
    "with all (and valid) properties": (path) => {
      it("makes api call", async () => {
        this.response = await request.post(path).send({
          email: process.env.TEST_MAIL_RECEIVER,
          name: "Test Name",
          phone: "123456789",
          city: "City",
          postcode: "12345",
          password: "Password1",
        });
      }).timeout(10000);

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
          `Test Name <${process.env.TEST_MAIL_RECEIVER}>`
        );
      });
      it("sets 'from:' correctly", async () => {
        expect(this.email.from.text).to.eql(
          `SkyGateCaseStudy <no-reply@test.de>`
        );
      });
      it("sets 'subject' correctly", async () => {
        expect(this.email.subject).to.eql(`Verify your registration!`);
      });
      it("includes the link in plain text", async () => {
        splitStr = this.email.text.split("link: ");
        expect(splitStr[0]).to.eql(
          `Please verify your registration by following this `
        );
        this.plainLink = splitStr[1].trim();
      });
      it("The link is correct", async () => {
        splitLink = this.plainLink.split("/");
        expect(splitLink[0]).to.eql(`www.${process.env.API_PROD_DOMAIN}`);
        expect(splitLink[1]).to.eql("api");
        expect(splitLink[2]).to.eql("v1");
        expect(splitLink[3]).to.eql("users");
        expect(Number.parseInt(splitLink[4])).not.to.be.NaN;
        expect(splitLink[5]).to.eql("verify");
        //the verification code
        expect(splitLink[6]).to.match(/^[0-9a-f]{10}$/);
      });
    },
    "a duplicate email": (path) => {
      it("makes api call", async () => {
        await request.post(path).send({
          email: "duplicate@mail.de",
          name: "Test Name",
          phone: "123456789",
          city: "City",
          postcode: "12345",
          password: "Password1",
        });
        this.response = await request.post(path).send({
          email: "duplicate@mail.de",
          name: "Test Name",
          phone: "123456789",
          city: "City",
          postcode: "12345",
          password: "Password1",
        });
      }).timeout(10000);

      it("returns Bad Request", async () => {
        expect(this.response.statusCode).to.eql(400);
      });
      it("includes a message", async () => {
        expect(this.response.body["msg"]).to.include("invalid");
      });

      it("includes a code", async () => {
        expect(this.response.body["errorCode"]).to.eql(102);
      });

      it("includes a list of invalid properties", async () => {
        expect(this.response.body["invalidProperties"]["email"][0]).to.eq(
          "IS_TAKEN"
        );
      });
    },
  },
});
