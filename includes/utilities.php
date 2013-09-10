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
function edit_row_article($array, $obj) {

		bind_value($array['title'], $obj, 'title');

		bind_value($array['author'], $obj, 'author');

		bind_value($array['location'], $obj, 'location');

		bind_value($array['page_start'], $obj, 'page_start');

		bind_value($array['page_end'], $obj, 'page_end');

		bind_value($array['volume'], $obj, 'volume');

		bind_value($array['issue'], $obj, 'issue');

		$date = string_format($array['date_published'], 'date_published');
		bind_value($date, $obj, 'date_published');

		$type = string_format($array['type'],'type');
		bind_value($type, $obj, 'type');

		$obj->execute();
}

function edit_row_review($article_id, $reviewer_id, $array, $obj) {

		bind_value($article_id, $obj, 'article_id');

		bind_value($reviewer_id, $obj, 'reviewer_id');

		$timestamp = string_format($array['timestamp'], 'timestamp');
		bind_value($timestamp, $obj, 'timestamp');

		bind_value($array['summary'], $obj, 'summary');

		bind_value($array['notes'], $obj, 'notes');

		bind_value($array['research_notes'], $obj, 'research_notes');

		bind_value($array['narration_pov'], $obj, 'narration_pov');

		$narration_embedded = string_format($array['narration_embedded'], 'bool');
		bind_value($narration_embedded, $obj, 'narration_embedded');

		bind_value($array['narration_tense'], $obj, 'narration_tense');

		$narration_tenseshift = string_format($array['narration_tenseshift'], 'bool');
		bind_value($array['narration_tenseshift'], $obj, 'narration_tenseshift');

		$obj->execute();
}

