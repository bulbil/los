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

// see insert-csv.php for a better explanation of this variable
$gcsv = 'https://docs.google.com/spreadsheet/pub?key=0AqAqvqKN28wbdDFMWTBiaGpBWDEwekxma2RCTXBKd2c&output=csv';

$columns = array(
	'timestamp',
	'initials',
	'article_title',
	'img_volume',
	'img_issue',
	'img_page',
	'img_caption',
	'img_creator',
	'img_engraver',
	'img_type',
	'x',
	'img_rotated',
	'img_placement',
	'groups',
	'persons',
	'entities',
	'places',
	'activities',
	'florafauna',
	'commodities',
	'technologies',
	'environments',
	'themes',
	'main',
	'img_description',
	'img_notes',
	'x',
	'img_research_notes'	
	);

function csvToArray($url) {

	global $columns;
	$handle = fopen($url, 'r');

//	creates an associative array out of each row of the csv -- each cell is the value, each $columns element is the key
	while ($row = fgetcsv($handle)) {

		$csv[] = array_combine($columns, $row);
	}

	fclose($handle);

	unset($csv[0]);

	return $csv;
}

$csv = csvToArray($gcsv);

try {

	$dbh = db_connect();

	$stmt_image_reviews = prepare_pdo_statement($image_reviews, 'Image_Reviews', $dbh);
	$stmt_images_themes = prepare_pdo_statement($images_themes, 'Images_Themes', $dbh);
	$stmt_images_tags = prepare_pdo_statement($images_tags, 'Images_Tags', $dbh);

	// helper counter for debugging
	$i = 1;
	
	// starts looping through each row of the csv
	foreach($csv as $row){

		echo_line('<br /><br /><strong>' . $i . '</strong>' );
		$i++;
		echo_line('<strong>' . $row['img_caption'] . '</strong>');

		$img_id = return_element_id($row, $dbh, true);

		$article_check_array = array('title' => $row['article_title'], 'volume' => $row['img_volume'], 'issue' => $row['img_issue']);

		$article_id = (strtolower($row['article_title']) !== 'freestanding') ? return_element_id($article_check_array, $dbh) : null;
		$row['article_id'] = $article_id;

		$image_columns = $GLOBALS['images'];
		
		if(!$article_id) unset($image_columns[0]);
		$stmt_images = prepare_pdo_statement($image_columns, 'Images', $dbh);

		if($row['article_id']) {

			$sql = "SELECT date_published FROM Articles
					WHERE article_id = $article_id";
			$results = $dbh->query($sql, PDO::FETCH_COLUMN, 0);
			$results = $results->fetchAll();
			if($results) $img_date = $results[0];
		}

		// normalize a few fields 
		$row['img_date'] = (isset($img_date)) ? $img_date : 0;
		$reviewer_id = (isset($row['initials'])) ? return_reviewer_id($row['initials'], $dbh) : '';
		$row['timestamp'] = string_format($row['timestamp'], 'timestamp');
		// $row['img_date'] = string_format($row['img_date'], 'date_csv');
		$row['img_rotated'] = (isset($row['img_rotated'])) ? string_format($row['img_rotated'], 'img_rotated') : 0;
		$row['img_placement'] = preg_replace('/;/',',', $row['img_placement']);
		$row['img_placement'] = preg_replace('/ /','', $row['img_placement']);

		// if no existing image, make a new one 
		if(!$img_id) {
			execute_image($row, $stmt_images);
			$img_id = $dbh->lastInsertId();
		}

		execute_image_review($img_id, $reviewer_id, $row, $stmt_image_reviews, $dbh);

		execute_themes($img_id, $reviewer_id, $row['themes'], $stmt_images_themes, $dbh, true);

		// these tag categories for some reason don't apply to images
		$image_categories = $GLOBALS['categories'];
		unset($image_categories[4]);
		unset($image_categories[10]);

		foreach($image_categories as $category) {
			execute_tags($row[$category], $category, $img_id, $reviewer_id, $stmt_images_tags, $dbh, true);
		}

		foreach (string_format($row['main'], 'array') as $element) {
			if($element){	
				update_main($element, $img_id, $reviewer_id, $dbh, true);
			}
		}
	}
} catch(PDOException $e) { echo $e->getMessage(); }