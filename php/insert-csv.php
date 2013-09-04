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

	$handle = fopen($url, 'r');

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

$csv = csvToArray($gcsv);

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
		echoLine('<strong>' . $row['title'] . '</strong>');

// binds values and executes to the Articles table statement
		editRowArticle($row, $stmt_articles);
		
		// echoLine($row['author']);
		// echoLine($row['location']);
		// echoLine($row['page_start']);
		// echoLine($row['page_end']);
		// echoLine($row['volume']);
		// echoLine($row['issue']);
		// echoLine(stringFormat($row['date_published'], 'date_published'));
		// echoLine($type);

// binds values and executes Reviews table statement

		// sets the current article_id as the last updated row, from the Articles table in this case
		$article_id = $dbh->lastInsertId();
		$reviewer_id = returnReviewerID($row['initials'], $article_id, $dbh);
		// echoLine($article_id);

		editRowReview($article_id, $reviewer_id, $row, $stmt_reviews, $dbh);

		// echoLine($row['initials']);
		// echoLine($reviewer_id);
		// echoLine(stringFormat($row['timestamp'], 'timestamp'));
		// echoLine($row['summary']);
		// echoLine($row['notes']);
		// echoLine($row['research_notes']);
		// echoLine($row['narration_pov']);
		// echoLine($narration_embedded);
		// echoLine($row['narration_tense']);
		// echoLine($row['narration_tenseshift']);

// binds values and executes Articles_Themes table statement
		editThemes($article_id, $reviewer_id, $row['themes'], $stmt_articles_themes, $dbh);

// binds values and executes Articles_Tags table statement, adds tags to Tags table if new

		foreach ($row as $key=>$value) {
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
				// tagArray($row['groups'], 'groups', $article_id, $reviewer_id, $stmt_articles_tags, $dbh);
				// tagArray($row['entities'], 'entities', $article_id, $reviewer_id, $stmt_articles_tags, $dbh);
				// tagArray($row['places'], 'places', $article_id, $reviewer_id, $stmt_articles_tags, $dbh);
				// tagArray($row['activities'], 'activities', $article_id, $reviewer_id, $stmt_articles_tags, $dbh);
				// tagArray($row['flora_fauna'], 'flora_fauna', $article_id, $reviewer_id, $stmt_articles_tags, $dbh);
				// tagArray($row['commodities'], 'commodities', $article_id, $reviewer_id, $stmt_articles_tags, $dbh);
				// tagArray($row['events'], 'events', $article_id, $reviewer_id, $stmt_articles_tags, $dbh);
				// tagArray($row['works'], 'works', $article_id, $reviewer_id, $stmt_articles_tags, $dbh);
				// tagArray($row['technologies'], 'technologies', $article_id, $reviewer_id, $stmt_articles_tags, $dbh);
				// tagArray($row['environments'], 'environments', $article_id, $reviewer_id, $stmt_articles_tags, $dbh);
			}
		}

// updates Articles_Tags and Articles_Themes tables if marked as a Main Element
		foreach (stringFormat($row['main'], 'array') as $value) {
			// echoLine($value);
			updateMain($value, $dbh);
		}

	}
} catch(PDOException $e) { echo $e->getMessage(); }

