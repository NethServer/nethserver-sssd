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

package NethServer::AccountsList;

use strict;
use NethServer::SSSD;
use Data::Dumper;

=head1 NAME

NethServer::HiddenAccounts -- Functions that filters out system entries from
Active Directory and LDAP providers

=cut

sub is_system_user
{
    my $self = shift;
    my $user = shift;
    return $self->is_in_db($user, 'db_users');
}

sub is_system_group
{
    my $self = shift;
    my $group = shift;
    return $self->is_in_db($group, 'db_groups');
}

sub __sid2string
{
    my $sid = shift;
    my ($revision_level, $sub_authority_count, $authority, @sub_authorities) = unpack 'C C xxN V*', $sid;
    if($sub_authority_count != scalar @sub_authorities) {
        return undef;
    }
    return join '-', 'S', $revision_level, $authority, @sub_authorities;
}

sub is_in_db
{
    my $self = shift;
    my $entry = shift;
    my @dbs = @_;

    if( ! $entry) {
        return 0;
    }

    if($self->{'isAD'}) {
        my $sid = __sid2string($entry);
        if($sid) {
            $sid =~ s/^(S-1-5-21-)(?:\d+-)*(\d+)$/$1domain-$2/;
            foreach my $db (@dbs) {
                if(grep { $sid eq $_ } @{$self->{$db}}) {
                    return 1;
                }
            }
            return 0;
        }
    }

    foreach my $db (@dbs) {
        if(grep { lc($entry) eq $_ } @{$self->{$db}}) {
            return 1;
        }
    }
    return 0;
}

sub __load_db
{
    my $self = shift;
    my @systemGroups;
    my @systemUsers;

    my $entryFilter;

    if($self->{'sssd'}->isAD()) {
        $entryFilter = sub () {
            if($_ =~ /^S-\d/) {
                return $_;
            }
            return lc($_);
        };
    } else {
        $entryFilter = sub () {
            if($_ =~ /^S-\d/) {
                return ();
            }
            return lc($_);
        }
    }

    open(FH, '<', $self->{'groups'});
    chomp(@systemGroups = <FH>);
    close(FH);
    @systemGroups = map { $entryFilter->($_) } @systemGroups;

    $self->{'db_groups'} = \@systemGroups;

    open(FH, '<', $self->{'users'});
    chomp(@systemUsers = <FH>);
    close(FH);
    @systemUsers = map { $entryFilter->($_) } @systemUsers;

    $self->{'db_users'} = \@systemUsers;
}

sub new
{
    my $class = shift;

    my $sssd = NethServer::SSSD->new();
    my $self = {
        'users' => '/etc/nethserver/system-users',
        'groups' => '/etc/nethserver/system-groups',
        @_,
        'db_users' => [],
        'db_groups' => [],
        'sssd' => $sssd,
        'isAD' => $sssd->isAD,
    };

    bless ($self, $class);

    $self->__load_db();

    return $self;
}

1;
