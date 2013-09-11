<?php
session_start();
require '../html/header.html';
include '../masthead.html';
include 'db.php';
include 'utilities.php';
require '../html/footer.html';
?>

<div class='container'>
<div class='row'>
<div class="col-md-6 col-md-offset-3">
<div class="alert alert-success">Nice One! You've successfully added a new article</div>
</div>

<div class="col-md-6 col-md-offset-3">
<div class="alert alert-warning">Nice One! You've successfully updated an existing article</div>
</div>
</div>
<div class='row'>
<?php foreach($_POST as $key => $value) {
	$html = (!is_array($value)) ? $key . ': ' . $value : $key . ': ' . implode(',', $value);
	echo_line($html);
	} ?>
</div>
</div>

<?php 
// attempts to connect to the los database
// try {

// 	$dbh = db_connect();

// 	$stmt_articles = prepare_pdo_statement($articles, 'Articles', $dbh);
// 	$stmt_reviews = prepare_pdo_statement($reviews, 'Reviews', $dbh);
// 	$stmt_articles_themes = prepare_pdo_statement($articles_themes, 'Articles_Themes', $dbh);
// 	$stmt_articles_tags = prepare_pdo_statement($articles_tags, 'Articles_Tags', $dbh);

// // binds values and executes to the Articles table statement
// 		edit_row_article($_POST, $stmt_articles);

// // binds values and executes Reviews table statement

// 		// sets the current article_id as the last updated row, from the Articles table in this case
// 		$article_id = $dbh->lastInsertId();
// 		$reviewer_id = return_reviewer_id($_POST['initials'], $article_id, $dbh);
// 		// echo_line($article_id);

// 		edit_row_review($article_id, $reviewer_id, $_POST, $stmt_reviews, $dbh);

// // binds values and executes Articles_Themes table statement
// 		edit_themes($article_id, $reviewer_id, $_POST['themes'], $stmt_articles_themes, $dbh);

// // binds values and executes Articles_Tags table statement, adds tags to Tags table if new

// 		foreach ($_POST as $key=>$value) {
// 			switch($key){
// 				case ('groups') :
// 				case ('entities') :
// 				case ('places') :
// 				case ('activities') :
// 				case ('flora_fauna') :
// 				case ('commodities') :
// 				case ('events') :
// 				case ('works') :
// 				case ('technologies') :
// 				case ('environments') : tag_array($value, $key, $article_id, $reviewer_id, $stmt_articles_tags, $dbh);
// 			}
// 		}

// // updates Articles_Tags and Articles_Themes tables if marked as a Main Element
// 		foreach (string_format($_POST['main'], 'array') as $value) {

// 			update_main($value, $dbh);
// 		}

// } catch(PDOException $e) { echo $e->getMessage(); }