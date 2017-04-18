=================
Accounts provider
=================

An accounts provider can be implemented by an LDAP server (RFC2307 schema only)
or by an Active Directory domain.

It is possible to connect this system to a **remote** accounts provider or
to install a **local** one.

Remote LDAP
===========

To bind a **remote** ldap accounts provider the following fields are displayed.

Host name or IP
    Insert the IP address or host name of the LDAP server.

TCP port
    Required only if the service uses a non-standard TCP port.

A connection is attempted to the given IP/TCP port. If the connection succeeds,
review and complete the configuration of the additional form.

LDAP server URI
    Use ``ldaps://`` scheme to enable SSL encryption. Specify non-standard TCP
    port by appending ``:portnumber``, after the host name. For instance:
    ``ldap://myhost.domain:3389``.

STARTTLS
    Enable or disable TLS encryption. By default it is always enabled if an
    authenticated bind is configured.

Base DN
    Perform any LDAP search under the given DN.

User DN
    If specified, perform user LDAP searches under the given DN, otherwise fall
    back to :guilabel:`Base DN`.

Group DN
    If specified, perform group LDAP searches under the given DN, otherwise fall
    back to :guilabel:`Base DN`.

Anonymous bind
    If the LDAP server allows to browse the LDAP tree under :guilabel:`Base DN`
    anonymously, this is the preferred choice.

Authenticated bind
    Provide the bind credentials by filling :guilabel:`Bind DN` and
    :guilabel:`Bind Password` fields. These credentials are used also by
    additional modules that require a direct and read-only connection with the
    LDAP server, like NextCloud, WebTop, SOGo and ejabberd.


Join a remote Active Directory domain
=====================================

To join a **remote** Active Directory accounts provider the following fields are displayed.

DNS domain name
    Name of the Active Directory domain, also known as *long* domain name.

AD DNS server
    IP address of the domain DNS server (usually the IP of a domain controller).

Credentials for joining the domain
    Provide the :guilabel:`User name` and :guilabel:`Password` of an AD account
    with the privilege of *joining a computer to the domain*. Note that the
    default **administrator** account could be disabled.

If the join operation is successful, review and complete the configuration of
the additional form.

LDAP server URI
    Use ``ldaps://`` scheme to enable SSL encryption. Specify non-standard TCP
    port by appending ``:portnumber``, after the host name. For instance:
    ``ldap://myhost.domain:3389``.

STARTTLS
    Enable or disable TLS encryption. By default it is always enabled if an
    authenticated bind is configured.

Base DN
    Perform any LDAP search under the given DN.

User DN
    If specified, perform user LDAP searches under the given DN, otherwise fall
    back to :guilabel:`Base DN`.

Group DN
    If specified, perform group LDAP searches under the given DN, otherwise fall
    back to :guilabel:`Base DN`.

Read-only bind credentials
    Provide the bind credentials by filling :guilabel:`Bind DN` and
    :guilabel:`Bind Password` fields. These credentials are used by additional
    modules that require a direct and read-only connection with the LDAP server
    and do not support GSSAPI authentication, like NextCloud, WebTop, SOGo and
    ejabberd.


New Local Active directory domain
=================================

DNS domain name
    Name of the Active Directory domain, also known as *long* domain name.

NetBIOS domain name
    This value also known as "workgroup" could be required to access SMB
    resources, like *Shared folders*.  It is usually the leftmost
    part of the DNS domain suffix and must be up to 15 characters long.

Domain Controller IP address
    Provide an unused IP address from a green network range. It is allocated to
    ``nsdc``, the Linux Container that runs the Samba Active Directory domain
    controller.
