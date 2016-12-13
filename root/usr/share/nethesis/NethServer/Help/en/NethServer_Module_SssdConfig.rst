
.. raw:: html

   {{{INCLUDE NethServer_Module_SssdConfig_*.html}}}


=================
Account providers
=================

Is possible to connect this system to a **remote** LDAP or Active Directory
account provider.  To install a **local** LDAP or Active Directory account
provider, go to the Software center page.

LDAP
====

Server URI
    Insert the IP address or host name of LDAP server.

Active Directory
================

NetBIOS domain
    This value also known as "workgroup" could be required to access SMB
    resources, like *Shared folders*.  It is set automatically to the leftmost
    part of the DNS domain suffix.  It is truncated to 15 characters.

DNS server IP address
    IP address of domain controller DNS server (usually the IP of the domain controller itself)

Advanced settings
=================

LDAP or Windows user name
    Specify the DN to perform the LDAP BIND operation.  The actual DN value
    depends on the LDAP server. For instance, it could be
    ``uid=user,ou=People,dc=domain,dc=com``.  Active Directory servers allow
    also NT style account names, like ``COMPANY\user``.

Password
    The password for the LDAP BIND operation. It is stored in clear-text format
    in the *configuration* e-smith database.

Base DN
    Perform any LDAP search under the given DN.

User DN
    If specified, perform user LDAP searches under the given DN, otherwise fall
    back to :guilabel:`Base DN`.

Group DN
    If specified, perform group LDAP searches under the given DN, otherwise fall
    back to :guilabel:`Base DN`.

LDAP connection URI
    The URI syntax is ``ldap://`` or ``ldaps://`` as scheme, the ``hostname`` or
    ``IP address`` and optionally the port number suffix (``:389``, for
    instance).

STARTTLS
    By default for LDAP providers, if a BIND operation is required and the
    connection is not protected by SSL, the STARTTLS command is attempted.  The
    yes/no value override this behavior.

