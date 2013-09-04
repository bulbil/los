<?php
/////////////////////////////////////////////////////////
//
//
//									<コ:彡
//
//						LAND OF SUNSHINE 
//						university of michigan digital humanities project
// 						nabil kashyap (nabilk.com)
//
/////////////////////////////////////////////////////////

// utility functions to format, insert and update data in corresponding tables

/////////////////////////////////////////////////////////
//
// FUNCTIONS FOR GETTING STUFF INTO THE DB
//
/////////////////////////////////////////////////////////

// for getting stuff into the articles table
function editRowArticle($array, $obj) {

		bindValue($array['title'], $obj, 'title');

		bindValue($array['author'], $obj, 'author');

		bindValue($array['location'], $obj, 'location');

		bindValue($array['page_start'], $obj, 'page_start');

		bindValue($array['page_end'], $obj, 'page_end');

		bindValue($array['volume'], $obj, 'volume');

		bindValue($array['issue'], $obj, 'issue');

		$date = stringFormat($array['date_published'], 'date_published');
		bindValue($date, $obj, 'date_published');

		$type = stringFormat($array['type'],'type');
		bindValue($type, $obj, 'type');

		$obj->execute();
}

function editRowReview($article_id, $reviewer_id, $array, $obj) {

		bindValue($article_id, $obj, 'article_id');

		bindValue($reviewer_id, $obj, 'reviewer_id');

		$timestamp = stringFormat($array['timestamp'], 'timestamp');
		bindValue($timestamp, $obj, 'timestamp');

		bindValue($array['summary'], $obj, 'summary');

		bindValue($array['notes'], $obj, 'notes');

		bindValue($array['research_notes'], $obj, 'research_notes');

		bindValue($array['narration_pov'], $obj, 'narration_pov');

		$narration_embedded = stringFormat($array['narration_embedded'], 'bool');
		bindValue($narration_embedded, $obj, 'narration_embedded');

		bindValue($array['narration_tense'], $obj, 'narration_tense');

		$narration_tenseshift = stringFormat($array['narration_tenseshift'], 'bool');
		bindValue($array['narration_tenseshift'], $obj, 'narration_tenseshift');

		$obj->execute();
}

function editThemes($article_id, $reviewer_id, $str, $obj, $pdo) {

	foreach(stringFormat($str, 'array') as $theme) {
	
		$value = stringFormat($theme, 'theme');
		$theme_id = returnID($theme, 'theme_id', 'theme', 'Themes', $pdo);

		if($theme_id && !ifExists($theme_id, 'Articles_Themes', 'theme_id', $pdo, $article_id, 'article_id')){			

			bindValue($theme_id, $obj, 'theme_id');
			bindValue($article_id, $obj, 'article_id');
			bindValue($reviewer_id, $obj, 'reviewer_id');			
			$obj->execute();									
		} else { echoLine('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong>' . $value . '</strong> not a theme ... check data'); }
	}
}


/////////////////////////////////////////////////////////
//
// HELPER FUNCTIONS FOR GETTING STUFF INTO THE DB
//
/////////////////////////////////////////////////////////


// arrays for the different tables
$articles = array(
	'title',
	'author',
	'location',
	'page_start',
	'page_end',
	'volume',
	'issue',
	'date_published',
	'type'
	);

$reviews = array(
	'article_id',
	'reviewer_id',
	'timestamp',
	'summary',
	'notes',
	'research_notes',
	'narration_pov',
	'narration_embedded',
	'narration_tense',
	'narration_tenseshift'
	);

$articles_themes = array(
	'article_id',
	'theme_id',
	'reviewer_id'
	);

$articles_tags = array(
	'article_id',
	'tag_id',
	'reviewer_id'
	);

// creates the SQL queries for PDO prepared statements from an array
function sqlImplode($array, $table, $param = '', $column = '', $str = '') {

	$sql_columns = implode(', ', $array);
	$sql_values = implode(', :', $array);
	$query = ($param != 'update') ? "INSERT INTO `$table` ($sql_columns) VALUES (:$sql_values)" :
									"UPDATE `$table` SET $sql_columns WHERE $column = '$str')";
	return $query;
}


