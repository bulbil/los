<?php
require '../html/header.html';
include 'navbar.php';
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
<?php echoLine('<div>'); echoLine(print_r($_POST)); echoLine('</div>'); ?>
</div>
</div>

<?php 

// attempts to connect to the los database
try {

	$dbh = db_connect();

	$stmt_articles = pdoStatementPrepare($articles, 'Articles', $dbh);
	$stmt_reviews = pdoStatementPrepare($reviews, 'Reviews', $dbh);
	$stmt_articles_themes = pdoStatementPrepare($articles_themes, 'Articles_Themes', $dbh);
	$stmt_articles_tags = pdoStatementPrepare($articles_tags, 'Articles_Tags', $dbh);

// binds values and executes to the Articles table statement
		editRowArticle($_POST, $stmt_articles);

// binds values and executes Reviews table statement

		// sets the current article_id as the last updated row, from the Articles table in this case
		$article_id = $dbh->lastInsertId();
		$reviewer_id = returnReviewerID($_POST['initials'], $article_id, $dbh);
		// echoLine($article_id);

		editRowReview($article_id, $reviewer_id, $_POST, $stmt_reviews, $dbh);

// binds values and executes Articles_Themes table statement
		editThemes($article_id, $reviewer_id, $_POST['themes'], $stmt_articles_themes, $dbh);

// binds values and executes Articles_Tags table statement, adds tags to Tags table if new

		foreach ($_POST as $key=>$value) {
			switch($key){
				case ('groups') :
				case ('entities') :
				case ('places') :
				case ('activities') :
				case ('flora_fauna') :
				case ('commodities') :
				case ('events') :
				case ('works') :
				case ('technologies') :
				case ('environments') : tagArray($value, $key, $article_id, $reviewer_id, $stmt_articles_tags, $dbh);
			}
		}

// updates Articles_Tags and Articles_Themes tables if marked as a Main Element
		foreach (stringFormat($_POST['main'], 'array') as $value) {

			updateMain($value, $dbh);
		}

} catch(PDOException $e) { echo $e->getMessage(); }