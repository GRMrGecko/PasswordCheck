<?php
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

function connectToDatabase() {
	global $_MGM;
	if (isset($_MGM['DBConnection'])) closeDatabase();
	$_MGM['DBConnection'] = NULL;
	$options = array();
	if ($_MGM['DBPersistent'])
		$options = array(PDO::ATTR_PERSISTENT => true);
	try {
		$_MGM['DBConnection'] = new PDO("mysql:host={$_MGM['DBHost']};dbname={$_MGM['DBName']};charset=utf8", $_MGM['DBUser'], $_MGM['DBPassword'], $options);
		$_MGM['DBConnection']->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	} catch (Exception $e) {
		mail("Server Admin <{$_SU['adminEmail']}>", "MySQL Error", "URL: ".$_SERVER['SERVER_NAME'].$_SU['installPath'].$_SU['fullPath']."\n\nError ".$e->getMessage().": ".mysql_error());
		//echo $e->getMessage()."<br />\n";
		error("Failed to connect to database");
	}
	if ($_MGM['DBConnection']==NULL) error("Database Connection Failed");
}
function closeDatabase() {
	global $_MGM;
	if (isset($_MGM['DBConnection'])) {
		$_MGM['DBConnection'] = NULL;
	}
}
function escapeString($theString) {
	global $_MGM;
	return $_MGM['DBConnection']->quote($theString);
}
function quoteObject($theObject) {
	global $_MGM;
	if (is_null($theObject)) {
		return "''";
	} else if (is_string($theObject)) {
		return escapeString($theObject);
	} else if (is_float($theObject) || is_integer($theObject)) {
		return $theObject;
	} else if (is_bool($theObject)) {
		return ($theObject ? 1 : 0);
	}
	return "''";
}
function databaseQuery($format) {
	global $_MGM;
	$result = NULL;
	try {
		if (isset($_MGM['DBConnection'])) {
			$args = func_get_args();
			array_shift($args);
			$args = array_map("quoteObject", $args);
			$query = vsprintf($format, $args);
			//echo $query."\n";
			$result = $_MGM['DBConnection']->query($query);
		}
		//if ($result==NULL) error("Failed to run query on database");
	} catch (Exception $e) {
		mail("Server Admin <{$_MGM['adminEmail']}>", "MySQL Error", "URL: ".$_SERVER['SERVER_NAME'].$_MGM['installPath'].$_MGM['fullPath']."\n\nError ".$e->getMessage().": ".mysql_error());
		//echo $e->getMessage()."<br />\n";
		//error("Failed to run query on database");
	}
	return $result;
}
function databaseRowCount($theResult) {
	global $_MGM;
	if ($theResult==NULL)
		return 0;
	return $theResult->rowCount();
}
function databaseFieldCount($theResult) {
	global $_MGM;
	if ($theResult==NULL)
		return 0;
	return $theResult->columnCount();
}
function databaseLastID() {
	global $_MGM;
	$result = 0;
	if (isset($_MGM['DBConnection'])) {
		$result = $_MGM['DBConnection']->lastInsertId();
	}
	return $result;
}
function databaseFetch($theResult) {
	global $_MGM;
	return $theResult->fetch();
}
function databaseFetchNum($theResult) {
	global $_MGM;
	return $theResult->fetch(PDO::FETCH_NUM);
}
function databaseFetchAssoc($theResult) {
	global $_MGM;
	return $theResult->fetch(PDO::FETCH_ASSOC);
}
function databaseResultSeek($theResult, $theLocation) {
	global $_MGM;
	return false;
}
function databaseFreeResult($theResult) {
	global $_MGM;
	$theResult = NULL;
}
?>