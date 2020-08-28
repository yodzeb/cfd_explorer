#!/usr/bin/perl

use JSON;
use strict;
use warnings;

undef $/;
my $url = "https://parapente.ffvl.fr/cfd/selectionner-les-vols";

open (PAGE, "curl -s $url |");
my $page = <PAGE>;

$page =~ s/.*(select class="form-control form-select" id="edit-1650-1-7" name="1650-1-7">.*?<\/select>).*/$1/ms;

my $clubs = {
    "clubs" => []
};

while ($page =~ /option value="(\d+)">([^\<]+)<\/option/mgs ) {
    my $id = $1;
    my $name = $2;
    $name =~ s/((?<=[^\s]))(\w)/"$1".lc($2)/ge; # quite proud of this one.
    push @{$clubs->{'clubs'}}, { "id" => $id, "name" => $name };
}

if (-e "/var/www/cfd/ressources") {
    open(OUTPUT, "> /var/www/cfd/ressources/clubs.json");
    print OUTPUT encode_json($clubs);
    close (OUTPUT);
}
else {
    open(OUTPUT, "> /var/www/cfd_explorer/ressources/clubs.json");
    print OUTPUT encode_json($clubs);
    close (OUTPUT);
}

print encode_json($clubs);
