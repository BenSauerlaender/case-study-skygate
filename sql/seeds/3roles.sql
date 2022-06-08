INSERT INTO role
    (name, permissions)
VALUES 
    ("user","user:{all}:{userID};user:read:{all}"),
    ("admin","user:{all}:{all}"),
    ("test","");
