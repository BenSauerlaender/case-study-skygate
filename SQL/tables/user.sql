CREATE TABLE IF NOT EXISTS user(
    user_id             INT             AUTO_INCREMENT,

    email               VARCHAR(100)    NOT NULL,
    name                VARCHAR(100)    NOT NULL,
    postcode            VARCHAR(5)      NOT NULL,
    city                VARCHAR(50)     NOT NULL,
    phone               VARCHAR(20)     NOT NULL,

    hashed_pass         VARCHAR(60)     NOT NULL,

    verified            BOOLEAN         NOT NULL ,
    verification_code   VARCHAR(10), 

    role_id             INT             NOT NULL,

    created_at          DATETIME(3)     NOT NULL DEFAULT NOW(3) ,
    updated_at          DATETIME(3)     NOT NULL DEFAULT NOW(3) ON UPDATE NOW(3),

    PRIMARY KEY (user_id),
    FOREIGN KEY (role_id) REFERENCES role(role_id),
    UNIQUE (email)
)
