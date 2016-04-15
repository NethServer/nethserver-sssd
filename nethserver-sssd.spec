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

%description
NethServer SSSD configuration

%prep
%setup

%build
%{__install} -d root%{perl_vendorlib} 
cp -av lib/perl/NethServer root%{perl_vendorlib}
%{makedocs}
perl createlinks

%install
(cd root   ; find . -depth -print | cpio -dump %{buildroot})
%{genfilelist} %{buildroot} > %{name}-%{version}-filelist

%files -f %{name}-%{version}-filelist
%doc COPYING
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


%changelog
* Fri Jan 29 2016 Davide Principi <davide.principi@nethesis.it>
- Initial version
