<?php

$L['Account_Title'] = 'Users and groups';
$L['Account_Description'] = 'Manage users and groups, configure the domain identity and authentication providers';
$L['Account_Tags'] = 'sssd user group realm domain account kerberos ldap';
$L['Select_Title'] = 'Backend choice';

$L['AuthProvider_header'] = 'Select the users and groups provider of domain "${0}"';
$L['Provider_none_label'] = 'None (disabled)';
$L['Provider_ldap_label'] = 'LDAP';
$L['Provider_ad_label'] = 'Active Directory';
$L['AdDns_label'] = 'DNS server IP address';
$L['LdapUri_label'] = 'Server IP address';
$L['BindProvider_label'] = 'Bind';
$L['NetbiosDomain_label'] = 'NetBIOS domain';

$L['login_label'] = 'Privileged user name';
$L['password_label'] = 'Password';
$L['Join_label'] = 'Join';
$L['Authenticate_header'] = 'Join the AD domain with privileged user credentials';

$L['NoConfig_header'] = 'Domain ${0}';

$L['AccountProvider_Error_32'] = 'Account provider error: no entries found. Check the LDAP bind credentials and Base DN in Accounts provider configuration';
$L['AccountProvider_Error_49'] = 'Account provider error: invalid credentials (${0})';
$L['AccountProvider_Error_49_710'] = 'Insufficent access rights (49/710): specify alternative LDAP bind credentials in Accounts provider configuration';
$L['AccountProvider_Error_4'] = 'Account provider warning: size limit exceeded (${0})';
$L['AccountProvider_Error_110'] = 'Account provider connection timed out';
$L['AccountProvider_Error_111'] = 'Account provider connection refused';

$L['valid_platform,ad-dns,srv_record,1'] = 'Does not seem an Active Directory domain controller';
