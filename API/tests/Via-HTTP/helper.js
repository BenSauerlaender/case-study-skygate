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
      console.log("Connected to Database!");
      done();
    });
  },
  afterAll(done) {
    connection.end();
    console.log("Disconnected from Database!");
    done();
  },
};

/**
 * Clears/Resets the database
 *
 * TODO: The nesting is terrible
 */
exports.clearDB = (done) => {
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
              console.log("Database reset");
              done();
            }
          );
        }
      );
    }
  );
};
