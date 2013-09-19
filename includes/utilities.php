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
function execute_article($array, $obj, $rec = 0) {

	global $articles;

	$array['type'] = string_format($array['type'],'type');
	$array['reconciled'] = $rec;

	foreach($articles as $column) {
		bind_value($array[$column], $obj, $column);
	}
	$obj->execute();
}

function execute_review($article_id, $reviewer_id, $array, $obj) {

	global $reviews;

	$array['article_id'] = $article_id;
	$array['reviewer_id'] = $reviewer_id;
	$array['timestamp'] = ($array['timestamp']) ? $array['timestamp'] : date('Y-m-d H:i:s');		

	foreach($reviews as $column) {

		bind_value($array[$column], $obj, $column);
	}
	$obj->execute();
}

function execute_article_themes($article_id, $reviewer_id, $str, $obj, $pdo) {

	global $articles_themes;

	$sql = "DELETE FROM Articles_Themes WHERE (`article_id`, `reviewer_id`) = ('$article_id', '$reviewer_id')";
	$pdo->query($sql);

	foreach(string_format($str, 'array') as $theme) {

		if($theme){

			$theme = string_format($theme,'theme');
			$theme_id = return_id('theme_id', array($theme), array('theme'), 'Themes', $pdo);
				
				if($theme_id) {
					
					if (!if_exists(array($article_id, $theme_id, $reviewer_id), $articles_themes, 'Articles_Themes', $pdo)){			
		
						bind_value($theme_id, $obj, 'theme_id');
						bind_value($article_id, $obj, 'article_id');
						bind_value($reviewer_id, $obj, 'reviewer_id');			
						$obj->execute();
		
					} else echo_line('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong>' . $theme . '</strong> already attached to this review');
		
			} else echo_line('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong>' . $theme . '</strong> not a theme ... check data');
		}
	}
}


// takes a string of tags delimited by semicolons, inserts the tag into Tags table if new and associates tags with articles and reviewers
function execute_article_tags($array, $category, $article_id, $reviewer_id, $obj, $pdo){

	global $articles_tags;

	foreach (string_format($array, 'array') as $tag){

		$tag = string_format($tag);
		if (strlen($tag) > 1 &&	$tag != 'n/a'){	

			$tag_id = return_id('tag_id', array($tag, $category), array('tag', 'category'), 'Tags', $pdo);
			if($tag_id == 0) {

				$tag = $pdo->quote($tag);
				insert_value($tag, 'Tags', 'tag', $pdo, $category, 'category');
				$tag_id = $pdo->lastInsertId();
			}

			if (!if_exists(array($article_id, $tag_id, $reviewer_id), $articles_tags, 'Articles_Tags', $pdo)){
				bind_value($tag_id, $obj, 'tag_id');
				bind_value($article_id, $obj, 'article_id');
				bind_value($reviewer_id, $obj, 'reviewer_id');
				$obj->execute();
				}
		} 
	}
}	

// function edit_article_tags($array, $category, $article_id, $reviewer_id, $obj, $pdo){

// 	foreach (string_format($array, 'array') as $tag){
// 		$tag = string_format($tag);

// 		if (strlen($tag) > 1 &&	$tag != 'n/a'){	

// 			$tag_id = return_id('tag_id', array($tag, $category), array('tag_id', 'category'), 'Tags', $pdo);

// 			if(!$tag_id) {

// 				$tag = $pdo->quote($tag);
// 				insert_value($tag, 'Tags', 'tag', $pdo, $category, 'category'); 
// 				$tag_id = $pdo->lastInsertId();
// 			} 

// 			// just in case the tag appears twice in the same category with reference to the same article
// 			if (!if_exists(array($tag_id, $article_id), array('tag_id', 'article_id'), 'Articles_Tags', $pdo)){
				
// 				bind_value($tag_id, $obj, 'tag_id');
// 				bind_value($article_id, $obj, 'article_id');
// 				bind_value($reviewer_id, $obj, 'reviewer_id');
// 				$obj->execute();
// 			}		
// 		} 
// 	}
// }	


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
	'type',
	'reconciled'
	);

