<?php

$myPDO = db_connect();

$str = "what's the fuck what";

$str = $myPDO->quote($str);

echo $str;

$sql = "INSERT INTO Articles (`title`) VALUES ($str)";

$myPDO->query($sql);