<?
//
//  Copyright (c) 2014 Mr. Gecko's Media (James Coleman). http://mrgeckosmedia.com/
//
// Permission is hereby granted, free of charge, to any person obtaining a copy
// of this software and associated documentation files (the "Software"), to deal
// in the Software without restriction, including without limitation the rights
// to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
// copies of the Software, and to permit persons to whom the Software is
// furnished to do so, subject to the following conditions:
//
// The above copyright notice and this permission notice shall be included in
// all copies or substantial portions of the Software.
//
// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
// IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
// FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
// AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
// LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
// OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
// THE SOFTWARE.
//

error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_STRICT);

$_MGM = array();
$_MGM['version'] = "1";
$_MGM['title'] = "Password Check";
$_MGM['adminEmail'] = "support@gec.im";

$_MGM['DBType'] = "MYSQLPDO"; // MYSQL, POSTGRESQL, SQLITE.
$_MGM['DBPersistent'] = false;
$_MGM['DBHost'] = "localhost";
$_MGM['DBUser'] = "root";
$_MGM['DBPassword'] = "password";
$_MGM['DBName'] = "passwords"; // File location for SQLite.
$_MGM['DBPort'] = 0; // 3306 = MySQL Default, 5432 = PostgreSQL Default.
$_MGM['DBPrefix'] = "";
require_once("db{$_MGM['DBType']}.php");

putenv("TZ=US/Central");
$_MGM['time'] = time();
$_MGM['domain'] = $_SERVER['HTTP_HOST'];
$_MGM['domainname'] = str_replace("www.", "", $_MGM['domain']);
$_MGM['port'] = $_SERVER['SERVER_PORT'];
$_MGM['ssl'] = ($_SERVER['HTTPS']=="on");

if ($_SERVER['REMOTE_ADDR'])
	$_MGM['ip'] = $_SERVER['REMOTE_ADDR'];
if ($_SERVER['HTTP_PC_REMOTE_ADDR'])	
	$_MGM['ip'] = $_SERVER['HTTP_PC_REMOTE_ADDR'];
if ($_SERVER['HTTP_CLIENT_IP'])
	$_MGM['ip'] = $_SERVER['HTTP_CLIENT_IP'];
if ($_SERVER['HTTP_X_FORWARDED_FOR'])
	$_MGM['ip'] = $_SERVER['HTTP_X_FORWARDED_FOR'];

$_MGM['installPath'] = substr($_SERVER['SCRIPT_NAME'], 0, strlen($_SERVER['SCRIPT_NAME'])-strlen(end(explode("/", $_SERVER['SCRIPT_NAME']))));
if (!isset($_GET['d'])) {
	$tmp = explode("?", substr($_SERVER['REQUEST_URI'], strlen($_MGM['installPath'])));
	$tmp = urldecode($tmp[0]);
	if (substr($tmp, 0, 9)=="index.php")
		$tmp = substr($tmp, 10, strlen($tmp)-10);
	$_MGM['fullPath'] = $tmp;
} else {
	$tmp = $_GET['d'];
	if (substr($tmp, 0, 1)=="/")
		$tmp = substr($tmp, 1, strlen($tmp)-1);
	$_MGM['fullPath'] = $tmp;
}
if (strlen($_MGM['fullPath'])>255) error("The URI you entered is to large");
$_MGM['path'] = explode("/", strtolower($_MGM['fullPath']));

$_MGM['CookiePrefix'] = "";
$_MGM['CookiePath'] = $_MGM['installPath'];
$_MGM['CookieDomain'] = ".".$_MGM['domainname'];
$_MGM['referrer'] = (isset($_COOKIE['referrer']) ? $_COOKIE['referrer']=="true" : false);


function generateURL($path) {
	global $_MGM;
	return "http".($_MGM['ssl'] ? "s" : "")."://".$_MGM['domain'].(((!$_MGM['ssl'] && $_MGM['port']==80) || ($_MGM['ssl'] && $_MGM['port']==443)) ? "" : ":{$_MGM['port']}").$_MGM['installPath'].$path;
}


if ($_MGM['path'][0]=="api") {
	require("code/api.php");
} else if ($_MGM['path'][0]=="js" && $_MGM['path'][1]=="zxcvbn-async.js") {// To set correct path for dynamic loading.
	require("js/zxcvbn-async.php");
	exit();
}

if ($_MGM['path'][0]!="") {
	require("code/404.php");
}
require("code/index.php");
?>