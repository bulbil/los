<?php

function sqlImplode($array, $table) {

	$sql_columns = implode(', ', $array);
	$sql_values = implode(', :', $array);
	$query = "INSERT INTO `$table` ($sql_columns) VALUES (:$sql_values)";
	return $query;
}

function insertValue($str, $table, $column, $pdo) {

	$r = $pdo->quote(stringFormat($str));
	echo $column . ' ' . $r . '<br/>';
	$sql = "INSERT INTO $table (`$column`) VALUES ($r)";
	$pdo->query($sql);
}

function insertTag($str, $obj, $column, $pdo) {

	$r = $pdo->quote(stringFormat($str));
	// $r = $str;
	echo $r;
	if(strlen($str) > 2){

		if(!ifExists($str, 'Tags', 'tag', $pdo, $column, 'category')) {
			// $sql = "INSERT INTO Tags(`category`, `tag`) VALUES ('$column', $r)";
			// $pdo->query($sql);
			$obj->bindValue(':tag', $str, PDO::PARAM_STR);
			$obj->bindValue(':category', $column, PDO::PARAM_STR);
			$obj->execute();
		}
	}
}

function bindValue($str, $obj, $column) {

	$str = stringFormat($str);
	$obj->bindValue($column, $str);
	return $obj;
}

function ifExists($str, $table, $column, $pdo, $str2 = '', $column2 = '') {

	$str = stringFormat($str);
	$sql = (!$str2) ? "SELECT EXISTS(SELECT * FROM $table WHERE $column = '$str')" :
						"SELECT EXISTS(SELECT * FROM $table WHERE $column = '$str' AND $column2 = '$str2')";
	$exists = $pdo->query($sql);
	$exists = $exists->fetch(PDO::FETCH_NUM);
	return $exists[0];
}

function insertThemeID($str, $obj) {

	$obj->bindValue('theme', $str);
	$if_secondary = (contains_substr($str, '--')) ? true : false;
	$obj->bindValue('if_secondary', $if_secondary); 
	$obj->execute();
}

function returnID($str1, $column1, $column2, $table, $pdo, $str2 = '', $column3 = '') {
	
	$r = $str1;
	echo $r;
	if(strlen($r) >= 2){
		$sql = (!$str2) ? "SELECT $column1 FROM $table WHERE $column2 = '$r'" :
					"SELECT $column1 FROM $table WHERE $column2 = '$r' AND $column3 = '$str2'";
		$result = $pdo->query($sql);
		$r = $result->fetch(PDO::FETCH_NUM);	
		return $r[0];}
	else { return 0;}
}

function updateMain($str, $pdo) {

	if (ifExists($str, 'Themes', 'theme', $pdo)) {

		$id = returnID($str, 'theme_id', 'theme', 'Themes', $pdo);
		$id = stringFormat($str, $pdo);
		$sql = "UPDATE Articles_Themes SET if_main = true WHERE theme_id = '$id'";
		$pdo->exec($sql);

		}

	elseif (ifExists($str, 'Tags', 'tag', $pdo)) {

		$id = returnID($str, 'tag_id', 'tag', 'Tags', $pdo);
		$id = stringFormat($str, $pdo);
		$sql = "UPDATE Articles_Tags SET if_main = true WHERE tag_id = '$id'";
		$pdo->exec($sql);
		} 
}

function stringFormat($str, $param = 'default', $pdo = '') {
	
	$str = trim($str);

	switch ($param){

		case('bool'): 	

			return ($str == 'Yes') ? true : false;

		case('timestamp'): 	

			if($str){	

				$d = DateTime::createFromFormat('j/n/Y G:i:s', $str);
				return $d->format('Y-m-d h:i:s');

			} else { return 0; }

		case('date_published'):	

			if($str){

				$str = preg_replace('/,/', '', $str);
				$d = '15 ' . $str;
				$d = DateTime::createFromFormat('d F Y', $d);
				return $d->format('Y-m-d');

			} else { return 0; }

		case('type'): 	

			$str = strtolower($str);
			$str = preg_replace('/-/', '', $str);
			return $str;

		case('array'):

			$str = rtrim($str, ';');
			$str = explode(';', $str);
			return $str;

		case('default'): 

			return $str;

	}

}