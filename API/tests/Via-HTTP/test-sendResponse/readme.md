# sendResponse Test
Here are unit tests for the sendResponse function
The sendResponse function takes an Response object. And set all the HTTP-responses accordingly to the Response object.

ATTENTION: The Tests are depends on BaseResponse!

The Test is a bit weird:
- It requires to be running the docker development environment.
- Than it exchange the index.php with replace.index.php
- Than it runs the tests in sendResponse.test.js
    - The tests will send http request to the docker container.
    - The replace.index.php will catch these requests, constructs a response object (here it depends on BaseResponse) accordingly to the requested test and calls sendRequest().
    - Than the sendResponse.test.js will evaluate if the response is expected
- finally the index.php will be changed back.
