INSERT INTO role
    (name, permissions)
VALUES 
    ("user","user:{all}:{userID}"),
    ("admin","user:{all}:{all}"),
    ("test","");
