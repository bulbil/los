<?

$gcsv = 'https://docs.google.com/spreadsheet/pub?key=0AtVEb6YM9oi8dDE0cEx1eVpqN2pBQkpxVjdpeGZ4WkE&output=csv';
$gcsv_local = 'http://localhost:8888/los/textdata.csv';

$gcsv_copy = 'https://docs.google.com/spreadsheet/pub?key=0AqAqvqKN28wbdGN0OFpuVGZFYnRSdFhjd05HYVFncEE&output=csv';
$gcsv_short = 'https://docs.google.com/spreadsheet/pub?key=0AqAqvqKN28wbdEhrSkVqQ3Ezb2p5ZV9UWnFUMGV5dEE&output=csv';

$handle = fopen($gcsv_short, 'r');

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
	'author = :author',
	'location = :location',
	'page_start = :page_start',
	'page_end = :page_end',
	'volume = :volume',
	'issue = :issue',
	'date_published = :date_published',
	'type = :type'
	);

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

// $reviewers = array(
// 	'article_id',
// 	'reviewer_id'
// 	);

// $reviewers_sql = sqlImplode($reviewers, 'Articles_Reviewers');

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

$stmt_reviews = $losPDO->prepare($reviews_sql);
// $stmt_reviewers = $losPDO->prepare($reviewers_sql);
$stmt_articles_themes = $losPDO->prepare($articles_themes_sql);
$stmt_tags = $losPDO->prepare($tags_sql);
$stmt_articles_tags = $losPDO->prepare($articles_tags_sql);

echo $tags_sql . '<br /><br />';
echo $reviews_sql . '<br /><br />';
echo $articles_themes_sql . '<br /><br />';
echo $articles_tags_sql . '<br /><br />';

	foreach($csv as $row){

			$reviewer_id = null;

			insertValue($row['title'], 'Articles', 'title', $tempPDO);
			$article_id = $tempPDO->lastInsertId();

			$articles_sql = sqlImplode($articles, 'Articles', 'update', 'article_id', $article_id);
			echo $articles_sql . '<br/><br/>';
			$stmt_articles = $losPDO->prepare($articles_sql);

			echo "A R T Y " . $article_id . '<br/>';
			bindValue($article_id, $stmt_reviews, 'article_id');
			// bindValue($article_id, $stmt_reviewers, 'article_id');

		foreach($row as $key=>$value) {

			switch($key){

				case 'author':
				case 'location':
				case 'page_start':
				case 'page_end':
				case 'volume':
				case 'issue': echo $key . ' ' . $value . '<br/>'; bindValue($value, $stmt_articles, $key); break;
				case 'date_published': echo $key . ' ' . stringFormat($value, $key) . '<br/>'; bindValue(stringFormat($value, $key), $stmt_articles, $key); break;
				case 'type': echo $key . ' ' . stringFormat($value, $key) . '<br/>'; bindValue(stringFormat($value, $key), $stmt_articles, $key); break;

				case 'timestamp': echo $key . ' ' . stringFormat($value, $key) . '<br/>'; bindValue(stringFormat($value, $key), $stmt_reviews, $key); break;
				case 'summary': echo $key . ' ' . $value . '<br/>'; bindValue($value, $stmt_reviews, $key, $tempPDO); break;	
				case 'notes': echo $key . ' ' . $value . '<br/>'; bindValue($value, $stmt_reviews, $key); break;
				case 'research_notes': echo $key . ' ' . $value . '<br/>'; bindValue($value, $stmt_reviews, $key); break;
				case 'narration_pov': echo $key . ' ' . stringFormat($value) . '<br/>'; bindValue($value, $stmt_reviews, $key); break;
				case 'narration_tense': echo $key . ' ' . stringFormat($value) . '<br/>'; bindValue($value, $stmt_reviews, $key); break;
				case 'narration_embedded':
				case 'narration_tenseshift': if(!stringFormat($value, 'bool')) {echo $key . ' '. stringFormat($value, 'bool') . '<br/>';} bindValue(stringFormat($value, 'bool'), $stmt_reviews, $key); break;

				case 'initials': 

					$reviewer_id = returnID($value, 'reviewer_id', $key, 'Reviewers', $tempPDO); 
					echo 'reviewer id' . ' ' . $reviewer_id . '<br/>'; 
					bindValue($reviewer_id, $stmt_reviews, 'reviewer_id');
					// bindValue($reviewer_id, $stmt_reviewers, 'reviewer_id'); 
					break;

				case 'groups':  
				case 'persons': 
				case 'entities':
				case 'places':  
				case 'activities':
				case 'flora_fauna':
				case 'commodities':
				case 'events':
				case 'works':
				case 'technologies':
				case 'environments':  

						echo "tag " . $article_id . '<br/>';

					foreach(stringFormat($value, 'array') as $tag) {

						insertTag($tag, $stmt_tags, $key, $tempPDO);
						$id = returnID($tag, 'tag_id', 'tag', 'Tags', $tempPDO, $key, 'category');
						// echo $id . '<br />';
						
						if($id && !ifExists($id, 'Articles_Tags' , 'tag_id', $tempPDO)){
							bindValue($id, $stmt_articles_tags, 'tag_id');
							bindValue($reviewer_id, $stmt_articles_tags, 'reviewer_id');
							bindValue($article_id, $stmt_articles_tags, 'article_id'); 
							$stmt_articles_tags->execute();
							}
						}
					break;

				case 'themes': 
					echo "theme " . $article_id . '<br/>';

					foreach(stringFormat($value, 'array') as $key=>$theme) {
						$id = returnID($theme, 'theme_id', 'theme', 'Themes', $tempPDO);
						// echo 'id found ' . $id;
						if($id) {
							bindValue($id, $stmt_articles_themes, 'theme_id');
							bindValue($reviewer_id, $stmt_articles_themes, 'reviewer_id');
							bindValue($article_id, $stmt_articles_themes, 'article_id');
							$stmt_articles_themes->execute();							
							}
						}
					break;

				case 'main':

					foreach(stringFormat($value, 'array') as $key=>$main) {

							updateMain($main, $tempPDO);							

						}
					break; 
					}
				}
			
		echo '<br />';
		$stmt_articles->execute();
		// $stmt_reviewers->execute();
		$stmt_reviews->execute();
		}

} catch (PDOException $e) { echo $e->getMessage();}

$losPDO = null;
$tempPDO = null;