function edit_themes($article_id, $reviewer_id, $str, $obj, $pdo) {

	foreach(string_format($str, 'array') as $theme) {
	
		$value = string_format($theme, 'theme');
		$theme_id = return_id($theme, 'theme_id', 'theme', 'Themes', $pdo);

		if($theme_id && !if_exists($theme_id, 'Articles_Themes', 'theme_id', $pdo, $article_id, 'article_id')){			

			bind_value($theme_id, $obj, 'theme_id');
			bind_value($article_id, $obj, 'article_id');
			bind_value($reviewer_id, $obj, 'reviewer_id');			
			$obj->execute();									
		} else { echo_line('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong>' . $value . '</strong> not a theme ... check data'); }
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

$dump = array(
	'article_id',
	'title',
	'author',
	'location',
	'page_start',
	'page_end',
	'volume',
	'issue',
	'date_published',
	'type',
	'category',
	'tag',
	'themes',
	'main',
	'summary',
	'notes',
	'research_notes',
	'narration',
	'narration_pov',
	'narration_embedded',
	'narration_tense',
	'narration_tenseshift'
	);

// creates the SQL queries for PDO prepared statements from an array
function sql_implode($array, $table, $param = '', $column = '', $str = '') {

	$sql_columns = implode(', ', $array);
	$sql_values = implode(', :', $array);
	$query = ($param != 'update') ? "INSERT INTO `$table` ($sql_columns) VALUES (:$sql_values)" :
									"UPDATE `$table` SET $sql_columns WHERE $column = '$str')";
	return $query;
}


// returns an object with the prepared PDO statement
function prepare_pdo_statement($array, $table, $pdo) {

	$sql = sql_implode($array, $table);
	$stmt = $pdo->prepare($sql);
	return $stmt;
}


// binds value to PDO prepared statement
function bind_value($str, $obj, $column) {

	$str = string_format($str);
	$obj->bind_value($column, $str);
	return $obj;
}


//inserts a value into a table -- just like that, no executing a statement, just get 'er done
function insert_value($str, $table, $column, $pdo, $str2 = '', $column2 = '') {

	$sql = (!$str2) ? "INSERT INTO $table (`$column`) VALUES ($str)" :
	"INSERT INTO $table (`$column`, `$column2`) VALUES ($str, '$str2')";
	$stmt = $pdo->quote($sql);
	$stmt = $pdo->prepare($sql);
	$stmt->bind_value($column, $str, PDO::PARAM_STR);
	if($str2){$stmt->bind_value($column2, $str2, PDO::PARAM_STR);}
	$stmt->execute();
}


// returns bool if exists in a table -- faster than return_id as far as I know
function if_exists($str, $table, $column, $pdo, $str2 = '', $column2 = '') {

	$sql = (!$str2) ? "SELECT EXISTS(SELECT * FROM $table WHERE $column = ?)" :
						"SELECT EXISTS(SELECT * FROM $table WHERE $column = ? AND $column2 = ?)";
	$param = (!$str2) ? array($str) : array($str, $str2);
	$stmt = $pdo->prepare($sql);
	$stmt->execute($param);
	$exists = $stmt->fetch(PDO::FETCH_NUM);
	return $exists[0];
}

function return_reviewer_id($str, $article_id, $pdo) {

	// grabs the reviewer_id or, if two sets of initials appear as in a reconciled article, sets the initials to 'rec'
	$id = (strlen($str) < 4) ? return_id($str, 'reviewer_id', 'initials', 'Reviewers', $pdo)
		: 9;
	// if reconciled, updates the corresponding article in the Articles table to 'reconciled'
	if($id == 'rec') {update_reconciled($article_id, $pdo);}
	return $id;
}


// give it a string and it should return an id -- can take an optional parameter to further specify select query
function return_id($str1, $column1, $column2, $table, $pdo, $str2 = '', $column3 = '') {

	$str1 = string_format($str1);
	$str2 = string_format($str2);

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
function tag_array($array, $category, $article_id, $reviewer_id, $obj, $pdo){

	foreach (string_format($array, 'array') as $tag){
		$tag = string_format($tag);

		if (strlen($tag) > 2 &&	$tag != 'n/a'){	

			$tag_id = return_id($tag, 'tag_id', 'tag', 'Tags', $pdo, $category, 'category');

			if(!$tag_id) {

				$tag = $pdo->quote($tag);
				insert_value($tag, 'Tags', 'tag', $pdo, $category, 'category'); 
				$tag_id = $pdo->lastInsertId();
			} 

			// just in case the tag appears twice in the same category with reference to the same article
			if (!if_exists($tag_id, 'Articles_Tags', 'tag_id', $pdo, $article_id, 'article_id')){
				
				bind_value($tag_id, $obj, 'tag_id');
				bind_value($article_id, $obj, 'article_id');
				bind_value($reviewer_id, $obj, 'reviewer_id');
				$obj->execute();
			}		
		} 
	}
}	


// just for inserting themes from the google spreadsheet themes list 
function insert_theme_id($str, $obj) {

	$obj->bind_value('theme', $str);
	$if_secondary = (contains_substr($str, '--')) ? true : false;
	$obj->bind_value('if_secondary', $if_secondary); 
	$obj->execute();
}


// updates boolean column for Articles table
function update_reconciled($str, $pdo) {

		$sql = "UPDATE Articles SET reconciled = 1 WHERE article_id = ?";
		$stmt = $pdo->prepare($sql);
		$stmt->execute(array($str));
}


// updates boolean column for Themes and Tags tables
function update_main($str, $pdo) {
	
	$str = string_format($str);
	if (if_exists($str, 'Themes', 'theme', $pdo)) {
		$id = return_id($str, 'theme_id', 'theme', 'Themes', $pdo);
		$sql = "UPDATE Articles_Themes SET if_main = 1 WHERE theme_id = ?";
		$stmt = $pdo->prepare($sql);
		$stmt->execute(array($id));
		}

	elseif (if_exists($str, 'Tags', 'tag', $pdo)) {
		$id = return_id($str, 'tag_id', 'tag', 'Tags', $pdo);
		$sql = "UPDATE Articles_Tags SET if_main = 1 WHERE tag_id = ?";
		$stmt = $pdo->prepare($sql);
		$stmt->execute(array($id));
		}

	else { echo_line('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong>' . $str . '</strong> main not found ... check data');}
}


/////////////////////////////////////////////////////////
//
// FUNCTIONS FOR FORMATTING & OUTPUT
//
/////////////////////////////////////////////////////////

// generalist formatting utility for the different google spreadsheet cells
function string_format($str, $param = 'default') {
	
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

		case('date_form'):

			$d = explode('-',$str);
			return JDMonthName($d[1], 1) . ' ' . $d[0];

		case('timestamp'): 	

			if($str){	

				$d = DateTime::createFromFormat('j/n/Y G:i:s', $str);
				return $d->format('YY-MM-DD H:i:s');

			} else { return 0; }

		case('theme'):
			$str = trim($str);
			// $str = preg_replace('/-/', '--', $str, 1);
			$str = preg_replace('/\./', '', $str);
			$str = preg_replace('/\w-\w/', '--', $str);
			return $str;

		case('type'): 	

			$str = strtolower($str);
			$str = preg_replace('/-/', '', $str);
			return $str;
	}

}

function echo_line($str1, $str2 = '') {

	$line = ($str2) ? $str2 . ' ' . $str1 . '<br/>' : $str1 . '<br/>';
	echo $line;
}

function echo_array($array){

	foreach(string_format($array, 'array') as $key=>$value) echo_line($value, $key);
}

// for outputting tables, php to html

function table_start($array) {

	$html = "<div class='col-md-10 col-md-offset-1'>";
	$html .= "<table class='table table-striped'><tr>";
	foreach($array as $column) { $html .= '<th>' . $column . '</th>'; }
	$html .= '</tr>';
	echo $html;
}

function table_row($array, $id_column = '') {

	$html = '<tr>';
	foreach($array as $value) { $html .= '<td>' . $value . '</td>'; }
	if($id_column) $html .= "<td><a href='edit-review.php?id=" . $id_column . "'>edit</a></td>"; 
	$html .= '</tr>';
	echo $html;
}

function table_end(){

	$html = '</div></table>';
	echo $html;
}

// for outputting json things

function return_json($param, $article_id = '', $reviewer_id = '') {

	function query($sql) {
		$dbh = db_connect();
		$results = $dbh->query($sql);
		while($row = $results->fetch(PDO::FETCH_ASSOC)) $results_array[] = $row;
		$json = json_encode($results_array);
		$dbh = null;
		return $json;
	}

	switch ($param){
		
		// all the the articles reviewed for a reviewer_id
		case('reviewed'):

			$sql = "SELECT timestamp, Articles.article_id, title, issue, volume, date_published, reconciled 
					FROM Articles JOIN Reviews ON Articles.article_id = Reviews.article_id 
					WHERE reviewer_id = $reviewer_id 
					ORDER BY UNIX_TIMESTAMP(timestamp) DESC";
			return query($sql);

		// the last article reviewed for a reviewer_id
		case('last'): 

			$sql = "SELECT timestamp, Articles.article_id, title, issue, volume, date_published, reconciled 
					FROM Articles JOIN Reviews ON Articles.article_id = Reviews.article_id 
					WHERE reviewer_id = $reviewer_id 
					ORDER BY UNIX_TIMESTAMP(timestamp) DESC LIMIT 1";
			return query($sql);

		case('article'):

			$sql = "SELECT * FROM Articles 
					WHERE article_id = $article_id";
			return query($sql);

		case('review'):

			$sql = "SELECT * FROM Reviews 
					WHERE (article_id, reviewer_id) = ($article_id, $reviewer_id)";
			return query($sql);

		case('themes'):

			$sql = "SELECT theme, if_main FROM Themes 
					JOIN Articles_Themes ON Articles_Themes.theme_id = Themes.theme_id 
					WHERE (Articles_Themes.article_id, Articles_Themes.reviewer_id) = ($article_id, $reviewer_id)";
			return query($sql);

		case('tags'):

			$sql = "SELECT category, tag, if_main FROM Tags 
					JOIN Articles_Tags ON Tags.tag_id = Articles_Tags.tag_id 
					WHERE (Articles_Tags.article_id, Articles_Tags.reviewer_id) = ($article_id, $reviewer_id)";
			return query($sql);

		case('themes_list'):

			$sql = "SELECT theme FROM Themes";
			return query($sql);	

		case('dump_articles'):

			$sql_columns = implode(',', $articles);
			$sql = "SELECT $sql_columns FROM Articles";
			return query($sql);	

		case('dump_reviews'):

			$sql_columns = implode(',', $reviews);
			$sql = "SELECT $sql_columns FROM Reviews";
			return query($sql);	

		case('dump_themes'):

			$sql = "SELECT article_id, reviewer_id, theme FROM Themes 
					JOIN Articles_Themes ON Themes.theme_id = Articles_Themes.theme_id";
			return query($sql);	

		case('dump_tags'):
			$sql = "SELECT category, tag FROM Tags";
			$sql_alt = "SELECT category, article_id, reviewer_id, category, tag 
					FROM Tags JOIN Articles_Tags 
					ON Tags.tag_id = Articles_Tags.tag_id";
			return query($sql);

		// case('dump'):

		// 	$sql_columns = implode(',', $dump);
		// 	$sql = "SELECT $sql_columns FROM Articles 
		// 				JOIN Reviews ON Articles.article_id = Reviews.article_id
		// 				Articles_Themes JOIN Themes ON Articles_Themes.ar"

		}
}

// for calling funtions from main.js

function js_functions() {

		$view = preg_split("/\//", $_SERVER['PHP_SELF']);
		$js = '<script>';
		$js .= 'losFormViews.themesList();';
		$js .= 'losFormViews.tagsLists();';
		$js .= 'losFormViews.mainLists();';

		if(isset($view[3])){
			switch($view[3]) {

				case('reviewer.php'): break;

				case('add-review.php'): 

					$js .= 'losFormViews.lastReview();';
					$js .= '</script>'; 
					return $js;

				case('edit-review.php'):
					$id = $_GET['id'];
					$js .= "losFormViews.editReview($id);";
					$js .= '</script>';
					return $js;

				case('reconcile.php'):
				case('data-table.php'):
				case('visualization.php'):
			}
		}
}