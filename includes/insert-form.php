<?php
//attempts to connect to the los database

function edit_tables($array, $str, $int, $if_image = false){

	if(isset($array['id'])) $article_id = (isset($_SESSION['db_article']) && $_SESSION['db_article']['id'] != $array['id']) ? $_SESSION['db_article']['id'] : $array['id'];
	else $article_id = $_SESSION['db_image']['article_id'];

	if($if_image) $img_id = (isset($_SESSION['db_image']) && $_SESSION['db_image']['img_id'] != $array['img_id']) ? $_SESSION['db_image']['img_id'] : $array['img_id'];
	echo_line($img_id);
	try {

		$dbh = db_connect();

		if($int == 2) edit_images_table($array, $img_id, $str, $dbh);
		elseif($if_image) {
			edit_articles_table($array, $article_id, $str, $dbh);
			$article_id = ($article_id > 0) ? $article_id : $dbh->lastInsertId();
			edit_images_table($array, $img_id, $str, $dbh, $article_id);
		}
		elseif($str == 'reconcile' || $int != 1) edit_articles_table($array, $article_id, $str, $dbh);

		$reviewer_id = ($str == 'reconcile' || $str == 'recedit') ? '9' : $_SESSION['reviewer_id'];		

		echo_line($if_image);

		if($if_image) {
			$id = ($img_id != 0) ? $img_id : $dbh->lastInsertId();
			edit_image_reviews_table($array, $id, $reviewer_id, $str, $dbh);
		}
		else {
			$id = ($article_id != 0) ? $article_id : $dbh->lastInsertId();
			edit_reviews_table($array, $id, $reviewer_id, $str, $dbh);
		}

		edit_themes_table($array, $id, $reviewer_id, $str, $dbh);
		edit_tags_table($array, $id, $reviewer_id, $str, $dbh);
		edit_main($array, $id, $reviewer_id, $str, $dbh);
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

function edit_images_table($array, $img_id, $str, $pdo, $article_id = 0) {

	$columns = $GLOBALS['images'];

	if($article_id == 0) unset($columns[0]);
	else $array['article_id'] = $article_id;

	if($img_id) $sql = sql_implode($columns, 'Images', 'update', array('img_id'), array($img_id));
	$stmt_images = (isset($sql)) ? $pdo->prepare($sql) : prepare_pdo_statement($columns, 'Images', $pdo);

	$array['img_date'] = string_format($array['img_date'], 'date_submit');
	$array['img_rotated'] = (isset($array['img_rotated'])) ? $array['img_rotated'] : 0;

	execute_image($array, $stmt_images);
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

function edit_image_reviews_table($array, $img_id, $reviewer_id, $str, $pdo){

	global $image_reviews;

	if($str == 'edit' && $array['img_id'] == $img_id) { 
		$sql = sql_implode($image_reviews, 'Image_Reviews', 'update', array('img_id', 'reviewer_id'), array($img_id, $reviewer_id));
	}

	$stmt_image_reviews =  (isset($sql)) ? $pdo->prepare($sql) : prepare_pdo_statement($image_reviews, 'Image_Reviews', $pdo);
	
	// normalizing a few fields
	$array['timestamp'] = date('Y-m-d H:i:s');

	//logic for executing statements
	execute_image_review($img_id, $reviewer_id, $array, $stmt_image_reviews);
}

// binds values and executes Articles_Themes table statement
function edit_themes_table($array, $id, $reviewer_id, $str, $pdo) {

	$if_image = ($array['type'] == 'Image') ? true : false;
	$table = (!$if_image) ? 'Articles_Themes' : 'Images_Themes';
	$columns = (!$if_image) ? $GLOBALS['articles_themes'] : $GLOBALS['images_themes'];

	$stmt_themes = prepare_pdo_statement($columns, $table, $pdo);
	execute_themes($id, $reviewer_id, $array['themes'], $stmt_themes, $pdo, $if_image);
}

// binds values and executes Articles_Tags table statement, adds tags to Tags table if new
function edit_tags_table($array, $id, $reviewer_id, $str, $pdo) {
	
	global $categories;
	
	$if_image = ($array['type'] == 'Image') ? true : false;
	$table = (!$if_image) ? 'Articles_Tags' : 'Images_Tags';
	$columns = (!$if_image) ? $GLOBALS['articles_tags'] : $GLOBALS['images_tags'];

	$sql = "DELETE FROM $table WHERE ($columns[0], `reviewer_id`) = ('$id', '$reviewer_id')";
	$pdo->query($sql);

	$stmt_tags = prepare_pdo_statement($columns, $table, $pdo);
	foreach($categories as $category) execute_tags($array[$category], $category, $id, $reviewer_id, $stmt_tags, $pdo, $if_image);
}

function edit_main($array, $id, $reviewer_id, $str, $pdo) {
	// updates Articles_Tags and Articles_Themes tables if marked as a Main Element	if($form_data['main']){
	
	$if_image = ($array['type'] == 'Image') ? true : false;
	$table = (!$if_image) ? 'Articles_Themes' : 'Images_Themes';
	$columns = (!$if_image) ? $GLOBALS['articles_themes'] : $GLOBALS['images_themes'];

	$sql = "UPDATE $table SET `if_main` = 0 WHERE ($columns[0],`reviewer_id`) = ($id, $reviewer_id)";
	$pdo->query($sql);
	$table = (!$if_image) ? 'Articles_Tags' : 'Images_Tags';
	$pdo->query($sql);

	foreach (string_format($array['main'], 'array') as $element) {

		if($element){	
			$element = preg_split('/:/', $element);
			$element = string_format($element[1]);
			update_main($element, $id, $reviewer_id, $pdo, $if_image);
		}
	}
}