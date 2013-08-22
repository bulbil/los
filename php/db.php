<?php
//provides configuration for database
$dsn = "mysql:dbname=los;host=localhost";
$user = "lummis";
$password = "pQaD9oF";


try {$losPDO = new PDO($dsn, $user, $password);}
catch(PDOException $ex) {echo 'Connection failed: ' . htmlspecialchars($ex->getMessage()); }