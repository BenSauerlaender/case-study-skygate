INSERT INTO role
    (name, permissions)
VALUES 
    ("user","getAllUsers changeOwnContactData deleteSelf changeOwnPassword changeOwnEmail logoutSelf"),
    ("admin","getAllUsers changeAllUsersContactData deleteAllUsers changeOwnPassword changeAllUsersPasswordsPrivileged changeOwnEmail changeAllUsersEmailPrivileged changeAllUsersRoles logoutSelf"),
    ("guest","getSelf changeOwnContactData deleteSelf changeOwnPassword changeOwnEmail logoutSelf");
