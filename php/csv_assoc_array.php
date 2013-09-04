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

	$stmt_articles = pdoStatementPrepare($articles, 'Articles', $dbh);
	$stmt_reviews = pdoStatementPrepare($reviews, 'Reviews', $dbh);
	$stmt_articles_themes = pdoStatementPrepare($articles_themes, 'Articles_Themes', $dbh);
	$stmt_articles_tags = pdoStatementPrepare($articles_tags, 'Articles_Tags', $dbh);

// helper counter / no purpose other than debugging
	$i = 1;
	
	// starts looping through each row of the csv
	foreach($csv as $row){

		echoLine('<br /><br /><strong>' . $i . '</strong>' );
		$i++;

// binds values and executes to the Articles table statement
		echoLine('<strong>' . $row['title'] . '</strong>');
		bindValue($row['title'], $stmt_articles, 'title');

		// echoLine($row['author']);
		bindValue($row['author'], $stmt_articles, 'author');

		// echoLine($row['location']);
		bindValue($row['location'], $stmt_articles, 'location');

		// echoLine($row['page_start']);
		bindValue($row['page_start'], $stmt_articles, 'page_start');

		// echoLine($row['page_end']);
		bindValue($row['page_end'], $stmt_articles, 'page_end');

		// echoLine($row['volume']);
		bindValue($row['volume'], $stmt_articles, 'volume');

		// echoLine($row['issue']);
		bindValue($row['issue'], $stmt_articles, 'issue');

		$date = stringFormat($row['date_published'], 'date_published');
		// echoLine($date);
		bindValue($date, $stmt_articles, 'date_published');

		$type = stringFormat($row['type'],'type');
		// echoLine($type);
		bindValue($type, $stmt_articles, 'type');

		$stmt_articles->execute();

// binds values and executes Reviews table statement

		// sets the current article_id as the last updated row, from the Articles table in this case
		$article_id = $dbh->lastInsertId();
		// echoLine($article_id);
		bindValue($article_id, $stmt_reviews, 'article_id');

		// grabs the reviewer_id or, if two sets of initials appear as in a reconciled article, sets the initials to 'rec'
		$reviewer_id = (strlen($row['initials']) < 4) ? returnID($row['initials'], 'reviewer_id', 'initials', 'Reviewers', $dbh)
			: 9;
		// echoLine($row['initials']);
		// echoLine($reviewer_id);
		bindValue($reviewer_id, $stmt_reviews, 'reviewer_id');

		// if reconciled, updates the corresponding article in the Articles table to 'reconciled'
		if($reviewer_id == 'rec') {updateReconciled($article_id, $dbh);}

		$timestamp = stringFormat($row['timestamp'], 'timestamp');
		// echoLine($timestamp);
		bindValue($timestamp, $stmt_reviews, 'timestamp');

		// echoLine($row['summary']);
		bindValue($row['summary'], $stmt_reviews, 'summary');

		// echoLine($row['notes']);
		bindValue($row['notes'], $stmt_reviews, 'notes');

		// echoLine($row['research_notes']);
		bindValue($row['research_notes'], $stmt_reviews, 'research_notes');

		// echoLine($row['narration_pov']);
		bindValue($row['narration_pov'], $stmt_reviews, 'narration_pov');

		$narration_embedded = stringFormat($row['narration_embedded'], 'bool');
		// echoLine($narration_embedded);
		bindValue($narration_embedded, $stmt_reviews, 'narration_embedded');

		// echoLine($row['narration_tense']);
		bindValue($row['narration_tense'], $stmt_reviews, 'narration_tense');

		$narration_tenseshift = stringFormat($row['narration_tenseshift'], 'bool');
		// echoLine($row['narration_tenseshift']);
		bindValue($row['narration_tenseshift'], $stmt_reviews, 'narration_tenseshift');

		$stmt_reviews->execute();

// binds values and executes Articles_Themes table statement
		foreach(stringFormat($row['themes'], 'array') as $value) {

			$value = stringFormat($value, 'theme');
			echoLine($value);
			$theme_id = returnID($value, 'theme_id', 'theme', 'Themes', $dbh);
			// echoLine('theme ' . $theme_id);
			if($theme_id && !ifExists($theme_id, 'Articles_Themes', 'theme_id', $dbh, $article_id, 'article_id')){			
				bindValue($theme_id, $stmt_articles_themes, 'theme_id');
				bindValue($reviewer_id, $stmt_articles_themes, 'reviewer_id');
				bindValue($article_id, $stmt_articles_themes, 'article_id');
				$stmt_articles_themes->execute();							
				} 
				else { echoLine('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong>' . $value . '</strong> not a theme ... check data');}
			}

// binds values and executes Articles_Tags table statement, adds tags to Tags table if new
		tagArray($row['groups'], 'groups', $article_id, $reviewer_id, $stmt_articles_tags, $dbh);
		tagArray($row['entities'], 'entities', $article_id, $reviewer_id, $stmt_articles_tags, $dbh);
		tagArray($row['places'], 'places', $article_id, $reviewer_id, $stmt_articles_tags, $dbh);
		tagArray($row['activities'], 'activities', $article_id, $reviewer_id, $stmt_articles_tags, $dbh);
		tagArray($row['flora_fauna'], 'flora_fauna', $article_id, $reviewer_id, $stmt_articles_tags, $dbh);
		tagArray($row['commodities'], 'commodities', $article_id, $reviewer_id, $stmt_articles_tags, $dbh);
		tagArray($row['events'], 'events', $article_id, $reviewer_id, $stmt_articles_tags, $dbh);
		tagArray($row['works'], 'works', $article_id, $reviewer_id, $stmt_articles_tags, $dbh);
		tagArray($row['technologies'], 'technologies', $article_id, $reviewer_id, $stmt_articles_tags, $dbh);
		tagArray($row['environments'], 'environments', $article_id, $reviewer_id, $stmt_articles_tags, $dbh);

// updates Articles_Tags and Articles_Themes tables if marked as a Main Element
		foreach (stringFormat($row['main'], 'array') as $value) {
			// echoLine($value);
			updateMain($value, $dbh);
		}

	}
} catch(PDOException $e) { echo $e->getMessage(); }

