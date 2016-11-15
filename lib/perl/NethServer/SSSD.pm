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
use Net::LDAP;
use Net::DNS::Resolver;
use NethServer::Password;
use Carp;
use URI;

sub __domain2suffix {
    my $domain = `hostname -d`;
    chomp $domain;
    $domain =~ s/\./,dc=/g;
    $domain = "dc=" . $domain;
    return $domain;
}

sub __builtinSuffix {
    return 'dc=directory,dc=nh';
}

sub __findHost {
    my $db = shift;
    my $provider = $db->get_prop('sssd','Provider') || 'ldap';
    if ($provider eq 'ldap') {
        my $ldap = Net::LDAP->new('localhost') || return '';
        return 'localhost';
    } elsif ($provider eq 'ad') {
        my $res = Net::DNS::Resolver->new();
        my $domain = $db->get('DomainName')->value();
        my $reply = $res->send("_ldap._tcp.".$domain, "SRV");
        if ($reply) {
            foreach my $rr ($reply->answer) {
                next unless $rr->type eq "SRV";
                return $rr->target;
            }
        }
    }
    return '';
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
  my $sssd = new NethServer::SSSD();

  print $sssd->host();

=cut

=head1 FUNCTIONS


=head2 isLdap

Return true if SSSD is configured to use LDAP,
false otherwise

=cut

sub isLdap {
    my $self = shift;
    return ( ($self->{'Provider'} || '') eq 'ldap');
}


=head2 isAD

Return true if SSSD is configured to use AD (or Samba 4),
false otherwise

=cut

sub isAD {
    my $self = shift;
    return ( ($self->{'Provider'} || '') eq 'ad');
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
    return $self->{'BaseDN'} if (defined $self->{'BaseDN'} && $self->{'BaseDN'});

    return $self->isAD() ? __domain2suffix() : __builtinSuffix();
}

=head2 bindDN

Return LDAP BindDN if set,
otherwise a bind DN calculated from the server domain.

=cut

sub bindDN {
    my $self = shift;
    my $suffix = '';
    return $self->{'BindDN'} if (defined $self->{'BindDN'} && $self->{'BindDN'});

    if (defined $self->{'BaseDN'} && $self->{'BaseDN'}) {
        $suffix = $self->{'BaseDN'};
    } else {
        $suffix = $self->isAD() ? __domain2suffix() : __builtinSuffix();
    }

    if ($self->isLdap()) {
        return "cn=ldapservice,$suffix";
    } else {
        my $machineName = qx(/usr/bin/testparm -s --parameter-name='netbios name' 2>/dev/null);
        chomp($machineName);
        return "cn=". substr($machineName, 0, 15) . ",cn=Computers,$suffix";
    }
}

=head2 userDN

Return LDAP UserDN if set,
otherwise a user DN calculated from the server domain.

=cut

sub userDN {
    my $self = shift;
    my $suffix = '';
    return $self->{'UserDN'} if (defined $self->{'UserDN'} && $self->{'UserDN'});

    if (defined $self->{'BaseDN'} && $self->{'BaseDN'}) {
        $suffix = $self->{'BaseDN'};
    } else {
        $suffix = $self->isAD() ? __domain2suffix() : __builtinSuffix();
    }
    
    if ($self->isLdap()) {
        return "ou=People,$suffix";
    } else {
        return "cn=Users,$suffix";
    }
}

=head2 groupDN

Return LDAP GroupDN if set,
otherwise a group DN calculated from the server domain.

=cut

sub groupDN {
    my $self = shift;
    my $suffix = '';
    return $self->{'UserDN'} if (defined $self->{'UserDN'} && $self->{'UserDN'});

    if (defined $self->{'BaseDN'} && $self->{'BaseDN'}) {
        $suffix = $self->{'BaseDN'};
    } else {
        $suffix = $self->isAD() ? __domain2suffix() : __builtinSuffix();
    }
    
    if ($self->isLdap()) {
        return "ou=Groups,$suffix";
    } else {
        return $suffix;
    }
}


=head2 bindPassword

Return LDAP bind password UserDN if set,
an empty string otherwise.

=cut

sub bindPassword {
    my $self = shift;
    return $self->{'BindPassword'} if (defined $self->{'BindPassword'} && $self->{'BindPassword'});

    if ($self->isLdap() && ($self->host() eq 'localhost' || $self->host() eq '127.0.0.1') ) {
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
    return $self->{'BindUser'} if (defined $self->{'BindUser'} && $self->{'BindUser'});

    if ($self->isLdap() && ($self->host() eq 'localhost' || $self->host() eq '127.0.0.1') ) {
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
    my $self = {};
    
    my $db = esmith::ConfigDB->open_ro();
    my $sssd = $db->get('sssd') || die("No sssd key defined");
    my %props = $sssd->props();
    foreach my $key (keys %props) { 
       $self->{$key} = $props{$key};
    }
    $self->{'config'} = $db;
    if (!defined $self->{'LdapURI'} || $self->{'LdapURI'} eq '') {
        my $host = __findHost($db);
        if ($host) {
            $self->{'LdapURI'} = "ldap://$host:389";
        }
    }


    bless ($self, $class);
    return $self;
}

1;
