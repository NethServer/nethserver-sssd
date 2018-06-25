#
# Copyright (C) 2016 Nethesis S.r.l.
# http://www.nethesis.it - support@nethesis.it
# 
# This script is part of NethServer.
# 
# NethServer is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License,
# or any later version.
# 
# NethServer is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License
# along with NethServer.  If not, see <http://www.gnu.org/licenses/>.
#

package NethServer::SSSD;

use strict;
use esmith::ConfigDB;
use NethServer::Password;
use Sys::Hostname;
use Carp;
use URI;

sub __domain2suffix {
    my $domain = shift;
    $domain =~ s/\./,dc=/g;
    $domain = "dc=" . $domain;
    return $domain;
}

sub __builtinSuffix {
    return 'dc=directory,dc=nh';
}

=head1 NAME

NethServer::SSSD -- module to retrive current LDAP configuration.
It supports both Active Directory and OpenLDAP providers.

=cut

=head1 DESCRIPTION

The library can be used to configure all software which needs to 
connect directly to the LDAP server.

=cut

=head1 USAGE

Usage example:

  use NethServer::SSSD;
  my $sssd = NethServer::SSSD->new();

  print $sssd->host();

=cut

=head1 FUNCTIONS


=head2 isLdap

Return true if SSSD is configured to use LDAP,
false otherwise

=cut

sub isLdap {
    my $self = shift;
    return ( $self->{'Provider'} eq 'ldap');
}


=head2 isAD

Return true if SSSD is configured to use AD (or Samba 4),
false otherwise

=cut

sub isAD {
    my $self = shift;
    return ( $self->{'Provider'} eq 'ad');
}

=head2 isLocalProvider

Return true if the account provider is running on the local machine. False otherwise.

Note that the Samba Active Directory container is considered "local", even if it
actually runs behind a "remote" IP.

=cut

sub isLocalProvider {
    my $self = shift;
    my $host = $self->host();
    if($self->isLdap() && ($host eq '127.0.0.1' || $host eq 'localhost')) {
        return 1;
    } elsif($self->isAD() && $self->{'nsdc'}->{'status'} eq 'enabled') {
        return 1;
    }
    return 0;
}

=head2 startTls

Return true ('1') if the LDAP STARTTLS command is required, false ('') otherwise

=cut

sub startTls {
    my $self = shift;
    if($self->{'StartTls'} eq 'enabled') {
        return '1';
    } elsif($self->{'StartTls'} eq 'disabled') {
        return '';
    }

    return $self->bindDN()
        && $self->bindPassword()
        && $self->isLdap()
        && ! $self->isLocalProvider()
        && $self->ldapURI() !~ /^ldaps/
    ;
}

=head2 ldapURI

Return LDAP URI.

=cut

sub ldapURI {
    my $self = shift;
    return $self->{'LdapURI'};
}

=head2 ldapUriDn

Return LDAP URI, with DNS SRV records resolution. This is required for GSSAPI
LDAP bind.

=cut

sub ldapUriDn {
    my $self = shift;
    my $dn = __domain2suffix(lc($self->{'Realm'}));
    
    $dn =~ s/,/%2C/g;
    $dn =~ s/=/%3D/g;
    
    return 'ldap:///' . $dn;
}

=head2 port

Return LDAP port if set, 
otherwisedefault 389 port.

=cut

sub port {
    my $self = shift;
    my $uri = URI->new($self->{'LdapURI'});
    if ($uri->port()) {
        return $uri->port()
    }
    return "389";
}


=head2 host

Return LDAP host if set.

=cut

sub host {
    my $self = shift;
    my $uri = URI->new($self->{'LdapURI'}) || return '';

    return $uri->host();
}

=head2 baseDN

Return LDAP BaseDN if set,
otherwise a base DN calculated from the server domain.

=cut

sub baseDN {
    my $self = shift;
    return $self->{'BaseDN'} if ($self->{'BaseDN'});

    return ($self->isLocalProvider() && $self->isLdap()) ? __builtinSuffix() : __domain2suffix(lc($self->{'Realm'}));
}

=head2 bindDN

Return LDAP BindDN if set,
otherwise a bind DN calculated from the server domain.

=cut

sub bindDN {
    my $self = shift;
    my $suffix = '';
    return $self->{'BindDN'} if ($self->{'BindDN'});

    $suffix = $self->baseDN();

    if ($self->isLdap() && $self->isLocalProvider()) {
        return "cn=ldapservice,$suffix";
    } elsif($self->isAD()) {
        my $machineName = qx(/usr/bin/testparm -s --parameter-name='netbios name' 2>/dev/null);
        chomp($machineName);
        my $workgroup = qx(/usr/bin/testparm -s --parameter-name='workgroup' 2>/dev/null);
        chomp($workgroup);
        return sprintf('%s\%s$', $workgroup, substr($machineName, 0, 15));
    }

    return ""; # implies anonymous binds
}

