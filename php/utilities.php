<?php

function tagArray($array, $category, $article_id, $reviewer_id, $obj, $pdo){

	foreach (stringFormat($array, 'array') as $tag){
		$tag = stringFormat($tag);

		if (strlen($tag) > 2 &&	$tag != 'n/a'){	

			$tag_id = returnID($tag, 'tag_id', 'tag', 'Tags', $pdo, $category, 'category');

			if(!$tag_id) {

				$tag = $pdo->quote($tag);
				insertValue($tag, 'Tags', 'tag', $pdo, $category, 'category'); 
				$tag_id = $pdo->lastInsertId();
			} 
	
			if (!ifExists($tag_id, 'Articles_Tags', 'tag_id', $pdo, $article_id, 'article_id')){
				
				bindValue($tag_id, $obj, 'tag_id');
				bindValue($article_id, $obj, 'article_id');
				bindValue($reviewer_id, $obj, 'reviewer_id');
				$obj->execute();
			}		
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

	$sql = (!$str2) ? "INSERT INTO $table (`$column`) VALUES ($str)" :
	"INSERT INTO $table (`$column`, `$column2`) VALUES ($str, '$str2')";
	$stmt = $pdo->quote($sql);
	$stmt = $pdo->prepare($sql);
	$stmt->bindValue($column, $str, PDO::PARAM_STR);
	if($str2){$stmt->bindValue($column2, $str2, PDO::PARAM_STR);}
	$stmt->execute();
}

function insertTag($str, $obj, $column, $pdo) {

	if(strlen($str) > 2){
			$str = addslashes($str);
			$sql = "INSERT INTO Tags(`category`, `tag`) VALUES ('$column', $str)";
			// $pdo->query($sql);
			$stmt = $pdo->prepare($sql);
			$stmt->bindValue(':tag', $str, PDO::PARAM_STR);
			$stmt->bindValue(':category', $column, PDO::PARAM_STR);
			$stmt->execute();
	}
}

function bindValue($str, $obj, $column) {

	$str = stringFormat($str);
	$obj->bindValue($column, $str);
	return $obj;
}

function ifExists($str, $table, $column, $pdo, $str2 = '', $column2 = '') {

	$sql = (!$str2) ? "SELECT EXISTS(SELECT * FROM $table WHERE $column = ?)" :
						"SELECT EXISTS(SELECT * FROM $table WHERE $column = ? AND $column2 = ?)";
	$param = (!$str2) ? array($str) : array($str, $str2);
	$stmt = $pdo->prepare($sql);
	$stmt->execute($param);
	$exists = $stmt->fetch(PDO::FETCH_NUM);
	return $exists[0];
}

function insertThemeID($str, $obj) {

	$obj->bindValue('theme', $str);
	$if_secondary = (contains_substr($str, '--')) ? true : false;
	$obj->bindValue('if_secondary', $if_secondary); 
	$obj->execute();
}

function returnID($str1, $column1, $column2, $table, $pdo, $str2 = '', $column3 = '') {

	$str1 = stringFormat($str1);
	$str2 = stringFormat($str2);

	if(strlen($str1) >= 2){

		$sql = (!$str2) ? "SELECT $column1 FROM $table WHERE $column2 = ?" :
					"SELECT $column1 FROM $table WHERE $column2 = ? AND $column3 = ?";
		$param = (!$str2) ? array($str1) : array($str1, $str2);

		$stmt = $pdo->prepare($sql);
		if(!$str2) { $stmt->execute(array($str1)); } else { $stmt->execute(array($str1,$str2)); }
		
		$result = $stmt->fetch(PDO::FETCH_NUM);
		return ($result[0]);}
	else { return '';}
}

function updateReconciled($str, $pdo) {

		$sql = "UPDATE Articles SET reconciled = 1 WHERE article_id = ?";
		$stmt = $pdo->prepare($sql);
		$stmt->execute(array($str));
}

function updateMain($str, $pdo) {
	
	$str = stringFormat($str);
	if (ifExists($str, 'Themes', 'theme', $pdo)) {
		$id = returnID($str, 'theme_id', 'theme', 'Themes', $pdo);
		$sql = "UPDATE Articles_Themes SET if_main = 1 WHERE theme_id = ?";
		$stmt = $pdo->prepare($sql);
		$stmt->execute(array($id));
		}

	elseif (ifExists($str, 'Tags', 'tag', $pdo)) {
		$id = returnID($str, 'tag_id', 'tag', 'Tags', $pdo);
		$sql = "UPDATE Articles_Tags SET if_main = 1 WHERE tag_id = ?";
		$stmt = $pdo->prepare($sql);
		$stmt->execute(array($id));
		}

	else { echoLine('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong>' . $str . '</strong> main not found ... check data');}
}

function stringFormat($str, $param = 'default') {
	
	$str = trim($str);

	switch ($param){

		case('default'): 

			return $str;

		case('array'):

			$str = rtrim($str, ';');
			$str = explode(';', $str);
			return $str;

		case('bool'): 	

			return ($str == 'Yes') ? true : false;

		case('date_published'):	

				$str = preg_replace('/,/', '', $str);
				$d = '15 ' . $str;
				$dArray = explode(' ', $d);
				if (count($dArray) == 3 && strtotime($dArray[1])) {
					$d = DateTime::createFromFormat('d F Y', $d);
					return $d->format('Y-m-d');

				} else { return 0000-00-00; }

		case('timestamp'): 	

			if($str){	

				$d = DateTime::createFromFormat('j/n/Y G:i:s', $str);
				return $d->format('Y-m-d H:i:s');

			} else { return 0; }

		case('theme'):
			$str = trim($str);
			// $str = preg_replace('/-/', '--', $str, 1);
			$str = preg_replace('/\./', '', $str);
			return $str;

		case('type'): 	

			$str = strtolower($str);
			$str = preg_replace('/-/', '', $str);
			return $str;
	}

}