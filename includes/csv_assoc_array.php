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

// adds all data from the land of sunshine googlespreadsheet (text datapoints (responses)) published as a csv to the appropriate tables
// relies heavily on a number of reusable utility scripts in utilities.php

// a variable to carry the url of the google spreadsheet:
// -- the spreadsheet must be 'published to the web' in google docs for it to be accessible
// -- the the suffix needs to be 'output=csv'
$gcsv = 'https://docs.google.com/spreadsheet/pub?key=0AqAqvqKN28wbdHA2R3pHLTBrZHJFbE1kOUtZLV9GSEE&output=csv';
$gcsv_local = 'http://localhost:8888/los/textdata_short.csv';

$gcsv_copy = 'https://docs.google.com/spreadsheet/pub?key=0AqAqvqKN28wbdGN0OFpuVGZFYnRSdFhjd05HYVFncEE&output=csv';
$gcsv_short = 'https://docs.google.com/spreadsheet/pub?key=0AqAqvqKN28wbdEhrSkVqQ3Ezb2p5ZV9UWnFUMGV5dEE&output=csv';

function csvToArray($url) {

	$handle = fopen($gcsv, 'r');

	// an array for mapping columns to table variables
	$columns = array(
		'timestamp',
		'initials',
		'title',
		'author',
		'location',
		'page_start',
		'page_end',
		'volume',
		'issue',
		'date_published',
		'type',
		'groups',
		'persons',
		'entities',
		'places',
		'activities',
		'flora_fauna',
		'commodities',
		'events',
		'works',
		'technologies',
		'environments',
		'themes',
		'main',
		'summary',
		'notes',
		'x',
		'research_notes',
		'narration',
		'narration_pov',
		'narration_embedded',
		'narration_tense',
		'narration_tenseshift'
		);

	// creates an associative array out of each row of the csv -- each cell is the value, each $columns element is the key
	while ($row = fgetcsv($handle)) {

		$csv[] = array_combine($columns, $row);
	}
	fclose($handle);

	unset($csv[0]);

	return $csv;
}

