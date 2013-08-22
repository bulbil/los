<?php

function sqlImplode($array, $table) {

	$sql_columns = implode(', ', $array);
	$sql_values = implode(', :', $array);
	$query = "INSERT INTO `$table` ($sql_columns) VALUES (:$sql_values)";
	return $query;
}

function stringCleanUp($str) {
	$str = trim($str);
	return $str;
}

function insertValue($str, $obj, $column) {

	$obj->bindValue($column, $str);
	return $obj;
}

function insertTagArray($str, $obj, $column) {

	$array = explode(';', $str);
	foreach ($array as $value) {

		$obj->bindValue('tag', $str);
		$obj->bindValue('category', $column);
		return $obj;
	}
}

function insertArray($str, $obj, $column) {

	$array = explode(';', $str);
	foreach ($array as $value) {

		$obj->bindValue($theme, $str);
		return $obj;
	}
}

function returnBoolean($str) {

	$str = stringCleanUp($str);
	return ($str == 'Yes') ? true : false;
}

function returnTimestamp($str) {

	if($str){	

		$d = DateTime::createFromFormat('j/n/Y G:i:s', $str);
		return $d->format('Y-m-d h:i:s');
	} else { return 0; }
}

function returnReviewerID($str,$pdo) {
	$s = $str;
	echo 1;
	$sql = "SELECT reviewer_id FROM Reviewers WHERE initials='$s'";
	echo 2;
	$result = $pdo->query($sql);
	echo 3;
	$r = $result->fetch(PDO::FETCH_ASSOC);	
	echo 4;
	return $r['reviewer_id'];
}

function returnDate($str) {

	if($str){

		$str = preg_replace('/,/', '', $str);
		$d = '15 ' . $str;
		$d = DateTime::createFromFormat('d F Y', $d);
		return $d->format('Y-m-d');

	} else { return 0; }
}

function returnType($str) {

	$str = strtolower($str);
	$str = preg_replace('/-/', '', $str);
	return $str;
}