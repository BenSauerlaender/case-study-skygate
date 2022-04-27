CREATE TABLE IF NOT EXISTS role(
    role_id     INT             AUTO_INCREMENT,
    name        VARCHAR(100)    NOT NULL,

    role_read   BOOLEAN         NOT NULL DEFAULT FALSE, 
    role_write  BOOLEAN         NOT NULL DEFAULT FALSE,
    role_delete BOOLEAN         NOT NULL DEFAULT FALSE,

    user_read   BOOLEAN         NOT NULL DEFAULT FALSE,
    user_write  BOOLEAN         NOT NULL DEFAULT FALSE,
    user_delete BOOLEAN         NOT NULL DEFAULT FALSE,

    created_at DATETIME(3)      NOT NULL DEFAULT NOW(3) ,
    updated_at DATETIME(3)      NOT NULL DEFAULT NOW(3) ON UPDATE NOW(3),

    PRIMARY KEY (role_id),
    UNIQUE (name)
)