CREATE TABLE IF NOT EXISTS refreshToken(
    user_id     INT              NOT NULL,
    count       INT              NOT NULL DEFAULT 0,

    created_at  DATETIME(3)      NOT NULL DEFAULT NOW(3) ,
    updated_at  DATETIME(3)      NOT NULL DEFAULT NOW(3) ON UPDATE NOW(3),

    PRIMARY KEY (user_id),
    FOREIGN KEY (user_id) REFERENCES user(user_id)
)