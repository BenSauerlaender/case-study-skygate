const request = require("supertest")("http://localhost:3000/api/v1");
const expect = require("chai").expect;

module.exports = {
  request,
  expect,
};
