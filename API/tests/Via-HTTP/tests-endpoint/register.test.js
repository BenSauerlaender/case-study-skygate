const { request, expect } = require("../config");
const { makeSuite } = require("../helper");
const { getEmail } = require("../emailHelper.js");

makeSuite("/register", {
  POST: {
    "without a body": () => {
      it("makes api call", async () => {
        this.response = await request.post("/register");
      });

      it("returns Bad Request", async () => {
        expect(this.response.statusCode).to.eql(400);
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
    "without all properties": () => {
      it("makes api call", async () => {
        this.response = await request
          .post("/register")
          .send({ email: "email@mail.de" });
      });

      it("returns Bad Request", async () => {
        expect(this.response.statusCode).to.eql(400);
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
    "with invalid properties": () => {
      it("makes api call", async () => {
        this.response = await request.post("/register").send({
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
      it("includes a message", async () => {
        expect(this.response.body["msg"]).to.include("invalid");
      });

      it("includes a list of invalid properties", async () => {
        expect(this.response.body["invalidProperties"]["postcode"][0]).to.eq(
          "INVALID_TYPE"
        );
      });
    },
    "with all (and valid) properties": () => {
      it("makes api call", async () => {
        this.response = await request.post("/register").send({
          email: "email@mail.de",
          name: "Name",
          phone: "123456789",
          city: "City",
          postcode: "12345",
          password: "Password1",
        });
      });

      it("returns Created", async () => {
        expect(this.response.statusCode).to.eql(201);
      });
      it("includes no body", async () => {
        expect(this.response.body).to.be.empty;
      });
    },
  },
  GET: () => {
    it("makes api call", async () => {
      this.response = await request.get("/register");
    });
    it("returns Method Not Allowed", async () => {
      expect(this.response.statusCode).to.eql(405);
    });
    it("includes explanation", async () => {
      expect(this.response.body["msg"]).to.include("don't allow this method");
    });
    it("includes list of available Methods", async () => {
      expect(this.response.body["availableMethods"][0]).to.eq("POST");
    });
  },
  PUT: () => {
    it("makes api call", async () => {
      this.response = await request.put("/register");
    });
    it("returns Method Not Allowed", async () => {
      expect(this.response.statusCode).to.eql(405);
    });
    it("includes explanation", async () => {
      expect(this.response.body["msg"]).to.include("don't allow this method");
    });
    it("includes list of available Methods", async () => {
      expect(this.response.body["availableMethods"][0]).to.eq("POST");
    });
  },
  DELETE: () => {
    it("makes api call", async () => {
      this.response = await request.delete("/register");
    });
    it("returns Method Not Allowed", async () => {
      expect(this.response.statusCode).to.eql(405);
    });
    it("includes explanation", async () => {
      expect(this.response.body["msg"]).to.include("don't allow this method");
    });
    it("includes list of available Methods", async () => {
      expect(this.response.body["availableMethods"][0]).to.eq("POST");
    });
  },
  PATCH: () => {
    it("makes api call", async () => {
      this.response = await request.patch("/register");
    });
    it("returns Method Not Allowed", async () => {
      expect(this.response.statusCode).to.eql(405);
    });
    it("includes explanation", async () => {
      expect(this.response.body["msg"]).to.include("don't allow this method");
    });
    it("includes list of available Methods", async () => {
      expect(this.response.body["availableMethods"][0]).to.eq("POST");
    });
  },
});
