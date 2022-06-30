const { request, expect } = require("./config");
const fs = require("fs");
let jwt = require("jsonwebtoken");
let connection = null;

require("dotenv").config({ path: "./../test.env" });

exports.godToken = jwt.sign(
  {
    id: 999,
    perm: "getAllUsers changeAllUsersContactData deleteAllUsers changeOwnPassword changeAllUsersPasswordsPrivileged changeOwnEmail changeAllUsersEmailPrivileged logoutSelf",
    exp: Math.floor(Date.now() / 1000) + 30000000,
  },
  process.env.ACCESS_TOKEN_SECRET
);
exports.mochaHooks = {
  /**
   * Makes connection to the database
   *
   * Executes before all endpoint Tests
   */
  beforeAll(done) {
    let mysql = require("mysql");

    //read env variables
    require("dotenv").config({ path: "./../test.env" });

    //create connection
    connection = mysql.createConnection({
      host: process.env.MYSQL_HOST,
      user: process.env.MYSQL_USER,
      password: process.env.MYSQL_PASSWORD,
    });

    connection.connect(function (err) {
      //if fails: throw error
      if (err) throw err;

      //else:
      //console.log("Connected to Database!");
      done();
    });
  },
  afterAll(done) {
    connection.end();
    //console.log("Disconnected from Database!");
    done();
  },
};

/**
 * Constructs a testSuite for one route.
 * A route has multiple methods with one or more tests with multiple assertions.
 * Before each Test the Database will be reset.
 *
 * @param {string} routeName  The name of the route to test
 * @param {object} methods    A method with HTTPMethods as properties.
 *      Each Method is a either a single test scenario or an object with multiple test scenario as properties.
 *      Each test scenario is a function, that provide it-assertions.
 */
exports.makeSuite = async (seeds, routeName, methods) => {
  //testsuite for one route
  describe(routeName, function () {
    //for each httpMethod
    for (const [methodName, tests] of Object.entries(methods)) {
      methodTests = getMethodTests(tests, routeName, methodName, seeds);
      describe(`${methodName} ${routeName}`, methodTests);
    }
  });
};

const getMethodTests = (tests, routeName, methodName, seeds) => {
  //if there is only one test scenario: take it
  if (typeof tests === "function") {
    return getTestScenario(tests, routeName, methodName, seeds);
  } else {
    //methodTestSuite contains testsuites for each scenario
    return () => {
      //for each test Scenario
      for (const [testName, assertions] of Object.entries(tests)) {
        //testsuite for one scenario
        describe(
          testName,
          getTestScenario(assertions, routeName, methodName, seeds)
        );
      }
    };
  }
};

/**
 * Takes a function with all it-assertions and add before and after statements
 *
 * @param {function} assertions //a bunch of it-assertions
 * @returns A function that represents one test scenario
 */
const getTestScenario = (assertions, path, method, seeds) => {
  return () => {
    //run before each test-scenario
    before(async function () {
      await clearDB();
      for (let i = 0; i < tables.length; i++) {
        await createTable(tables[i]);
      }
      for (let i = 0; i < seeds.length; i++) {
        await seedDB(seeds[i]);
      }
    });

    //it-assertions
    assertions(path, method);

    after(function () {});
  };
};

/**
 *
 * @returns Assertions that assert this method is not allowed
 */
exports.notAllowed = () => {
  return (path, method) => {
    let cleanedPath = path.replaceAll(/{[a-zA-Z]*}/g, "0");
    it("is not allowed", async () => {
      switch (method) {
        case "POST":
          this.response = await request.post(cleanedPath);
          break;
        case "GET":
          this.response = await request.get(cleanedPath);
          break;
        case "PUT":
          this.response = await request.put(cleanedPath);
          break;
        case "DELETE":
          this.response = await request.delete(cleanedPath);
          break;
        case "PATCH":
          this.response = await request.patch(cleanedPath);
          break;
        default:
          throw new Error("The method is not supported");
      }
      expect(this.response.statusCode).to.eql(405);
      expect(this.response.body["msg"]).to.include(
        "don't allow the requested method"
      );
      expect(this.response.body["availableMethods"]).to.have.lengthOf.above(0);
    });
  };
};

/**
 * Clears/Resets the database
 *
 * TODO: The nesting is terrible
 */
const clearDB = async () => {
  return await new Promise((resolve, reject) => {
    connection.query(
      `DROP DATABASE IF EXISTS ${process.env.MYSQL_DATABASE}; `,
      function (err) {
        if (err) throw err;
        connection.query(
          `CREATE DATABASE ${process.env.MYSQL_DATABASE}; `,
          function (err) {
            if (err) throw err;
            connection.query(
              `USE ${process.env.MYSQL_DATABASE}; `,
              function (err) {
                if (err) throw err;
                //console.log("db was reset");
                resolve();
              }
            );
          }
        );
      }
    );
  });
};

const tables = ["role", "user", "refreshToken", "emailChangeRequest"];

const createTable = async (table) => {
  return await new Promise((resolve, reject) => {
    fs.readFile(`../../sql/tables/${table}.sql`, "utf8", (err, data) => {
      if (err) throw err;
      connection.query(data, function (err) {
        if (err) throw err;
        //console.log(`table ${table} was created`);
        resolve();
      });
    });
  });
};

const seedDB = async (seed) => {
  return await new Promise((resolve, reject) => {
    fs.readFile(`../../sql/seeds/${seed}.sql`, "utf8", (err, data) => {
      if (err) throw err;
      connection.query(data, function (err) {
        if (err) throw err;
        //console.log(`db was seeded with ${seed}`);
        resolve();
      });
    });
  });
};

exports.createTable = createTable;
exports.clearDB = clearDB;
exports.tables = tables;
exports.seedDB = seedDB;
