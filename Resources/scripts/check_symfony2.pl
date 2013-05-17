#!/usr/bin/perl
#
# Perl Nagios check script using Multi-check format 
#  for use with LiipMonitorBundle to monitor symfony apps.
#
# This uses https on the common /utils/health/run or /health/run directories 
#  when providing just the host hame (-H) option
# OR
# Uses the exact address provided with the address (-A) option
#
#
# Usage - Nagios config:
#
# define command{  
#         command_name    check_symfony_health
#         command_line    $USER1$/check_symfony2 -w 1  -c 1 -H $HOSTNAME$ -n USERNAME -p USERPASSWORD
# }
#
# Created by: Troy Germain, troy.germain@gmail.com
# Updated on: 2/7/2013


use strict;
use warnings;
use Getopt::Std;
use WWW::Mechanize;
use JSON;

my %opts = (
    H => undef,
    u => undef,
    A => undef,
    p => undef,
    w => 1,
    c => 1,
);
getopts 'H:A:u:p:w:c', \%opts;
die "Missing (-H or -A)\n" unless ($opts{H} or $opts{A});

my $java="<br /><SCRIPT LANGUAGE=\'JavaScript\'> function Toggle(node) { if (node.nextSibling.style.display == \'none\') { if (node.childNodes.length > 0) { node.childNodes.item(0).replaceData(0,1,String.fromCharCode(8211)) } node.nextSibling.style.display = \'block\' } else { if (node.childNodes.length > 0) { node.childNodes.item(0).replaceData(0,1,\'+\') } node.nextSibling.style.display = \'none\' } } </SCRIPT>";
my $htmldivide="<A onClick=\'Toggle(this)\' style=\'color:#4444FF;line-height:0.3em;font-size:1.5em;cursor:crosshair\'>+</A><DIV style=\'display:none\'><div><table style=\'border-left-width:1px; border-right-width:0px; border-left-style:dotted\' id=multi_table>";
my $htmlend="</table></div>";


my $totalnumber = 0;
my $passnumber = 0;
my $failnumber = 0;
my $jsn;
my $content;
my $browser = WWW::Mechanize->new();
my $donechecks;
my $color = '#33FF00';
my $message;

if (defined($opts{u}) && defined($opts{p}) ) {
    $browser->credentials($opts{u}, $opts{p});
}
if (defined($opts{A})) {
    eval {
        $browser->get( $opts{A} );
        $jsn = $browser->content();
           $content = decode_json( $jsn );
    };
    if ($@) {
        print "Symfony Health Unknown - Failed connecting to health page: $opts{A}\n";
       exit 3;
    }
} else {
    my $url = "https://" . $opts{H} . "/monitor/health/run";

    eval {
        $browser->get( $url );
        $jsn = $browser->content();
        $content = decode_json( $jsn );
    };
    if ($@) {
        print "Symfony Health Unknown - Failed connecting to health page for $opts{H}\n";
        exit 3;
    }
}

foreach (@{$content->{checks}})
{
    $totalnumber += 1;
    if ($_->{status}) {
        $failnumber += 1;
        $color = '#F83838';
    } else {
        $passnumber += 1;
        $color = '#33FF00';
    }
    $donechecks .= "<tr style=\'font-size:8pt\'><td nowrap><table style=\'background-color:${color}\'><tr style=\'vertical-align:middle\'><td style=\'font-size:6pt\'> ${totalnumber} </td></tr></table></td><td></td><td></td><td> $_->{checkName} </td><td> $_->{message} </td></tr>";
}

if ($failnumber > 0) {
    $message = "Symfony Heath Critical - ${totalnumber} checks ran ${failnumber} failed ${java}";
} else {
    $message = "Symfony Heath OK - ${totalnumber} checks ran ${passnumber} ok ${java}";
}

$message .= $htmldivide;

$message .= $donechecks;

$message .= $htmlend;

print "$message\n";

if ($failnumber < $opts{w} or $failnumber == 0 ) {
    exit 0;
}

if ($failnumber >= $opts{c}) {
    exit 2;
}
    exit 1;
