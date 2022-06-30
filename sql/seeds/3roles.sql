INSERT INTO role
    (name, permissions)
VALUES 
    ("user","getAllUsers changeOwnContactData deleteSelf changeOwnPassword changeOwnEmail logoutSelf"),
    ("admin","getAllUsers changeAllUsersContactData deleteAllUsers changeOwnPassword changeAllUsersPasswordsPrivileged changeOwnEmail changeAllUsersEmailPrivileged logoutSelf"),
    ("guest","getSelf changeOwnContactData deleteSelf changeOwnPassword changeOwnEmail logoutSelf");
