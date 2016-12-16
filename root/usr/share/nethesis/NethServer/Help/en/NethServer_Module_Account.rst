

=====
Users
=====

A system user is required to access many services provided by
the server (email, shared folders, etc..).

You can connect to a **remote** LDAP or Active Directory Accounts provider, or
install a **local** one. Creation and edit of users is available only if you
install a **local** Accounts provider.  If users are read from a **remote** one,
the users and groups lists can be **only viewed**.

Each user is characterized by a pair of credentials (user and
password). A newly created user account remains locked until it has
set a password. A blocked user can not use the services of
servers that require authentication.

Create / Modify
===============

Allows you to create or modify user data. The user name cannot
be changed after creation.

User
----

Basic information about the user. These fields are required.

User name
    The *Username* will be used to access the services. It can
    contain only lowercase letters, numbers, dashes, dots, and
    underscore (_) and must start with a lowercase letter. For
    example, "luisa", "jsmith" and "liu-jo" is a valid user name, and
    "4Friends", "Franco Blacks" and "aldo / mistake" are not.

Name
    It is the user's real name. For example, "John Snow"

Groups
    Using the search bar, you can select the groups to
    which the user will be added. The user can belong to several groups.

Password expiration
    Disable password expiration for a single user on the server.

Remote shell (SSH)
    Allow user to log in to system using SSH secure shell


Set Password / Change Password
------------------------------

Allows to set an initial password, or change the user's password.

The password must meet the following requirements:

* Must have at least 5 different characters
* Must not be present in the dictionaries of common words
* Must be different from your username
* Can not have repetitions of patterns formed by 3 more characters (for example the password As1.$ AS1. $ is not valid)

Lock / Unlock
-------------

Allows you to lock or unlock a user. User data will not be deleted.

Delete
-------

Delete the user. All user data will be deleted.

These actions are available only if there is a user backend installed

======
Groups
======

Create, modify or remove groups of users used to assign user permissions and
access to services.

Create / Modify
===============

Group
-----

Create a new group, adding members to the group.

Group Name
    May contain only lowercase letters, numbers, hyphens, and underscores and
    must start with a lowercase letter. For example, "sales", "beta3" and
    "rev_net" are valid names, while "3d", "Sales Office" and "q & a" are not.

Membership
    Allows you to search for users on the server. Users can be added to the
    group with the * Add * button. To delete the users listed use the button
    *X*.

Delete
======

This action removes a group. Any shared folder associated to the group is not
erased.