$article_check = array(
	'page_start', 
	'page_end', 
	'volume', 
	'issue'
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

$categories = array(
	'groups',
	'persons',
	'entities',
	'places',
	'activities',
	'florafauna',
	'commodities',
	'events',
	'works',
	'technologies',
	'environments'
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
function pdo_update($n) { return $n . " = :" . $n; }

function sql_implode($array, $table, $param = '', $columnArray = '', $filterArray = '') {

	if($param == 'update') {	
		$update_array = array_map("pdo_update", $array);
		$update_array = implode(',', $update_array);
		$columns = implode(',', $columnArray);
		$filters = implode(',', $filterArray);

		$query = "UPDATE `$table` SET $update_array WHERE ($columns) = ($filters)";
		return $query;

	} else {

		$sql_columns = implode(', ', $array);
		$sql_values = implode(', :', $array);

		$query = "INSERT INTO `$table` ($sql_columns) VALUES (:$sql_values)";
		return $query;
	}
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
	$obj->bindValue($column, $str);
	return $obj;
}


//inserts a value into a table -- just like that, no executing a statement, just get 'er done
function insert_value($str, $table, $column, $pdo, $str2 = '', $column2 = '') {

	$sql = (!$str2) ? "INSERT INTO $table (`$column`) VALUES ($str)" :
	"INSERT INTO $table (`$column`, `$column2`) VALUES ($str, '$str2')";
	$stmt = $pdo->quote($sql);
	$stmt = $pdo->prepare($sql);
	$stmt->bindValue($column, $str, PDO::PARAM_STR);
	if($str2){$stmt->bindValue($column2, $str2, PDO::PARAM_STR);}
	$stmt->execute();
}


// returns bool if exists in a table -- faster than return_id as far as I know
function if_exists($filterArray, $columnArray, $table, $pdo) {

	$columns = implode(' = ? AND ', $columnArray);
	$sql = "SELECT EXISTS(SELECT * FROM $table WHERE $columns = ?)";
	$stmt = $pdo->prepare($sql);
	$stmt->execute($filterArray);
	$exists = $stmt->fetch(PDO::FETCH_NUM);
	return $exists[0];
}

function if_article_exists($row, $dbh){

		$current_article = array($row['page_start'], $row['page_end'], $row['volume'], $row['issue']);

		return if_exists($current_article, $article_check, 'Articles', $dbh); 
}


// give it a string and it should return an id -- can take an optional parameter to further specify select query
function return_id($column, $filterArray, $columnArray, $table, $pdo) {

	$filters = array_map('string_format', $filterArray);
	$columns = implode(' = ? AND ', $columnArray);

	$sql = "SELECT $column FROM $table WHERE $columns = ?";
	$stmt = $pdo->prepare($sql);
	$stmt->execute($filters);
	
	$result = $stmt->fetch(PDO::FETCH_NUM);
	// $stmt->pdoParam
	$result = ($result[0]) ? $result[0] : 0;
	return $result;
}


function return_reviewer_id($str, $pdo) {
	$str = string_format($str);
	// grabs the reviewer_id or, if two sets of initials appear as in a reconciled article, sets the initials to 'rec'
	$id = (strlen($str) < 4) ? return_id('reviewer_id', array($str), array('initials'), 'Reviewers', $pdo)
		: '9';
	// if reconciled, updates the corresponding article in the Articles table to 'reconciled'
	// if($id == 'rec') {update_reconciled($article_id, $pdo);}
	return $id;
}


function return_article_id($row, $pdo) {

	global $article_check;
	foreach ($article_check as $check) $filterArray[] = $row[$check];

	if(if_exists($filterArray, $article_check, 'Articles', $pdo)) {
		$article_id = return_id('article_id', $filterArray, $article_check, 'Articles', $pdo);
		return $article_id;
	} else { return false;}
}

function return_row($returnArray, $filterArray, $columnArray, $table, $pdo) {

	$returns = implode(',', $returnArray);
	$columns = implode(' = ? AND ', $columnArray);
	$sql = "SELECT $returns FROM $table WHERE $columns = ?";
	$stmt = $pdo->prepare($sql);
	$stmt->execute($filterArray);
	$results = $stmt->fetch(PDO::FETCH_ASSOC);
	return $results;
}

// just for inserting themes from the google spreadsheet themes list 
function insert_theme_id($str, $obj) {

	$obj->bindValue('theme', $str);
	$if_secondary = (contains_substr($str, '--')) ? true : false;
	$obj->bindValue('if_secondary', $if_secondary); 
	$obj->execute();
}


// updates boolean column for Articles table
// function update_reconciled($str, $pdo) {

// 	$sql = "UPDATE Articles SET reconciled = 1 WHERE article_id = ?";
// 	$stmt = $pdo->prepare($sql);
// 	$stmt->execute(array($str));
// }

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

// updates boolean column for Themes and Tags tables
function update_main($str, $article_id, $reviewer_id, $pdo) {

	global $articles_themes;
	global $articles_tags;

	echo_line($str);
	$id = return_id('theme_id', array($str), array('theme'), 'Themes', $pdo);
	if($id) { 
		echo_line('theme id ' . $id);
		$themes_array = implode(' = ? AND ', $articles_themes);
		$sql = "UPDATE Articles_Themes SET `if_main` = '1' 
				WHERE $themes_array = ?";

		$stmt = $pdo->prepare($sql);

	}else{

		$id = return_id('tag_id', array($str), array('tag'), 'Tags', $pdo);
		echo_line('tag id ' . $id);
		if($id) {
			$tags_array = implode(' = ? AND ', $articles_tags);
			$sql = "UPDATE Articles_Tags SET `if_main` = '1' 
					WHERE $tags_array = ?";
			$stmt = $pdo->prepare($sql);
		} else { echo_line('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong>' . $str . '</strong> main not found ... check data'); return;}
	}
	echo_line($id);
	$stmt->execute(array($article_id, $id, $reviewer_id)); 
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
			$array = preg_split('/(;|,)/', $str);
			return $array;

		case('bool'): 	

			return ($str == 'Yes') ? true : false;

		case('date_check'):

			$date = preg_split('/-/', $str);
			$date = $date[1] . '-' . $date[0];
			return $date;

		case('date_csv'):	

				$str = preg_replace('/,/', '', $str);
				$d = '15 ' . $str;
				$dArray = explode(' ', $d);
				if (count($dArray) == 3 && strtotime($dArray[1])) {
					$d = DateTime::createFromFormat('d F Y', $d);
					return $d->format('Y-m-d');

				} else { return 0000-00-00; }

		case('date_submit'):

				$date = explode('-', $str);
				$date = $date[1] . '-' . $date[0] . '-15';
				return $date;

		case('date_form'):

			$d = explode('-',$str);
			return JDMonthName($d[1], 1) . ' ' . $d[0];

		case('timestamp'): 	

				$d = DateTime::createFromFormat('n/j/Y G:i:s', $str);
				return $d->format('Y-m-d H:i:s');

		case('theme'):
			$str = trim($str);
			$str = preg_replace('/\./', '', $str);
			$str = preg_replace('/\s?--\s?/', '--', $str);
			$str = preg_replace('/\s?-\s?/', '-', $str);
			$str = preg_replace('/(^people)\w?/i', 'Peoples', $str);
			$str= preg_replace('/(?<!anti|non)(?<=\w)-(?=\w)(?!age)/i','--', $str, 1);
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

// for getting the title for the forms page

function getFormTitle(){

	$form_title = $_GET['form'];
	switch($form_title) {
		case ('add'): return 'add review';
		case ('edit'): return 'edit review';
		case ('reconcile'): return 'reconcile reviews';
		case ('recedit'): return 'edit reconciled review';
	}
}

// for outputting tables, php to html

function table_start($array, $id, $padding ='') {

	$html = "<div class='row'>";
	$html .= "<div class='col-md-10 col-md-offset-1'>";
	$html .= "<table class='table table-striped'";
	$html .= "id='$id'><tr>";
	foreach($array as $column) { $html .= '<th>' . $column . '</th>'; }
	for($i = 0; $i < $padding; $i++) $html .= "<th>&nbsp;</th>";
	$html .= '</tr>';
	echo $html;
}

function table_row($array, $table_columns, $id_column = '', $p = 'reviewer') {

	$html = '<tr>';
	foreach($table_columns as $column) { $html .= '<td>' . $array[$column] . '</td>'; }
	
	if($p == 'reviewer'){
		if($array['reconciled'] == 0) $html .= "<td><a href='review-form.php?form=edit&id=" . $id_column . "'>edit </a>";
		$html .= table_reconcile_cell($id_column, $array['reconciled']);
		$html .= '</tr>';
	}
	
	echo $html;
}

function table_reconcile_cell($id, $bool) {

	$reviewer_id = $_SESSION['reviewer_id'];
	$dbh = db_connect();

	if($bool) {
		$sql = "SELECT Reviews.reviewer_id FROM Reviews JOIN Articles 
				ON Reviews.article_id = Articles.article_id 
				WHERE Articles.reconciled = 1 AND Reviews.article_id = $id
				AND Reviews.reviewer_id <> $reviewer_id AND Reviews.reviewer_id <> '9'";
		$results = $dbh->query($sql, PDO::FETCH_NUM);
		$results = $results->fetchAll();
		$rid = $results[0][0];

		if($results) return "<td><a style='color: #f0ad4e;' href='reconcile-form.php?form=recedit&id=" . $id . "&rid=" . $rid . "'><em>edit reconciled</em></a></td>";

	} else {

		$sql = "SELECT EXISTS (SELECT COUNT(*) FROM Reviews WHERE article_id = $id HAVING COUNT(*) > 1)";
		$results = $dbh->query($sql, PDO::FETCH_COLUMN, 0);
		$results = $results->fetchAll();

		if($results[0] == 1) {

			$sql = "SELECT Reviews.reviewer_id FROM Reviews JOIN Articles 
					ON Reviews.article_id = Articles.article_id 
					WHERE Reviews.article_id = $id AND Reviews.reviewer_id <> $reviewer_id 
					AND Articles.reconciled = 0"; 
			$results = $dbh->query($sql, PDO::FETCH_NUM);
			$results = $results->fetchAll();
			if($results) return "/ <a href='reconcile-form.php?form=reconcile&id=" . $id . "&rid=" . $results[0][0] . "'>reconcile</a></td>";
			else return "</td>";
		}
	}
}

function table_end(){

	$html = '</div></div></table>';
	echo $html;
}

// for outputting json things

function return_json($param, $article_id = '', $reviewer1_id = '', $reviewer2_id = '') {

	function query($sql) {
		$dbh = db_connect();
		$results = $dbh->query($sql);
		while($row = $results->fetch(PDO::FETCH_ASSOC)) $results_array[] = $row;
		$json = (isset($results_array)) ? json_encode($results_array) : "<em>sorry bro, no results ...</em>";
		$dbh = null;
		return $json;
	}

	switch ($param){
		// spits out reviewer info
		case('reviewer'):
			$id = (!$reviewer2_id) ? $reviewer1_id : $reviewer2_id;
			$sql = "SELECT initials FROM Reviewers WHERE reviewer_id = $id";
			return query($sql);

		// all the the articles reviewed for a reviewer_id
		case('reviewed'):

			$sql = "SELECT timestamp, Articles.article_id, title, issue, volume, date_published, reconciled 
					FROM Articles JOIN Reviews ON Articles.article_id = Reviews.article_id 
					WHERE reviewer_id = $reviewer1_id 
					ORDER BY UNIX_TIMESTAMP(timestamp) DESC";
			return query($sql);

		// the last article reviewed for a reviewer_id
		case('last'): 

			$sql = "SELECT timestamp, Articles.article_id, title, issue, volume, date_published, reconciled 
					FROM Articles JOIN Reviews ON Articles.article_id = Reviews.article_id 
					WHERE reviewer_id = $reviewer1_id 
					ORDER BY UNIX_TIMESTAMP(timestamp) DESC LIMIT 1";
			return query($sql);

		// 	all the data for a record from the Articles table for a particular article id
		case('article'):

			$sql = "SELECT * FROM Articles 
					WHERE article_id = $article_id";
			return query($sql);

		// 	all the data for a record from the Reviews table for a particular article id and reviewer id
		case('review'):

			$id = (!$reviewer2_id) ? $reviewer1_id : $reviewer2_id;
			$sql = "SELECT * FROM Reviews WHERE (article_id, reviewer_id) = ($article_id, $id)";
			return query($sql);

		// 	all the themes for a review from the Articles_Themes table for a particular article id / reviewer_id
		case('themes'):

			$id = (!$reviewer2_id) ? $reviewer1_id : $reviewer2_id;
			$sql = 	"SELECT theme, if_main FROM Themes 
					JOIN Articles_Themes ON Articles_Themes.theme_id = Themes.theme_id 
					WHERE (Articles_Themes.article_id, Articles_Themes.reviewer_id) = ($article_id, $id)";
			return query($sql);

		// 	all the tags for a review from the Articles_Tags table for a particular article id / reviewer_id
		case('tags'):

			$id = (!$reviewer2_id) ? $reviewer1_id : $reviewer2_id;
			$sql = "SELECT category, tag, if_main FROM Tags 
					JOIN Articles_Tags ON Tags.tag_id = Articles_Tags.tag_id 
					WHERE (Articles_Tags.article_id, Articles_Tags.reviewer_id) = ($article_id, $id)";
			return query($sql);

		// 	all the themes
		case('themes_list'):

			$sql = "SELECT theme FROM Themes";
			return query($sql);	

		// 	all the Articles table data
		case('dump_articles'):

			$sql_columns = implode(',', $articles);
			$sql = "SELECT $sql_columns FROM Articles";
			return query($sql);	

		// 	all the Articles table data
		case('dump_reviews'):

			$sql_columns = implode(',', $reviews);
			$sql = "SELECT $sql_columns FROM Reviews";
			return query($sql);	

		// 	all the Articles_Themes table data
		case('dump_themes'):

			$sql = "SELECT article_id, reviewer_id, theme FROM Themes 
					JOIN Articles_Themes ON Themes.theme_id = Articles_Themes.theme_id";
			return query($sql);	

		// 	all the Articles_Tags table data
		case('dump_tags'):
			$sql = "SELECT category, tag FROM Tags";
			$sql_alt = "SELECT category, article_id, reviewer_id, category, tag 
					FROM Tags JOIN Articles_Tags 
					ON Tags.tag_id = Articles_Tags.tag_id";
			return query($sql);

		case('test_table'):
			$columnsArray = array('date_published', 'title', 'author', 'tag', 'theme');
			$sql_columns = implode(',', $columnsArray);
			$sql = "SELECT $sql_columns FROM Articles 
					JOIN Articles_Tags ON Articles.article_id = Articles_Tags.article_id
					JOIN Articles_Themes ON Articles.article_id = Articles_Themes.article_id
					JOIN Tags ON Articles_Tags.tag_id = Tags.tag_id
					JOIN Themes ON Articles_Themes.theme_id = Themes.theme_id
					WHERE Articles_Tags.if_main = 1 AND Articles_Themes.if_main = 1";
			return query($sql);
		}
}

// for writes js functions into the footer based on get 'form' value, adding the appropriate article/reviewer id values
function js_form_functions() {

		$view = (isset($_GET['form'])) ? $_GET['form'] : '';
		$js = '<script>';
		// $js .= 'losForm.prepare;';

		if(isset($view)){
			switch($view) {

				// case('reviewer.php'): break;

				case('add'): 

					$js .= 'losForm.lastReview();';
					$js .= '</script>'; 
					return $js;

				case('edit'):
					$a_id = $_GET['id'];
					$js .= "losForm.editReview($a_id);";
					$js .= '</script>';
					return $js;

				case('recedit'):
					$a_id = $_GET['id'];
					$r1_id = $_SESSION['reviewer_id'];
					$r2_id = $_GET['rid'];
					$js .= "losForm.editReconciled($a_id, $r1_id, $r2_id);";
					$js .= '</script>';
					return $js;

				case('reconcile'):
					$a_id = $_GET['id'];
					$r_id = $_GET['rid'];
					$js .= "losForm.reconcileReview($a_id, $r_id);";
					$js .= '</script>';
					return $js;

				case('data-table.php'):
				case('visualization.php'):
			}
		}
}