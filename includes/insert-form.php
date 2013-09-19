<?php
//attempts to connect to the los database

function edit_tables($array, $str, $int){

	if(isset($_SESSION['db_data']) && $_SESSION['db_data']['id'] != $array['id']) $article_id = $_SESSION['db_data']['id'];
	else $article_id = $array['id'];

	try {

		$dbh = db_connect();
		
		if($str == 'reconcile' || $int != 1) { edit_articles_table($array, $article_id, $str, $dbh); }

		$article_id = ($article_id != 0) ? $article_id : $dbh->lastInsertId();
		$reviewer_id = ($str == 'reconcile' || $str == 'recedit') ? '9' : $_SESSION['reviewer_id'];

		edit_reviews_table($array, $article_id, $reviewer_id, $str, $dbh);
		edit_articles_themes_table($array, $article_id, $reviewer_id, $str, $dbh);
		edit_articles_tags_table($array, $article_id, $reviewer_id, $str, $dbh);
		edit_main($array, $article_id, $reviewer_id, $str, $dbh);
	} catch(PDOException $e) { echo $e->getMessage(); }
}

function edit_articles_table($array, $article_id, $str, $pdo) {

	global $articles;

	if($article_id) $sql = sql_implode($articles, 'Articles', 'update', array('article_id'), array($article_id));
	$stmt_articles = (isset($sql)) ? $pdo->prepare($sql) : prepare_pdo_statement($articles, 'Articles', $pdo);

	$array['date_published'] = string_format($array['date_published'], 'date_submit');

	$rec = ($str == 'reconcile') ? 1 : 0; 

	execute_article($array, $stmt_articles, $rec);
}

// binds values and executes Reviews table statement
function edit_reviews_table($array, $article_id, $reviewer_id, $str, $pdo){

	global $reviews;

	if(($str == 'edit' && $array['id'] == $article_id) || $str == 'recedit') { 

		$sql = sql_implode($reviews, 'Reviews', 'update', array('article_id', 'reviewer_id'), array($article_id, $reviewer_id));
	}

	$stmt_reviews =  (isset($sql)) ? $pdo->prepare($sql) : prepare_pdo_statement($reviews, 'Reviews', $pdo);
	
	// normalizing a few fields
	$array['timestamp'] = date('Y-m-d H:i:s');
	$array['narration_embedded'] = (isset($array['narration_embedded'])) ? $array['narration_embedded'] : 0;
	$array['narration_tenseshift'] = (isset($array['narration_tenseshift'])) ? $array['narration_tenseshift'] : 0;

	//logic for executing statements
	execute_review($article_id, $reviewer_id, $array, $stmt_reviews);
}

// binds values and executes Articles_Themes table statement
function edit_articles_themes_table($array, $article_id, $reviewer_id, $str, $pdo) {

	global $articles_themes;
	$stmt_articles_themes = prepare_pdo_statement($articles_themes, 'Articles_Themes', $pdo);
	execute_article_themes($article_id, $reviewer_id, $array['themes'], $stmt_articles_themes, $pdo);
}

// binds values and executes Articles_Tags table statement, adds tags to Tags table if new
function edit_articles_tags_table($array, $article_id, $reviewer_id, $str, $pdo) {
	
	global $articles_tags;
	global $categories;

	$sql = "DELETE FROM `Articles_Tags` WHERE (`article_id`, `reviewer_id`) = ('$article_id', '$reviewer_id')";
	$pdo->query($sql);
	
	$stmt_articles_tags = prepare_pdo_statement($articles_tags, 'Articles_Tags', $pdo);
	
	foreach($categories as $category) execute_article_tags($array[$category], $category, $article_id, $reviewer_id, $stmt_articles_tags, $pdo);
}

function edit_main($array, $article_id, $reviewer_id, $str, $pdo) {
	// updates Articles_Tags and Articles_Themes tables if marked as a Main Element	if($form_data['main']){
	$table = 'Articles_Themes';	
	$sql = "UPDATE $table SET `if_main` = 0 WHERE (`article_id`,`reviewer_id`) = ($article_id, $reviewer_id)";
	$pdo->query($sql);
	$table = 'Articles_Tags';
	$pdo->query($sql);

	foreach (string_format($array['main'], 'array') as $element) {

		if($element){	
			$element = preg_split('/:/', $element);
			$element = string_format($element[1]);
			update_main($element, $article_id, $reviewer_id, $pdo);
		}
	}
}