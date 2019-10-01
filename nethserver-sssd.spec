Name:           nethserver-sssd
Version: 1.5.0
Release: 1%{?dist}
Summary:        NethServer SSSD configuration

License:        GPLv3+
URL: %{url_prefix}/%{name}
Source0:        %{name}-%{version}.tar.gz
BuildArch:      noarch
BuildRequires:  nethserver-devtools
Requires:       realmd, sssd, adcli, nethserver-lib
# send expiring password warnings: 
Requires: mailx, postfix, anacron
Requires: samba-common-tools >= 4.9.1
Requires: krb5-workstation
Requires: python-tdb
Requires: tdb-tools
Requires: php-cli, openldap-clients

%description
NethServer SSSD configuration

%prep
%setup

%build
%{__install} -d root%{perl_vendorlib} 
cp -av lib/perl/NethServer root%{perl_vendorlib}
%{makedocs}
perl createlinks
mkdir -p root/%{_nseventsdir}/group-create
mkdir -p root/%{_nseventsdir}/group-delete
mkdir -p root/%{_nseventsdir}/group-modify
mkdir -p root/%{_nseventsdir}/user-create
mkdir -p root/%{_nseventsdir}/user-delete
mkdir -p root/%{_nseventsdir}/user-lock
mkdir -p root/%{_nseventsdir}/user-modify
mkdir -p root/%{_nseventsdir}/user-unlock
mkdir -p root/%{_nseventsdir}/password-policy-update
mkdir -p root/%{_nseventsdir}/password-modify
mkdir -p root/var/lib/nethserver/home

%install
(cd root   ; find . -depth -print | cpio -dump %{buildroot})
%{genfilelist} %{buildroot} | sed '
\|^%{_sysconfdir}/sudoers.d/20_nethserver_sssd$| d
\|/var/lib/nethserver/home| d
\|%{_nseventsdir}/password-modify| d
' > %{name}-%{version}-filelist

%files -f %{name}-%{version}-filelist
%doc COPYING
%doc README.rst
%doc scripts
%config %attr (0440,root,root) %{_sysconfdir}/sudoers.d/20_nethserver_sssd
%config %ghost %attr(0644,root,root) /etc/backup-config.d/nethserver-sssd.include
%dir %{_nseventsdir}/%{name}-update
%dir %{_nseventsdir}/group-create
%dir %{_nseventsdir}/group-delete
%dir %{_nseventsdir}/group-modify
%dir %{_nseventsdir}/user-create
%dir %{_nseventsdir}/user-delete
%dir %{_nseventsdir}/user-lock
%dir %{_nseventsdir}/user-modify
%dir %{_nseventsdir}/user-unlock
%dir %{_nseventsdir}/password-policy-update
%dir %{_nseventsdir}/password-modify
%dir /var/lib/nethserver/home


%changelog
* Tue Oct 01 2019 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.5.0-1
- Samba 4.9 setspn and keytab management upgrade - NethServer/dev#5840

* Mon Mar 04 2019 Davide Principi <davide.principi@nethesis.it> - 1.4.8-1
- DiscoverDcType not respected by kinit - Bug NethServer/dev#5721

* Mon Dec 10 2018 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.4.7-1
- Account expired cannot change password - Bug NethServer/dev#5670

* Fri Dec 07 2018 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.4.6-1
- Detach user-cleanup event from user-delete - NethServer/dev#5624

* Mon Dec 03 2018 Davide Principi <davide.principi@nethesis.it> - 1.4.5-1
- Sysadmin script to delete users - NethServer/dev#5657

* Tue Nov 20 2018 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.4.4-1
- DC: network unreachable if green is configured as DHCP - Bug NethServer/dev#5635

* Mon Nov 05 2018 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.4.3-1
- Integration of arm specific requirements - NethServer/dev#5610

* Tue Oct 02 2018 Davide Principi <davide.principi@nethesis.it> - 1.4.2-1
- Change confusing UI labels in Dashboard and MailAccount User - NethServer/dev#5596

* Thu Aug 09 2018 Davide Principi <davide.principi@nethesis.it> - 1.4.1-1
- Enhancement: (un)mask password fields - NethServer/dev#5554

* Mon Jul 02 2018 Davide Principi <davide.principi@nethesis.it> - 1.4.0-1
- Send AD queries to a preferred DC - NethServer/dev#5534
- Template of krb5.conf - NethServer/dev#5535
- Do not use Samba machine secret in Domain accounts page - NethServer/nethserver-sssd#98

* Tue May 08 2018 Stephane de Labrusse <stephdl@de-labrusse.fr> - 1.3.8-1
- Silence warning message from NethServer::SSSD - Bug NethServer/dev#5476

* Thu Mar 29 2018 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.3.7-1
- Bad home directory owner after ns6 rsync-upgrade - Bug NethServer/dev#5432
- Dashboard hangs after hotsync-restore and restore-config - Bug NethServer/dev#5434

* Thu Jan 25 2018 Davide Principi <davide.principi@nethesis.it> - 1.3.6-1
- LDAP connection error with remote MS AD - Bug NethServer/dev#5415

* Wed Jan 10 2018 Davide Principi <davide.principi@nethesis.it> - 1.3.5-1
- AD join fails after changing server name - Bug NethServer/dev#5399
- AD access for LDAP simple auth applications - NethServer/dev#5396

