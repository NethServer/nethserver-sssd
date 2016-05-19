Name:           nethserver-sssd
Version:        0.0.1
Release:        1%{?dist}
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
' > %{name}-%{version}-filelist

%files -f %{name}-%{version}-filelist
%doc COPYING
%doc README.rst
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
* Fri Jan 29 2016 Davide Principi <davide.principi@nethesis.it>
- Initial version
