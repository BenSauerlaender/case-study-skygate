const { request, expect } = require("../config");

request("http://localhost")
  .get("/api/breeds/image/random")
  .end(function (err, res) {
    if (err) throw err;
    console.log(res.body);
  });
