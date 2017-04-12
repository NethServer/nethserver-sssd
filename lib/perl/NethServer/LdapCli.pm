#
# Copyright (C) 2017 Nethesis S.r.l.
# http://www.nethesis.it - nethserver@nethesis.it
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
# along with NethServer.  If not, see COPYING.
#

use strict;
use NethServer::SSSD;
use Net::LDAP;
use Net::LDAP::Extra qw(AD);
use Net::LDAP::Control::Paged;
use Net::LDAP::Constant qw( LDAP_CONTROL_PAGED );
use Net::DNS;
use Authen::SASL qw(Perl);
use Sys::Hostname;

package NethServer::LdapCli;

=head1 NAME

NethServer::LdapCli -- connect to the accounts provider LDAP server

=cut

=head1 DESCRIPTION

See the connect() function

=cut

=head1 USAGE

 use NethServer::SSSD;
 use NethServer::LdapCli;

 $sssd = NethServer::SSSD->new();
 $ldap = NethServer::LdapCli($sssd);

 # $ldap is a Net::LDAP object instance connected to the LDAP accounts provider

=cut

=head1 SEE ALSO

L<NethServer::SSSD>

=cut

=head1 FUNCTIONS

=cut

=head2 connect

Takes a L<NethServer::SSSD> object with the current accounts provider
configuration.

Additional arguments are passed to the Net::LDAP->new() method.

Returns a L<Net::LDAP> object connected to the accounts provider LDAP server, or
undef if an error occurs.

=cut

sub connect
{
    my $sssd = shift;
    my @ldap_params = @_;

    my %config = ();
    my $sasl;

    my @ldap_hosts;
    if($sssd->isAD()) {
        @ldap_hosts =  _get_ldap_hosts(lc($sssd->{'Realm'}) || $sssd->{'Domain'});
    } else {
        @ldap_hosts = ($sssd->ldapURI());
    };

    # \@ldap_hosts
    my $ldap = Net::LDAP->new($ldap_hosts[0],  @ldap_params);

    if( ! $ldap) {
        warn("($!): $@\n");
        exit(1);
    }

    if($sssd->isAD() && $ldap->is_AD()) {
        _init_kerberos($sssd);
        $sasl = eval {
            local ($SIG{__DIE__});
            Authen::SASL->new(mechanism => 'GSSAPI')->client_new('ldap', $ldap->{'net_ldap_host'});
        };
    } elsif($sssd->startTls()) {
        $ldap->start_tls('verify' => 'none');
    }

    my $result;

    if($sasl) {
        $result = $ldap->bind('', 'sasl' => $sasl);
    } elsif ($config{'bindDN'} && $config{'bindPassword'}) {
        $result = $ldap->bind($config{'bindDN'}, 'password' => $config{'bindPassword'});
    } else {
        $result = $ldap->bind();
    }

    if($result->is_error()) {
        warn(sprintf("(%s) %s", $result->code(), $result->error()));
        exit($result->code());
    }

    return $ldap;
}

=head2 paged_search

Repeatedly executes Net::LDAP::search(), issuing "pagedResultsControl" as
specified by RFC2696.

Arguments:

=over 4

=item

$sssd, L<NethServer::SSSD|NethServer::SSSD> object instance

=cut

=item

$ldap, L<Net::LDAP|Net::LDAP> object instance

=cut

=back

Any other argument is passed as-is to Net::LDAP::search().

For each returned Net::LDAP::Entry object, the given callback function is
invoked.

Sample invocation:

    $result = NethServer::LdapCli::paged_search($sssd, $ldap,
        'base' => $sssd->userDN(),
        'scope' => 'subtree',
        'deref' => 'never',
        'timelimit' => $opt_t,
        'filter' => $config{'userfilter'},
        'callback' => \&_cb_user_counter,
    );


=cut

sub paged_search
{
    my $sssd = shift;
    my $ldap = shift;
    my %args = @_;
    my $callback = $args{'callback'};
    $args{'callback'} = sub {
        my $message = shift;
        my $entry = shift;

        if(! defined $entry || ref($entry) ne 'Net::LDAP::Entry') {
            return;
        }

        $callback->($message, $entry);

        $message->pop_entry();
    };

    my $page = Net::LDAP::Control::Paged->new('size' => ($sssd->{'LdapPageSize'} || '1000'));

    my $cookie;

    $args{'control'} = [ $page ];

    while(1) {
        my $message = $ldap->search(%args);

        # exit loop on error
        if($message->code()) {
            last;
        }

        my $control = $message->control(Net::LDAP::Constant::LDAP_CONTROL_PAGED) or last;
        $cookie = $control->cookie;

        # exit loop if
        if( ! defined($cookie) || ! length($cookie)) {
            last;
        }

        $page->cookie($cookie);
    }

    # clean up on abnormal loop exit.
    if (defined($cookie) && length($cookie)) {
        # We had an abnormal exit, so let the server know we do not want any more

        $page->cookie($cookie);
        $page->size(0);
        $ldap->search( %args );
    }

}

### internals

sub _get_ldap_hosts
{
    my $domain = shift;
    my $resolver = Net::DNS::Resolver->new();

    my $packet = $resolver->query('_ldap._tcp.dc._msdcs.' . $domain, 'SRV');
    if( ! $packet) {
        return undef;
    }

    return map { $_->type eq 'SRV' ? ($_->target . ':' . $_->port) : () } Net::DNS::rrsort("SRV", "priority", $packet->answer);

}

sub _init_kerberos
{
    my $sssd = shift;
    $ENV{'KRB5CCNAME'} = sprintf("/tmp/krb5cc_%d", $<);
    my ($systemName, $domainName) = split(/\./, Sys::Hostname::hostname(), 2);

    system(qw(/usr/bin/klist -s));
    if($? != 0) {
        system(qw(/usr/bin/kinit -k), sprintf('%s$@%s', uc($systemName), uc($sssd->{'Realm'} || $sssd->{'Domain'})));
    }
}

1;