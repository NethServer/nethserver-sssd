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

    return ($self->isLocalProvider() && $self->isLdap()) ? __builtinSuffix() : __domain2suffix($self->{'Domain'});
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
        return "cn=". substr($machineName, 0, 15) . ",cn=Computers,$suffix";
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
    return $self->{'UserDN'} if ($self->{'UserDN'});

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

Return LDAP bind user BindUser if set,
"ldapservice" if ldap is local, machine account if is AD

=cut

sub bindUser {
    my $self = shift;
    return $self->{'BindUser'} if ($self->{'BindUser'});

    if ($self->isLdap() && $self->isLocalProvider() ) {
        return 'ldapservice';
    } elsif ($self->isAD()) {
        my $machineName = qx(/usr/bin/testparm -s --parameter-name='netbios name' 2>/dev/null);
        chomp($machineName);
        return substr($machineName, 0, 15) . '$';
    }

    return '';
}


=head2 new

Create a NethServer::SSSD instance.

=cut

sub new 
{
    my $class = shift;

    my $db = esmith::ConfigDB->open_ro();
    my $sssd = $db->get('sssd');
    my $nsdc = $db->get('nsdc');

    my $self = {
        'nsdc' => {
            'status' => 'disabled',
            $nsdc ? $nsdc->props() : ()
        },
        'AdDns' => '',
        'Provider' => 'ldap',
        'LdapURI' => '',
        'BaseDN' => '',
        'BindDN' => '',
        'BindPassword' => '',
        'UserDN' => '',
        'Domain' => $db->get('DomainName')->value(),
        'StartTls' => '',
        $sssd->props()
    };

    if ($self->{'LdapURI'} eq '') {
        my $host = 'localhost';
        my $proto = 'ldap';
        if($self->{'Provider'} eq 'ad') {
            $host = $self->{'Domain'};
            $proto = 'ldap'; # FIXME Active Directory might want simple binds over SSL
        }
        $self->{'LdapURI'} = "$proto://$host";
    }


    bless ($self, $class);
    return $self;
}

1;
