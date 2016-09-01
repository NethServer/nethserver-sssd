========================
Authentication providers
========================

Is possible to connect this system to an external LDAP or Active Directory account provider. Is NOT possible to change account provider once configured.
It's also possible to install an LDAP or Active Directory account provider in this system, installing nethserver-directory or nethserver-dc package.

LDAP
====

Server URI
    to configure an external LDAP provider insert the IP address or host name of LDAP server.

Active Directory
=================

DNS server IP address
    IP address of domain controller DNS server (usually the address of domain controller itself)

===============================
Domain controller configuration
===============================

Set the IP address for the machine that is going to run the Samba Active Directory Domain Controller (DC). The chosen IP address must satisfy three conditions:

* The IP address must be in the same subnet range of a green network.
* The green network must be bound to a bridged interface.
* The IP address must not be used by any other machine.

Create a bridge interface for the green network
    Automatically create a bridge on green interface for the DC machine

=====
Users
=====

A system user is required to access many services provided by
the server (email, shared folders, etc..).

You can connect to an external LDAP or Active Directory user base or install a user backend. Creation and edit of users is available only if you install a backend, if users are from a remote system this list is read only.

Each user is characterized by a pair of credentials (user and
password). A newly created user account remains locked until it has
set a password. A blocked user can not use the services of
servers that require authentication.

Create / Modify
===============

Allows you to create or modify user data The username cannot
be changed after creation.

These actions are available only if there is a user backend installed

User
----

Basic information about the user. These fields are
 required.

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

This action is available only if there is a user backend installed

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

Create, modify or remove groups of users
used to assign user permissions and access to services
or email shared folders.

You can connect to an external LDAP or Active Directory or install a user backend. Creation and edit of groups is available only if you install a backend, if groups are from a remote system this list is read only


Create / Modify
===============

Group
-----

Create a new group, adding members to the group.

Group Name
    May contain only lowercase letters, numbers,
    hyphens, and underscores and must start with
    a lowercase letter. For example, "sales", "beta3" and "rev_net"
    are valid names, while "3d", "Sales Office" and "q & a" are
    not.
Membership
    Allows you to search for users on the server. Users
    can be added to the group with the * Add * button. To delete the
    users listed use the button *X*.

This action is available only if there is a user backend installed

Delete
======

This action removes the defined groups.

This action is available only if there is a user backend installed
