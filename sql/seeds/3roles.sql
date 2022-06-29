INSERT INTO role
    (name, permissions)
VALUES 
    ("user","getAllUsers changeOwnContactData deleteSelf changeOwnPassword changeOwnEmail logoutSelf"),
    ("admin","getAllUsers changeAllUsersContactData deleteAllUsers changeOwnPassword changeOwnEmail logoutSelf"),
    ("guest","getSelf changeOwnContactData deleteSelf changeOwnPassword changeOwnEmail logoutSelf");
