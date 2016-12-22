Name:           nethserver-sssd
Version: 1.1.1
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
Requires:  samba-common-tools
Requires: krb5-workstation
Requires: python-tdb

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
