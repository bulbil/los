<?php

function tagArray($array, $category, $article_id, $reviewer_id, $obj, $pdo){

	foreach (stringFormat($array, 'array') as $tag){

		if (!ifExists($tag, 'Tags', 'tag', $pdo, $category, 'category') && strlen($tag) > 2 && $tag != 'n/a'){	

			$tag_id = returnID(stringFormat($tag), 'tag_id', 'tag', 'Tags', $pdo, $category, 'category');
	
			if(!$tag_id) { 
	
				insertValue($tag, 'Tags', 'tag', $pdo, $category, 'category'); 
				$tag_id = $pdo->lastInsertId();
				echoLine($tag_id . ': ' . $tag);
	
			} else { echoLine('exists '. $tag_id . ': ' . $tag); }
	
			bindValue($tag_id, $obj, 'tag_id');
			bindValue($article_id, $obj, 'article_id');
			bindValue($reviewer_id, $obj, 'reviewer_id');
			$obj->execute();
		}
	}

}	

function echoline($str1, $str2 = '') {

	$line = ($str2) ? $str2 . ' ' . $str1 . '<br/>' : $str1 . '<br/>';
	echo $line;
}

function echoArray($array){

	foreach(stringFormat($array, 'array') as $key=>$value) echoLine($value, $key);

}

function sqlImplode($array, $table, $param = '', $column = '', $str = '') {

	$sql_columns = implode(', ', $array);
	$sql_values = implode(', :', $array);
	$query = ($param != 'update') ? "INSERT INTO `$table` ($sql_columns) VALUES (:$sql_values)" :
									"UPDATE `$table` SET $sql_columns WHERE $column = '$str')";
	return $query;
}

function insertValue($str, $table, $column, $pdo, $str2 = '', $column2 = '') {

	$r = $pdo->quote(stringFormat($str));
	$r2 = $pdo->quote(stringFormat($str2));
	$sql = (!$str2) ? "INSERT INTO $table (`$column`) VALUES ($r)" :
	"INSERT INTO $table (`$column`, `$column2`) VALUES ($r, $r2)";
	$pdo->query($sql);
}

function insertTag($str, $obj, $column, $pdo) {

	$r = $pdo->quote(stringFormat($str));
	if(strlen($str) > 2){

			$sql = "INSERT INTO Tags(`category`, `tag`) VALUES ('$column', $r)";
			$pdo->query($sql);
			// $obj->bindValue(':tag', $str, PDO::PARAM_STR);
			// $obj->bindValue(':category', $column, PDO::PARAM_STR);
			// $obj->execute();
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
	
	$r = (stringFormat($str1));
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
		$sql = "UPDATE Articles_Themes SET if_main = '1' WHERE theme_id = '$id'";
		$pdo->exec($sql);

		}

	elseif (ifExists($str, 'Tags', 'tag', $pdo)) {
		echoLine('tugss what');
		$id = returnID($str, 'tag_id', 'tag', 'Tags', $pdo);
		// $id = stringFormat($str);
		$sql = "UPDATE Articles_Tags SET if_main = 'true' WHERE tag_id = '$id'";
		$pdo->exec($sql);
		}

	else { echoLine('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong>' . $str . '</strong> not found ... check data');}
}

function stringFormat($str, $param = 'default') {
	
	$str = trim($str);

	switch ($param){

		case('bool'): 	

			return ($str == 'Yes') ? true : false;

		case('timestamp'): 	

			if($str){	

				$d = DateTime::createFromFormat('j/n/Y G:i:s', $str);
				return $d->format('Y-m-d H:i:s');

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