// returns an object with the prepared PDO statement
function pdoStatementPrepare($array, $table, $pdo) {

	$sql = sqlImplode($array, $table);
	$stmt = $pdo->prepare($sql);
	return $stmt;
}


// binds value to PDO prepared statement
function bindValue($str, $obj, $column) {

	$str = stringFormat($str);
	$obj->bindValue($column, $str);
	return $obj;
}


//inserts a value into a table -- just like that, no executing a statement, just get 'er done
function insertValue($str, $table, $column, $pdo, $str2 = '', $column2 = '') {

	$sql = (!$str2) ? "INSERT INTO $table (`$column`) VALUES ($str)" :
	"INSERT INTO $table (`$column`, `$column2`) VALUES ($str, '$str2')";
	$stmt = $pdo->quote($sql);
	$stmt = $pdo->prepare($sql);
	$stmt->bindValue($column, $str, PDO::PARAM_STR);
	if($str2){$stmt->bindValue($column2, $str2, PDO::PARAM_STR);}
	$stmt->execute();
}


// returns bool if exists in a table -- faster than returnID as far as I know
function ifExists($str, $table, $column, $pdo, $str2 = '', $column2 = '') {

	$sql = (!$str2) ? "SELECT EXISTS(SELECT * FROM $table WHERE $column = ?)" :
						"SELECT EXISTS(SELECT * FROM $table WHERE $column = ? AND $column2 = ?)";
	$param = (!$str2) ? array($str) : array($str, $str2);
	$stmt = $pdo->prepare($sql);
	$stmt->execute($param);
	$exists = $stmt->fetch(PDO::FETCH_NUM);
	return $exists[0];
}

function returnReviewerID($str, $article_id, $pdo) {

	// grabs the reviewer_id or, if two sets of initials appear as in a reconciled article, sets the initials to 'rec'
	$id = (strlen($str) < 4) ? returnID($str, 'reviewer_id', 'initials', 'Reviewers', $pdo)
		: 9;
	// if reconciled, updates the corresponding article in the Articles table to 'reconciled'
	if($id == 'rec') {updateReconciled($article_id, $pdo);}
	return $id;
}


// give it a string and it should return an id -- can take an optional parameter to further specify select query
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


// takes a string of tags delimited by semicolons, inserts the tag into Tags table if new and associates tags with articles and reviewers
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

			// just in case the tag appears twice in the same category with reference to the same article
			if (!ifExists($tag_id, 'Articles_Tags', 'tag_id', $pdo, $article_id, 'article_id')){
				
				bindValue($tag_id, $obj, 'tag_id');
				bindValue($article_id, $obj, 'article_id');
				bindValue($reviewer_id, $obj, 'reviewer_id');
				$obj->execute();
			}		
		} 
	}
}	


// just for inserting themes from the google spreadsheet themes list 
function insertThemeID($str, $obj) {

	$obj->bindValue('theme', $str);
	$if_secondary = (contains_substr($str, '--')) ? true : false;
	$obj->bindValue('if_secondary', $if_secondary); 
	$obj->execute();
}


// updates boolean column for Articles table
function updateReconciled($str, $pdo) {

		$sql = "UPDATE Articles SET reconciled = 1 WHERE article_id = ?";
		$stmt = $pdo->prepare($sql);
		$stmt->execute(array($str));
}


// updates boolean column for Themes and Tags tables
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


/////////////////////////////////////////////////////////
//
// FUNCTIONS FOR FORMATTING STUFF
//
/////////////////////////////////////////////////////////

// generalist formatting utility for the different google spreadsheet cells
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

function echoline($str1, $str2 = '') {

	$line = ($str2) ? $str2 . ' ' . $str1 . '<br/>' : $str1 . '<br/>';
	echo $line;
}

function echoArray($array){

	foreach(stringFormat($array, 'array') as $key=>$value) echoLine($value, $key);
}