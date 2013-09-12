<?php
session_start();
require '../html/header.html';
include '../html/masthead.html';
include 'db.php';
include 'utilities.php';
require '../html/footer.html';
?>

<div class='container'>
<div class='row'>
<div class="col-md-6 col-md-offset-3">
	
<?php

	switch ($_POST['form']) {
		case 'add': echo "<div class='alert alert-success'>Nice One! You've successfully added a new review</div>"; break;
		case 'edit': echo "<div class='alert alert-warning'>Nice One! You've successfully updated an existing review</div>"; break;
		case 'reconcile': echo "<div class='alert alert-info'>Nice One! You've successfully added a reconciled review</div>"; break;
	}
	foreach($_POST as $key => $value) {
	$html = (!is_array($value)) ? $key . ': ' . $value : $key . ': ' . implode(',', $value);
	echo_line($html);
	}
?>


</div>
</div>
<?php

// attempts to connect to the los database
try {

	$dbh = db_connect();
	$form = $_POST['form'];

	$article_id = (isset($_POST['id'])) ? $_POST['id'] : return_article_id($row,$dbh);

	if($article_id) { 
		$sql = sql_implode($articles, 'Articles', 'update', $article_id, 'article_id');
		$stmt_articles = $dbh->prepare($sql);

	} else {

		$stmt_articles = prepare_pdo_statement($articles, 'Articles', $dbh);
	}

	if($form == 'add' || $form == 'reconcile') { 

		$stmt_reviews = prepare_pdo_statement($reviews, 'Reviews', $dbh);
		$stmt_articles_themes = prepare_pdo_statement($articles_themes, 'Articles_Themes', $dbh);
		$stmt_articles_tags = prepare_pdo_statement($articles_tags, 'Articles_Tags', $dbh);
		$_POST['timestamp'] = date('Y-m-d H:i:s');
	}

	if ($form == 'edit') { 

		$sql = sql_implode($reviews, 'Reviews', 'update', $dbh, $article_id, 'article_id');
		$stmt_reviews = $dbh->prepare($sql);

		$sql = sql_implode($articles_tags, 'Articles_Tags', 'update', $dbh, $article_id, 'article_id');
		$stmt_articles_tags = $dbh->prepare($sql);

		$sql = sql_implode($articles_themes, 'Articles_Themes', 'update', $dbh, $article_id, 'article_id');
		$stmt_articles_themes = $dbh->prepare($sql);
	}

// binds values and executes to the Articles table statement
		if($form == 'reconcile') { edit_row_article($_POST, $stmt_articles, true);
		} else { edit_row_article($_POST, $stmt_articles);}


// binds values and executes Reviews table statement

		$reviewer_id = ($form == 'reconcile') ? '9' : $_SESSION['reviewer_id'];
		$article_id = ($article_id) ? $article_id : $dbh->lastInsertId();
		echo_line($article_id);

		edit_row_review($article_id, $reviewer_id, $_POST, $stmt_reviews, $dbh);

// binds values and executes Articles_Themes table statement
		edit_themes($article_id, $reviewer_id, $_POST['themes-list'], $stmt_articles_themes, $dbh);

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
				case ('environments') : tag_array($value, $key, $article_id, $reviewer_id, $stmt_articles_tags, $dbh);
			}
		}

// updates Articles_Tags and Articles_Themes tables if marked as a Main Element
		foreach (string_format($_POST['main'], 'array') as $value) {

			$value = trim(preg_split('/:/', $value)[1]);
			echo_line($value);
			update_main($value, $dbh);
		}

} catch(PDOException $e) { echo $e->getMessage(); }