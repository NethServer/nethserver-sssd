======
Users
======

A system user is required to access many services provided by
the server (email, shared folders, etc..).

Each user is characterized by a pair of credentials (user and
password). A newly created user account remains locked until it has
set a password. A blocked user can not use the services of
servers that require authentication.

Create / Modify
===============

Allows you to create or modify user data The username cannot
be changed after creation.

User
------

Basic information about the user. These fields are
 required.

Username
    The *Username* will be used to access the services. It can
    contain only lowercase letters, numbers, dashes, dots, and
    underscore (_) and must start with a lowercase letter. For
    example, "luisa", "jsmith" and "liu-jo" is a valid user name, and
    "4Friends", "Franco Blacks" and "aldo / mistake" are not.
Name
    It is the user's real name. For example, "John"
Surname
    The user's last name
Groups
    Using the search bar, you can select the groups to
    which the user will be added. The user can belong to several groups.

Change Password
---------------

Allows to set an initial password, or change the user's password.

The password must meet the following requirements:

* Must have at least 5 different characters
* Must not be present in the dictionaries of common words
* Must be different from your username
* Can not have repetitions of patterns formed by 3 more characters (for example the password As1.$ AS1. $ is not valid)

Lock / Unlock
----------------

Allows you to lock or unlock a user. User data 
will not be deleted.

Delete
-------

Delete the user. All user data will be deleted.

Details
--------

This section collects information on the organization to which
the user belongs to and is optional. The default values can be
specify the menu item *Data organization*.

For the following fields, you can specify a custom value,
otherwise it is the setting made by the module "Data
organization, "available only to the system administrator.

* Company
* Office
* Address
* City
* Phone


Service
-------

This section contains the list of services to which the user is
enabled.

.. raw:: html

   {{{INCLUDE NethServer_Module_User_Plugin_*.html}}}
