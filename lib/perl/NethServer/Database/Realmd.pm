#
# Copyright (C) 2016 Nethesis S.r.l.
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
package NethServer::Database::Realmd;

use Net::DBus;

sub TIEHASH {
    my $class = shift;

    my $bus = Net::DBus->system();

    my $self = {
        'bus' => $bus,
        'service' => $bus->get_service("org.freedesktop.realmd"),
        'keys' => [],
    };
    bless $self, $class;
    return $self;
}

sub FETCH {
    my $self = shift;
    my $key = shift;

    if( ! $self->EXISTS($key)) {
        return undef;
    }

    my $realm = $self->_get_object($key);
    my $value = 'realmd';

    $value .= '|RealmName|' . $realm->RealmName;
    $value .= '|DomainName|' . $realm->DomainName;
    $value .= '|Details|' . (join(',', map { $_->[0] . ':' . $_->[1] } @{$realm->Details}));

    return $value;
}

sub EXISTS {
    my $self = shift;
    my $key = shift;
    return grep { $_ eq $key } @{$self->_get_keys()};
}

sub FIRSTKEY {
    my $self = shift;
    $self->{'keys'} = $self->_get_keys();
    return $self->NEXTKEY();
}

sub NEXTKEY {
    my $self = shift;
    my ($k, $v) = each($self->{'keys'});
    return $v;
}

sub _get_object {
    my $self = shift;
    my $key = shift;
    foreach (@{$self->{'service'}->get_object("/org/freedesktop/realmd/Sssd")->Realms}) {
        my $o = $self->{'service'}->get_object($_);
        if($o->Name eq $key) {
            return $o;
        }
    }
    return undef;
}

sub _get_keys {
    my $self = shift;
    my $key = shift;
    return [map {$self->{'service'}->get_object($_)->Name} @{$self->{'service'}->get_object("/org/freedesktop/realmd/Sssd")->Realms}];
}

1;