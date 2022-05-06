CREATE TABLE IF NOT EXISTS role(
    role_id     INT             AUTO_INCREMENT,
    name        VARCHAR(100)    NOT NULL,

    permissions VARCHAR(200)    NOT NULL DEFAULT "", 

    created_at DATETIME(3)      NOT NULL DEFAULT NOW(3) ,
    updated_at DATETIME(3)      NOT NULL DEFAULT NOW(3) ON UPDATE NOW(3),

    PRIMARY KEY (role_id),
    UNIQUE (name)
)