// attempts to connect to the los database
try {

	$dbh = db_connect();

	$stmt_articles = prepare_pdo_statement($articles, 'Articles', $dbh);
	$stmt_reviews = prepare_pdo_statement($reviews, 'Reviews', $dbh);
	$stmt_articles_themes = prepare_pdo_statement($articles_themes, 'Articles_Themes', $dbh);
	$stmt_articles_tags = prepare_pdo_statement($articles_tags, 'Articles_Tags', $dbh);

// helper counter / no purpose other than debugging
	$i = 1;
	
	// starts looping through each row of the csv
	foreach($csv as $row){

		echo_line('<br /><br /><strong>' . $i . '</strong>' );
		$i++;

// binds values and executes to the Articles table statement
		echo_line('<strong>' . $row['title'] . '</strong>');
		bind_value($row['title'], $stmt_articles, 'title');

		// echo_line($row['author']);
		bind_value($row['author'], $stmt_articles, 'author');

		// echo_line($row['location']);
		bind_value($row['location'], $stmt_articles, 'location');

		// echo_line($row['page_start']);
		bind_value($row['page_start'], $stmt_articles, 'page_start');

		// echo_line($row['page_end']);
		bind_value($row['page_end'], $stmt_articles, 'page_end');

		// echo_line($row['volume']);
		bind_value($row['volume'], $stmt_articles, 'volume');

		// echo_line($row['issue']);
		bind_value($row['issue'], $stmt_articles, 'issue');

		$date = string_format($row['date_published'], 'date_published');
		// echo_line($date);
		bind_value($date, $stmt_articles, 'date_published');

		$type = string_format($row['type'],'type');
		// echo_line($type);
		bind_value($type, $stmt_articles, 'type');

		$stmt_articles->execute();

// binds values and executes Reviews table statement

		// sets the current article_id as the last updated row, from the Articles table in this case
		$article_id = $dbh->lastInsertId();
		// echo_line($article_id);
		bind_value($article_id, $stmt_reviews, 'article_id');

		// grabs the reviewer_id or, if two sets of initials appear as in a reconciled article, sets the initials to 'rec'
		$reviewer_id = (strlen($row['initials']) < 4) ? return_id($row['initials'], 'reviewer_id', 'initials', 'Reviewers', $dbh)
			: 9;
		// echo_line($row['initials']);
		// echo_line($reviewer_id);
		bind_value($reviewer_id, $stmt_reviews, 'reviewer_id');

		// if reconciled, updates the corresponding article in the Articles table to 'reconciled'
		if($reviewer_id == 'rec') {update_reconciled($article_id, $dbh);}

		$timestamp = string_format($row['timestamp'], 'timestamp');
		// echo_line($timestamp);
		bind_value($timestamp, $stmt_reviews, 'timestamp');

		// echo_line($row['summary']);
		bind_value($row['summary'], $stmt_reviews, 'summary');

		// echo_line($row['notes']);
		bind_value($row['notes'], $stmt_reviews, 'notes');

		// echo_line($row['research_notes']);
		bind_value($row['research_notes'], $stmt_reviews, 'research_notes');

		// echo_line($row['narration_pov']);
		bind_value($row['narration_pov'], $stmt_reviews, 'narration_pov');

		$narration_embedded = string_format($row['narration_embedded'], 'bool');
		// echo_line($narration_embedded);
		bind_value($narration_embedded, $stmt_reviews, 'narration_embedded');

		// echo_line($row['narration_tense']);
		bind_value($row['narration_tense'], $stmt_reviews, 'narration_tense');

		$narration_tenseshift = string_format($row['narration_tenseshift'], 'bool');
		// echo_line($row['narration_tenseshift']);
		bind_value($row['narration_tenseshift'], $stmt_reviews, 'narration_tenseshift');

		$stmt_reviews->execute();

// binds values and executes Articles_Themes table statement
		foreach(string_format($row['themes'], 'array') as $value) {

			$value = string_format($value, 'theme');
			echo_line($value);
			$theme_id = return_id($value, 'theme_id', 'theme', 'Themes', $dbh);
			// echo_line('theme ' . $theme_id);
			if($theme_id && !if_exists($theme_id, 'Articles_Themes', 'theme_id', $dbh, $article_id, 'article_id')){			
				bind_value($theme_id, $stmt_articles_themes, 'theme_id');
				bind_value($reviewer_id, $stmt_articles_themes, 'reviewer_id');
				bind_value($article_id, $stmt_articles_themes, 'article_id');
				$stmt_articles_themes->execute();							
				} 
				else { echo_line('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong>' . $value . '</strong> not a theme ... check data');}
			}

// binds values and executes Articles_Tags table statement, adds tags to Tags table if new
		tag_array($row['groups'], 'groups', $article_id, $reviewer_id, $stmt_articles_tags, $dbh);
		tag_array($row['entities'], 'entities', $article_id, $reviewer_id, $stmt_articles_tags, $dbh);
		tag_array($row['places'], 'places', $article_id, $reviewer_id, $stmt_articles_tags, $dbh);
		tag_array($row['activities'], 'activities', $article_id, $reviewer_id, $stmt_articles_tags, $dbh);
		tag_array($row['flora_fauna'], 'flora_fauna', $article_id, $reviewer_id, $stmt_articles_tags, $dbh);
		tag_array($row['commodities'], 'commodities', $article_id, $reviewer_id, $stmt_articles_tags, $dbh);
		tag_array($row['events'], 'events', $article_id, $reviewer_id, $stmt_articles_tags, $dbh);
		tag_array($row['works'], 'works', $article_id, $reviewer_id, $stmt_articles_tags, $dbh);
		tag_array($row['technologies'], 'technologies', $article_id, $reviewer_id, $stmt_articles_tags, $dbh);
		tag_array($row['environments'], 'environments', $article_id, $reviewer_id, $stmt_articles_tags, $dbh);

// updates Articles_Tags and Articles_Themes tables if marked as a Main Element
		foreach (string_format($row['main'], 'array') as $value) {
			// echo_line($value);
			update_main($value, $dbh);
		}

	}
} catch(PDOException $e) { echo $e->getMessage(); }