=head2 userDN

Return LDAP UserDN if set,
otherwise a user DN calculated from the server domain.

=cut

sub userDN {
    my $self = shift;
    my $suffix = '';
    return $self->{'UserDN'} if ($self->{'UserDN'});

    $suffix = $self->baseDN();

    if ($self->isLdap() && $self->isLocalProvider()) {
        return "ou=People,$suffix";
    }

    return $suffix;
}

=head2 groupDN

Return LDAP GroupDN if set,
otherwise a group DN calculated from the server domain.

=cut

sub groupDN {
    my $self = shift;
    my $suffix = '';
    return $self->{'GroupDN'} if ($self->{'GroupDN'});

    $suffix = $self->baseDN();
    
    if ($self->isLdap() && $self->isLocalProvider()) {
        return "ou=Groups,$suffix";
    }

    return $suffix;
}


=head2 bindPassword

Return LDAP bind password UserDN if set,
an empty string otherwise.

=cut

sub bindPassword {
    my $self = shift;
    return $self->{'BindPassword'} if ($self->{'BindPassword'});

    if ($self->isLdap() && $self->isLocalProvider()) {
        return NethServer::Password::store('ldapservice');
    } elsif ($self->isAD()) {
        my $workgroup = qx(/usr/bin/testparm -s --parameter-name=workgroup 2>/dev/null);
        chomp($workgroup);
        my $secret = "";
        my $pyscript = <<EOF;
import tdb
db = tdb.open("/var/lib/samba/private/secrets.tdb")
print db["SECRETS/MACHINE_PASSWORD/${workgroup}"].rstrip("\\000")
EOF

        pipe RH, WH;
        open(OLDIN, "<&STDIN");
        open(STDIN, "<&RH");
        if(open(PIPE, "-|")) {
            close(RH);
            print WH $pyscript;
            close(WH);
            $secret = <PIPE>;
            chomp($secret);
        } else {
            exec("/usr/bin/python -");
        }
        close(PIPE);
        close(RH);
        open(STDIN, "<&OLDIN");
        return $secret;
    }

    return '';
}


=head2 bindUser

Return LDAP bind user BindUser if set, otherwise return the first value of
the DN from bindDN(). If bindDN() returns an AD user name format, 
like DOMAIN\sAMAccountName or the UPN string, try to extract the user identifier 
part and returns it.

=cut

sub bindUser {
    my $self = shift;
    return $self->{'BindUser'} if ($self->{'BindUser'});

    my $bindUser = [split(/,/, $self->bindDN())]->[0] || '';
    $bindUser =~ s/^.+=//;
    if ($self->isAD()) {
        $bindUser =~ s/^.+\\//;
        $bindUser =~ s/@.+$//;
    }

    return $bindUser;
}


=head2 new

Create a NethServer::SSSD instance. The new() method accepts a list of 
key => value pairs that override default values and sssd props values from DB.

Example:

    my $settings = NethServer::SSSD->new();
    my $settings_probe = NethServer::SSSD->new('Provider' => 'ldap', 'LdapURI' => 'ldap://1.2.3.4');

=cut

sub new 
{
    my $class = shift;

    my ($systemName, $domainName) = split(/\./, Sys::Hostname::hostname(), 2);
    my %sssdProps = (
        'status' => 'disabled',
        'Provider' => 'none',
        'DiscoverDcType' => 'dns',
    );
    my %nsdcProps = (
        'status' => 'disabled'
    );

    my $db = esmith::ConfigDB->open_ro();
    if($db && $db->get('sssd')) {
        %sssdProps = (%sssdProps, $db->get('sssd')->props());
    }
    if($db && $db->get('nsdc')) {
        %nsdcProps = (%nsdcProps, $db->get('nsdc')->props());
    }

    my $self = {
        'nsdc' => \%nsdcProps,
        'AdDns' => '',
        'Provider' => '',
        'LdapURI' => '',
        'BaseDN' => '',
        'BindDN' => '',
        'BindPassword' => '',
        'UserDN' => '',
        'Realm' => '',
        'StartTls' => '',
        %sssdProps,
        @_
    };

    if($self->{'Realm'} eq '') {
        $self->{'Realm'} = uc($domainName);
    }

    if ($self->{'LdapURI'} eq '') {
        my $host = '127.0.0.1';
        my $proto = 'ldap';
        if($self->{'Provider'} eq 'ad') {
            $host = lc($self->{'Realm'});
            $proto = 'ldaps'; # Require encryption by default
        }
        $self->{'LdapURI'} = "$proto://$host";
    }


    bless ($self, $class);
    return $self;
}

1;
