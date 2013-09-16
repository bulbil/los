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
	$html . "</div></div>";
	echo_line($html);
	}

// attempts to connect to the los database
try {

	$dbh = db_connect();
	$form = $_POST['form'];

	$article_id = (isset($_POST['id'])) ? $_POST['id'] : return_article_id($row,$dbh);

	if($article_id) { 
		$sql = sql_implode($articles, 'Articles', 'update', $article_id, 'article_id');
		$stmt_articles = $dbh->prepare($sql);

	} else {

		$_POST['date_published'] = string_format($_POST['date_published'], 'date_submit');
		$stmt_articles = prepare_pdo_statement($articles, 'Articles', $dbh);
	}

	if($form == 'add' || $form == 'reconcile') { 

		$stmt_reviews = prepare_pdo_statement($reviews, 'Reviews', $dbh);
		$stmt_articles_themes = prepare_pdo_statement($articles_themes, 'Articles_Themes', $dbh);
		$stmt_articles_tags = prepare_pdo_statement($articles_tags, 'Articles_Tags', $dbh);
		$_POST['timestamp'] = date('Y-m-d H:i:s');
	}

	if ($form == 'edit') { 
		// some kind of check for whether the article info is being updated ...
		// $article_id = return_article_id($row,$dbh);

		// }

		$sql = sql_implode($reviews, 'Reviews', 'update', $article_id, 'article_id');
		$stmt_reviews = $dbh->prepare($sql);

		$sql = sql_implode($articles_tags, 'Articles_Tags', 'update', $article_id, 'article_id');
		$stmt_articles_tags = $dbh->prepare($sql);

		$sql = sql_implode($articles_themes, 'Articles_Themes', 'update', $article_id, 'article_id');
		$stmt_articles_themes = $dbh->prepare($sql);
	}

// binds values and executes to the Articles table statement
		if($form == 'reconcile') { 

			$_POST['date_published'] = string_format($_POST['date_published'], 'date_submit');
			edit_article($_POST, $stmt_articles, true);
		} else { 

			echo_line($_POST['date_published']);
			edit_article($_POST, $stmt_articles);
		}


// binds values and executes Reviews table statement
		$reviewer_id = ($form == 'reconcile') ? '9' : $_SESSION['reviewer_id'];
		$article_id = ($article_id) ? $article_id : $dbh->lastInsertId();

		$_POST['narration_embedded'] = (isset($_POST['narration_embedded'])) ? $_POST['narration_embedded'] : 0;
		$_POST['narration_tenseshift'] = (isset($_POST['narration_tenseshift'])) ? $_POST['narration_tenseshift'] : 0;
		edit_review($article_id, $reviewer_id, $_POST, $stmt_reviews, $dbh);
// binds values and executes Articles_Themes table statement
		edit_article_themes($article_id, $reviewer_id, $_POST['themes'], $stmt_articles_themes, $dbh);
// binds values and executes Articles_Tags table statement, adds tags to Tags table if new

		foreach ($_POST as $key=>$value) {
			switch($key){
				case ('groups') :
				case ('entities') :
				case ('places') :
				case ('activities') :
				case ('florafauna') :
				case ('commodities') :
				case ('events') :
				case ('works') :
				case ('technologies') :
				case ('environments') : edit_article_tags($value, $key, $article_id, $reviewer_id, $stmt_articles_tags, $dbh);
			}
		}

// updates Articles_Tags and Articles_Themes tables if marked as a Main Element

		if($_POST['main']){
			foreach (string_format($_POST['main'], 'array') as $value) {
				$value = preg_split('/:/', $value);
				$value = $value[1];
				echo_line('update main: ' . $value);
				update_main($value, $article_id, $reviewer_id, $dbh);
			}
		}

} catch(PDOException $e) { echo $e->getMessage(); }