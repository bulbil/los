<?php
//db config
function db_connect (){

	$dsn = "mysql:dbname=los;host=localhost";
	$user = "lummis";
	$pw = "pQaD9oF";

	$dbh = new PDO($dsn, $user, $pw);
	$dbh->setAttribute (PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	// $dbh->setAttribute (PDO::ATTR_ERRMODE, PDO::MYSQL_ATTR_USE_BUFFERED_QUERY);
	return ($dbh);
}