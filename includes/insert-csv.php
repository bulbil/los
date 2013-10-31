<?php
/////////////////////////////////////////////////////////
//
//									<コ:彡
//
//						LAND OF SUNSHINE 
//						university of michigan digital humanities project
// 						nabil kashyap (nabilk.com)
//
//					 	License: MIT (c) 2013
//						https://github.com/misoproject/dataset/blob/master/LICENSE-MIT 
//						
/////////////////////////////////////////////////////////

// adds all data from the land of sunshine googlespreadsheet (text datapoints (responses)) published as a csv to the appropriate tables
// relies heavily on a number of reusable utility scripts in utilities.php

// a variable to carry the url of the google spreadsheet:
// -- the spreadsheet must be 'published to the web' in google docs for it to be accessible
// -- the the suffix needs to be 'output=csv'
$gcsv = 'https://docs.google.com/spreadsheet/pub?key=0AtVEb6YM9oi8dDE0cEx1eVpqN2pBQkpxVjdpeGZ4WkE&output=csv';

// if you want to test a local copy -- like so ... 
// $gcsv_local = 'http://localhost:8888/los/textdata_short.csv';

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
	'florafauna',
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

function csvToArray($url) {

	global $columns;
	$handle = fopen($url, 'r');
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
		echo_line('<strong>' . $row['title'] . '</strong>');

// if there's no existing article, binds values and executes the Articles table statement
// or article_id is false

		$article_id = return_element_id($row, $dbh);
		$reviewer_id = return_reviewer_id($row['initials'], $dbh);
		$row['timestamp'] = string_format($row['timestamp'], 'timestamp');
		$row['narration_embedded'] = (isset($row['narration_embedded'])) ? string_format($row['narration_embedded'], 'bool') : 0;
		$row['narration_tenseshift'] = (isset($row['narration_tenseshift'])) ? string_format($row['narration_tenseshift'], 'bool') : 0;

		if(!$article_id) {
			$row['date_published'] = string_format($row['date_published'], 'date_csv');
			execute_article($row, $stmt_articles, $rec); 
			$article_id = $dbh->lastInsertId();
			}
		// echo_line($row['author']);
		// echo_line($row['location']);
		// echo_line($row['page_start']);
		// echo_line($row['page_end']);
		// echo_line($row['volume']);
		// echo_line($row['issue']);
		// echo_line(string_format($row['date_published'], 'date_published'));
		// echo_line($type);

// binds values and executes Reviews table statement

		// sets the current article_id as the last updated row, from the Articles table in this case
		// $article_id = return_article_id($row, $dbh->lastInsertId();

		execute_review($article_id, $reviewer_id, $row, $stmt_reviews, $dbh);
		// echo_line($row['initials']);
		// echo_line($reviewer_id);
		// echo_line(string_format($row['timestamp'], 'timestamp'));
		// echo_line($row['summary']);
		// echo_line($row['notes']);
		// echo_line($row['research_notes']);
		// echo_line($row['narration_pov']);
		// echo_line($narration_embedded);
		// echo_line($row['narration_tense']);
		// echo_line($row['narration_tenseshift']);

// binds values and executes Articles_Themes table statement
		execute_themes($article_id, $reviewer_id, $row['themes'], $stmt_articles_themes, $dbh);

// binds values and executes Articles_Tags table statement, adds tags to Tags table if new

		foreach ($row as $key=>$value) {
			switch($key){
				case ('groups') :
				case ('persons') :
				case ('entities') :
				case ('places') :
				case ('activities') :
				case ('florafauna') :
				case ('commodities') :
				case ('events') :
				case ('works') :
				case ('technologies') :
				case ('environments') : execute_tags($value, $key, $article_id, $reviewer_id, $stmt_articles_tags, $dbh);
			}
		}

// updates Articles_Tags and Articles_Themes tables if marked as a Main Element
		foreach (string_format($row['main'], 'array') as $value) {

			$value = string_format($value, 'theme'); 
			update_main($value, $article_id, $reviewer_id, $dbh);
		}

	}
} catch(PDOException $e) { echo $e->getMessage(); }

