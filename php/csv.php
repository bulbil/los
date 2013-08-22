<?

$gcsv = 'https://docs.google.com/spreadsheet/pub?key=0AtVEb6YM9oi8dDE0cEx1eVpqN2pBQkpxVjdpeGZ4WkE&output=csv';
$handle = fopen($gcsv, 'r');
$columns = array('timestamp',
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
'narration_tenseshift');

while ($rows = fgetcsv($handle)) {

	$csv[] = array_combine($columns, $rows);
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
	'reviewer_id',
	'timestamp',
	'summary',
	'notes',
	'research_notes',
	'narration_pov',
	// 'narration_embedded',
	'narration_tense',
	// 'narration_tenseshift'
	);

$reviews_sql = sqlImplode($reviews, 'Reviews');

$reviewers = array(
	'article_id',
	'reviewer_id'
	);

$reviewers_sql = sqlImplode($reviewers, 'Article_Reviewers');

$article_themes = array(
	'article_id',
	'theme_id',
	'reviewer_id',
	'if_main'
	);

$article_themes_sql = sqlImplode($article_themes, 'Article_Themes');

$tags = array(
	'category',
	'tag'
	);

$tags_sql = sqlImplode($tags, 'Tags');

$article_tags = array(
	'article_id',
	'tag_id',
	'reviewer_id',
	'if_main'
	);

$article_tags_sql = sqlImplode($article_tags, 'Article_Tags');

$sql = $reviews_sql;
echo $sql;

$stmt = $losPDO->prepare($sql);

foreach($csv as $row){

		foreach($row as $key=>$value) {
	
			switch($key){
					
				// case 'title': echo $key . ' ' . $value . '<br/>'; insertValue($value, $stmt, $key); break;
				// case 'author': echo $key . ' ' . $value . '<br/>'; insertValue($value, $stmt, $key); break;	
				// case 'location': echo $key . ' ' . $value . '<br/>'; insertValue($value, $stmt, $key); break;
				// case 'page_start': echo $key . ' ' . $value . '<br/>'; insertValue($value, $stmt, $key); break;
				// case 'page_end': echo $key . ' ' . $value . '<br/>'; insertValue($value, $stmt, $key); break;
				// case 'volume': echo $key . ' ' . $value . '<br/>'; insertValue($value, $stmt, $key); break;
				// case 'issue': echo $key . ' ' . $value . '<br/>'; insertValue($value, $stmt, $key); break;
				// case 'date_published': echo $key . ' ' . returnDate($value) . '<br/>'; insertValue(returnDate($value), $stmt, $key); break;
				// case 'type': echo $key . ' ' . returnType($value) . '<br/>'; insertValue(returnType($value), $stmt, $key); break;

				case 'initials': $d = returnReviewerID($value, $losPDO); echo $key . ' ' . $d . '<br/>'; insertValue($d, $stmt, 'reviewer_id'); break;
				case 'timestamp': echo $key . ' ' . returnTimestamp($value) . '<br/>'; insertValue(returnTimestamp($value), $stmt, $key); break;
				case 'summary': echo $key . ' ' . $value . '<br/>'; insertValue($value, $stmt, $key); break;	
				case 'notes': echo $key . ' ' . $value . '<br/>'; insertValue($value, $stmt, $key); break;
				case 'research_notes': echo $key . ' ' . $value . '<br/>'; insertValue($value, $stmt, $key); break;
				case 'narration_pov': echo $key . ' ' . $value . '<br/>'; insertValue($value, $stmt, $key); break;
				// case 'narration_embedded': echo $key . ' ' . returnBoolean($value) . '<br/>'; insertValue(returnBoolean($value), $stmt, $key); break;
				case 'narration_tense': echo $key . ' ' . $value . '<br/>'; insertValue($value, $stmt, $key); break;
				// case 'narration_tenseshift': echo $key . ' ' . returnBoolean($value) . '<br/>'; insertValue(returnBoolean($value), $stmt, $key); break;
			}

		}
		$stmt->execute();
	}