* Tue Dec 12 2017 Davide Principi <davide.principi@nethesis.it> - 1.3.4-1
- Change the shell access after the user creation with SAMBA4 AD -- NethServer/dev#5391
- Use UTF-8 for tsv files -- NethServer/nethserver-sssd#83

* Thu Nov 09 2017 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.3.3-1
- SSSD: changes to remote AD account provider aren't applied - Bug NethServer/dev#5377
- Accounts Provider: probe AD for SSL/TLS support - NethServer/dev#5365

* Mon Oct 16 2017 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.3.2-1
- Cosmetic: add translation for AccountProvider_Error_1

* Mon Oct 16 2017 Davide Principi <davide.principi@nethesis.it> - 1.3.1-1
- Samba automatic updates - NethServer/dev#5360

* Thu Jul 20 2017 Davide Principi <davide.principi@nethesis.it> - 1.3.0-1
- Web interface for changing nsdc IP address - NethServer/dev#5330

* Fri Jun 30 2017 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.2.2-1
- AD account provider: web interface doesn't correctly display users with password expiration - Bug NethServer/dev#5318
- Remove Password warning slider
- New validator: AD Realm must be different from host FQDN

* Mon May 22 2017 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.2.1-1
- Default userPrincipalName is not an email address - Bug NethServer/dev#5284

* Wed May 10 2017 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.2.0-1
- Account provider: allow reset of local providers - NethServer/dev#5252
- Accounts provider guided configuration - NethServer/dev#5253
- Upgrade from NS 6 via backup and restore - NethServer/dev#5234

* Mon Mar 06 2017 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.1.9-1
- LDAP users not listed with remote accounts provider - Bug NethServer/dev#5229

* Wed Mar 01 2017 Davide Principi <davide.principi@nethesis.it> - 1.1.8-1
- Samba secrets.tdb is missing from backup set - Bug NethServer/dev#5228

* Fri Feb 10 2017 Davide Principi <davide.principi@nethesis.it> - 1.1.7-1
- Help the sysadmin on configuring the accounts provider - NethServer/dev#5215

* Mon Jan 30 2017 Davide Principi <davide.principi@nethesis.it> - 1.1.6-1
- pre-backup-config: expand nethserver-sssd.include -- NethServer/nethserver-sssd#47

* Mon Jan 30 2017 Davide Principi <davide.principi@nethesis.it> - 1.1.5-1
- Domain admins members are not granted full server-manager access - Bug NethServer/dev#5209

* Mon Jan 16 2017 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.1.4-1
- DC: restore configuration fails - NethServer/dev#5188

* Tue Jan 10 2017 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.1.3-1
- Web interface: add missing account provider errors - NethServer/nethserver-sssd#42

* Tue Jan 03 2017 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.1.2-1
- UI users and groups list items are not sorted - Bug NethServer/dev#5182
- Join credentials not honoured - Bug NethServer/dev#5181

* Thu Dec 22 2016 Davide Principi <davide.principi@nethesis.it> - 1.1.1-1
- Invalid AD machine account credentials after update - Bug NethServer/dev#5177

* Thu Dec 15 2016 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.1.0-1
- Enable LDAPs protocol on Active Directory clients - NethServer/dev#5161
- Notify changes to NethServer::SSSD clients - NethServer/dev#5164
- Store locally AD credentials - NethServer/dev#5165
- Authenticated binds to a remote LDAP account provider - NethServer/dev#5158
- Set the members of administrators group - NethServer/dev#5168
- bindDN() returns local domain suffix - Bug NethServer/dev#5153
- Default "admins" config DB record - NethServer/dev#5157

* Wed Nov 09 2016 Davide Principi <davide.principi@nethesis.it> - 1.0.8-1
- LDAP account with read-only privileges - NethServer/dev#5145

* Mon Nov 07 2016 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.0.7-1
- Legacy short user name support - NethServer/dev#5144
- Missing home dir on RSAT-created accounts - NethServer/dev#5137
- Useless Password policy page - NethServer/dev#5136
- Missing inline help in Password policy page - NethServer/5135

* Mon Oct 17 2016 Davide Principi <davide.principi@nethesis.it> - 1.0.6-1
- Display NetBIOS domain name on DC configuration page - NethServer/dev#5124

* Mon Oct 10 2016 Davide Principi <davide.principi@nethesis.it> - 1.0.5-1
- Controller provisioning fails with long domain name - Bug NethServer/dev#5116

* Fri Sep 23 2016 Davide Principi <davide.principi@nethesis.it> - 1.0.4-1
- Nsdc domain join fails with long hostname - Bug NethServer/dev#5110

* Thu Sep 01 2016 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.0.3-1
- UI stale after joining a remote account provider - Bug NethServer/dev#5097

* Mon Aug 01 2016 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.0.2-1
- Accounts: web interface tweaks - NethServer/dev#5073
- sssd-save event: execute system-adjust. NethServer/dev#5071

* Thu Jul 21 2016 Stefano Fancello <stefano.fancello@nethesis.it> - 1.0.1-1
- NethServer::SSSD: can't use method port() - Bug NethServer/dev#5051

* Thu Jul 07 2016 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.0.0-1
- First NS7 release

* Fri Jan 29 2016 Davide Principi <davide.principi@nethesis.it>
- Initial version
