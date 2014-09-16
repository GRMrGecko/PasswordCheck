#!/usr/bin/env perl
#
#  Copyright (c) 2014 Mr. Gecko's Media (James Coleman). http:#mrgeckosmedia.com/
#
# Permission is hereby granted, free of charge, to any person obtaining a copy
# of this software and associated documentation files (the "Software"), to deal
# in the Software without restriction, including without limitation the rights
# to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
# copies of the Software, and to permit persons to whom the Software is
# furnished to do so, subject to the following conditions:
#
# The above copyright notice and this permission notice shall be included in
# all copies or substantial portions of the Software.
#
# THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
# IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
# FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
# AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
# LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
# OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
# THE SOFTWARE.
#

#DBD::mysql
use DBI;

use POSIX;
use DateTime;

sub trim($) {
	my $string = shift;
	$string =~ s/^\s+//;
	$string =~ s/\s+$//;
	$string =~ s/^\t+//;
	$string =~ s/\t+$//;
	$string =~ s/\n//g;
	$string =~ s/\r//g;
	return $string;
}
sub clean($) {
	my $string = shift;
	$string =~ s/"/""/g;
	$string =~ s/\n//;
	$string =~ s/\r//;
	return "\"$string\"";
}

$dbHost = "127.0.0.1";
$dbName = "passwords";
$dbUser = "root";
$dbPassword = "password";

#print localtime(time).": Connecting to DataBase\n";

$dbConnection = DBI->connect("DBI:mysql:$dbName;host=$dbHost", $dbUser, $dbPassword) || die "Could not connect to database: $DBI::errstr";

open(passwords, ">/Users/grmrgecko/Desktop/passwords.csv");

my $result = $dbConnection->prepare("SELECT `id`,`password` FROM emailplaintext LIMIT 2;");
$result->execute();
while (@data = $result->fetchrow_array()) {
	my $i = 0;
	foreach (@data) {
		if ($i!=0) {
			print passwords ",";
		}
		print passwords clean($_);
		$i++;
	}
	print passwords "\n";
}
$result->finish();

close(passwords);

$dbConnection->disconnect();