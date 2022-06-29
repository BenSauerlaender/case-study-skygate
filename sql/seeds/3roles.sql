INSERT INTO role
    (name, permissions)
VALUES 
    ("user","getSelf getOwnContactData deleteSelf changeOwnPassword changeOwnEmail logoutSelf getAllUsers"),
    ("admin","getAllUsersContactData deleteAllUsers changeOwnPassword changeOwnEmail logoutSelf getAllUsers"),
    ("test","");
