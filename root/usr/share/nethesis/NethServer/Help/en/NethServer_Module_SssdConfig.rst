
.. raw:: html

   {{{INCLUDE NethServer_Module_SssdConfig_*.html}}}


========================
Authentication providers
========================

Is possible to connect this system to a remote LDAP or Active Directory
account provider.  To install an LDAP or Active Directory account provider on
this system, go to the Software center page.

LDAP
====

Server URI
    to configure an external LDAP provider insert the IP address or host name of LDAP server.

Active Directory
=================

NetBIOS domain
    This value also known as "workgroup" could be required to access SMB
    resources, like *Shared folders*.  It is set automatically to the leftmost
    part of the DNS domain suffix.  It is truncated to 15 characters.

DNS server IP address
    IP address of domain controller DNS server (usually the IP of the domain controller itself)


