<?php

$gcsv = 'https://docs.google.com/spreadsheet/pub?key=0AqAqvqKN28wbdHA2R3pHLTBrZHJFbE1kOUtZLV9GSEE&output=csv';
$gcsv_local = 'http://localhost:8888/los/textdata_short.csv';

$gcsv_copy = 'https://docs.google.com/spreadsheet/pub?key=0AqAqvqKN28wbdGN0OFpuVGZFYnRSdFhjd05HYVFncEE&output=csv';
$gcsv_short = 'https://docs.google.com/spreadsheet/pub?key=0AqAqvqKN28wbdEhrSkVqQ3Ezb2p5ZV9UWnFUMGV5dEE&output=csv';

$handle = fopen($gcsv, 'r');

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

while ($row = fgetcsv($handle)) {

	$csv[] = array_combine($columns, $row);
}
fclose($handle);

unset($csv[0]);

$articles = array(
	'title',
	'author',
	'location',
	'page_start',
	'page_end',
	'volume',
	'issue',
	'date_published',
	'type'
	);

$articles_sql = sqlImplode($articles, 'Articles');

$reviews = array(
	'article_id',
	'reviewer_id',
	'timestamp',
	'summary',
	'notes',
	'research_notes',
	'narration_pov',
	'narration_embedded',
	'narration_tense',
	'narration_tenseshift'
	);

$reviews_sql = sqlImplode($reviews, 'Reviews');

$articles_themes = array(
	'article_id',
	'theme_id',
	'reviewer_id'
	);

$articles_themes_sql = sqlImplode($articles_themes, 'Articles_Themes');

$tags = array(
	'category',
	'tag'
	);

$tags_sql = sqlImplode($tags, 'Tags');

$articles_tags = array(
	'article_id',
	'tag_id',
	'reviewer_id'
	);

$articles_tags_sql = sqlImplode($articles_tags, 'Articles_Tags');

try {

$losPDO = db_connect();
$tempPDO = db_connect();

$stmt_articles = $losPDO->prepare($articles_sql);
$stmt_reviews = $losPDO->prepare($reviews_sql);
$stmt_articles_themes = $losPDO->prepare($articles_themes_sql);
$stmt_tags = $losPDO->prepare($tags_sql);
$stmt_articles_tags = $losPDO->prepare($articles_tags_sql);

$i = 1;
	foreach($csv as $row){

		echoLine('<br /><br /><strong>' . $i . '</strong>' );
		$i++;

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

		$article_id = $losPDO->lastInsertId();
		// echoLine($article_id);
		bindValue($article_id, $stmt_reviews, 'article_id');

		$reviewer_id = (strlen($row['initials']) < 4) ? returnID($row['initials'], 'reviewer_id', 'initials', 'Reviewers', $losPDO)
			: 9;
		// echoLine($row['initials']);
		// echoLine($reviewer_id);
		bindValue($reviewer_id, $stmt_reviews, 'reviewer_id');

		if($reviewer_id == 'rec') {updateReconciled($article_id, $losPDO);}

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

		foreach(stringFormat($row['themes'], 'array') as $value) {
			$value = stringFormat($value, 'theme');
			echoLine($value);
			$theme_id = returnID($value, 'theme_id', 'theme', 'Themes', $losPDO);
			// echoLine('theme ' . $theme_id);
			if($theme_id && !ifExists($theme_id, 'Articles_Themes', 'theme_id', $losPDO, $article_id, 'article_id')){			
				bindValue($theme_id, $stmt_articles_themes, 'theme_id');
				bindValue($reviewer_id, $stmt_articles_themes, 'reviewer_id');
				bindValue($article_id, $stmt_articles_themes, 'article_id');
				$stmt_articles_themes->execute();							
				} 
				else { echoLine('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong>' . $value . '</strong> not a theme ... check data');}
			}

		tagArray($row['groups'], 'groups', $article_id, $reviewer_id, $stmt_articles_tags, $losPDO);
		tagArray($row['entities'], 'entities', $article_id, $reviewer_id, $stmt_articles_tags, $losPDO);
		tagArray($row['places'], 'places', $article_id, $reviewer_id, $stmt_articles_tags, $losPDO);
		tagArray($row['activities'], 'activities', $article_id, $reviewer_id, $stmt_articles_tags, $losPDO);
		tagArray($row['flora_fauna'], 'flora_fauna', $article_id, $reviewer_id, $stmt_articles_tags, $losPDO);
		tagArray($row['commodities'], 'commodities', $article_id, $reviewer_id, $stmt_articles_tags, $losPDO);
		tagArray($row['events'], 'events', $article_id, $reviewer_id, $stmt_articles_tags, $losPDO);
		tagArray($row['works'], 'works', $article_id, $reviewer_id, $stmt_articles_tags, $losPDO);
		tagArray($row['technologies'], 'technologies', $article_id, $reviewer_id, $stmt_articles_tags, $losPDO);
		tagArray($row['environments'], 'environments', $article_id, $reviewer_id, $stmt_articles_tags, $losPDO);

		foreach (stringFormat($row['main'], 'array') as $value) {
			// echoLine($value);
			updateMain($value, $losPDO);
		}

	}
} catch(PDOException $e) { echo $e->getMessage(); }

