# Case Study at SkyGate - API
I'm creating a simple web app as part of my internship at SkyGate internetworking GmbH

This is the Backend-Api part.

Its a "plain" PHP no framework.
## Development Environment

- Start the environment
    ```
    composer start
    ```
- Stop the environment
    ```
    composer stop
    ```

- See the logs
    ```
    composer logs
    ```

## Testing

- Start the Unit tests
    ```
    composer run-unit-tests
    ```
- Start the Integration tests
    ```
    composer run-integration-tests
    ```
- Start the Endpoint tests (first start the dev-environment)
    ```
    composer run-endpoint-tests
    ```
- Run only a specific endpoint test file
    - Start the dev-environment
    - cd into tests/Via-HTTP/
    - run:
        ```
        npm run endpoint-test-only -- <relative/path/to/file>
        ```


- Create a Unit test code-coverage analyses
    ```
    composer run-unit-test-code-coverage
    ```
- Open the code-coverage analyses
    ```
    composer open-code-coverage
    ```