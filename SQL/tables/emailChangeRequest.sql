CREATE TABLE IF NOT EXISTS emailChangeRequest(
    request_id          INT             AUTO_INCREMENT,

    user_id             INT             NOT NULL,
    new_email           VARCHAR(100)    NOT NULL,
    verification_code   VARCHAR(10)     NOT NULL, 

    created_at          DATETIME(3)     NOT NULL DEFAULT NOW(3) ,
    updated_at          DATETIME(3)     NOT NULL DEFAULT NOW(3) ON UPDATE NOW(3),

    PRIMARY KEY (request_id),
    FOREIGN KEY (user_id) REFERENCES user(user_id),
    UNIQUE (new_email),
    UNIQUE (user_id)
)