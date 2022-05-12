var connection = null;

exports.mochaHooks = {
  /**
   * Makes connection to the database
   *
   * Executes before all endpoint Tests
   */
  beforeAll(done) {
    var mysql = require("mysql");

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
exports.makeSuite = async (routeName, methods) => {
  //testsuit for one route
  describe(routeName, function () {
    //for each httpMethod
    for (const [methodName, tests] of Object.entries(methods)) {
      //if there is only one test scenario: take it
      if (typeof tests === "function") {
        //methodtestsuite is only one scenario
        methodTestSuite = getTestScenario(tests);
      } else {
        //methodTestSuite contains testsuites for each scenario
        methodTestSuite = () => {
          //for each test Scenario
          for (const [testName, assertions] of Object.entries(tests)) {
            //testsuit for one scenario
            describe(testName, getTestScenario(assertions));
          }
        };
      }
      //testsuite for one httpMethod
      describe(`${methodName} ${routeName}`, methodTestSuite);
    }
  });
};

/**
 * Takes a function with all it-assertions and add before and after statements
 *
 * @param {function} assertions //a bunch of it-assertions
 * @returns A function that represents one test scenario
 */
const getTestScenario = (assertions) => {
  return () => {
    //run before each test-scenario
    before(async function () {
      await clearDB();
    });

    //it-assertions
    assertions();

    after(function () {});
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
                //console.log("resetDatabase");
                resolve();
              }
            );
          }
        );
      }
    );
  });
};
