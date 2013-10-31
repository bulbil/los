<?php
session_start();

if(!$_GET['p'] == 'test_table' && !isset($_SESSION['username'])) { echo "<a href='../index.php'><em>sorry bro, not logged in ...</em></a>"; die; }
include 'db.php';
include 'utilities.php';

$p = (isset($_GET['p'])) ? $_GET['p'] : '';
$id = (isset($_GET['id'])) ? $_GET['id'] : '';
$reviewer2_id = (isset($_GET['rid'])) ? $_GET['rid'] : '';
$if_image = (isset($_GET['img'])) ? $_GET['img'] : 0;

if($_GET['p'] == 'data_table') echo return_json($p, $id);
else echo return_json($p, $id, $_SESSION['reviewer_id'], $reviewer2_id, $if_image);

// for outputting json things

function return_json($param, $id = '', $reviewer1_id = '', $reviewer2_id = '', $if_image = false) {

	function query($sql, $num = false) {
		$dbh = db_connect();
		$results = $dbh->query($sql);

		if($num) while($row = $results->fetch(PDO::FETCH_NUM)) $results_array[] = $row;
		else while($row = $results->fetch(PDO::FETCH_ASSOC)) $results_array[] = $row;
		
		$json = (isset($results_array)) ? json_encode($results_array) : "<em>sorry bro, no results ...</em>";
		$dbh = null;
		return $json;
	}

	switch ($param){
		// spits out reviewer info
		case('reviewer'):
			$id = (!$reviewer2_id) ? $reviewer1_id : $reviewer2_id;
			$sql = "SELECT initials FROM Reviewers WHERE reviewer_id = $id";
			return query($sql);

		// all the the articles reviewed for a reviewer_id
		case('reviewed'):

			$sql = "SELECT timestamp, Articles.article_id, title, issue, volume, date_published, reconciled 
					FROM Articles JOIN Reviews ON Articles.article_id = Reviews.article_id 
					WHERE reviewer_id = $reviewer1_id 
					ORDER BY UNIX_TIMESTAMP(timestamp) DESC";
			return query($sql);

		// the last article reviewed for a reviewer_id
		case('last'): 

			$sql = "SELECT timestamp, Articles.article_id, title, issue, volume, date_published, reconciled 
					FROM Articles JOIN Reviews ON Articles.article_id = Reviews.article_id 
					WHERE reviewer_id = $reviewer1_id 
					ORDER BY UNIX_TIMESTAMP(timestamp) DESC LIMIT 1";
			return query($sql);

		case('img_article'):

			$sql = "SELECT article_id FROM Images WHERE img_id = $id";
			return query($sql);

		// 	all the data for a record from the Articles or Images table for a particular id
		case('element'):

			$table = (!$if_image) ? 'Articles' : 'Images';
			$table_id = (!$if_image) ? 'article_id' : 'img_id';
			$sql = "SELECT * FROM $table 
					WHERE $table_id = $id";
			return query($sql);

		// 	all the data for a record from the Reviews table for a particular article id and reviewer id
		case('review'):

			$table = (!$if_image) ? 'Reviews' : 'Image_Reviews';
			$table_id = (!$if_image) ? 'article_id' : 'img_id';

			$reviewer_id = (!$reviewer2_id) ? $reviewer1_id : $reviewer2_id;
			$sql = "SELECT * FROM $table WHERE ($table_id, reviewer_id) = ($id, $reviewer_id)";
			return query($sql);

		// 	all the themes for a review from the Articles_Themes table for a particular article id / reviewer_id
		case('themes'):

			$table = (!$if_image) ? 'Articles_Themes' : 'Images_Themes';
			$table_id = (!$if_image) ? 'article_id' : 'img_id';

			$reviewer_id = (!$reviewer2_id) ? $reviewer1_id : $reviewer2_id;
			$sql = 	"SELECT theme, if_main FROM Themes 
					JOIN $table ON $table.theme_id = Themes.theme_id
					WHERE ($table.$table_id, $table.reviewer_id) = ($id, $reviewer_id) ORDER BY theme";
			return query($sql);

		// 	all the tags for a review from the Articles_Tags table for a particular article id / reviewer_id
		case('tags'):

			$table = (!$if_image) ? 'Articles_Tags' : 'Images_Tags';
			$table_id = (!$if_image) ? 'article_id' : 'img_id';

			$reviewer_id = (!$reviewer2_id) ? $reviewer1_id : $reviewer2_id;
			$sql = "SELECT category, tag, if_main FROM Tags 
					JOIN $table ON Tags.tag_id = $table.tag_id
					WHERE ($table.$table_id, $table.reviewer_id) = ($id, $reviewer_id) ORDER BY tag";
			return query($sql);

		// 	all the themes
		case('themes_list'):

			$sql = "SELECT theme FROM Themes ORDER BY theme";
			return query($sql);	

		// 	all the Articles table data
		case('dump_articles'):

			$sql_columns = implode(',', $articles);
			$sql = "SELECT $sql_columns FROM Articles";
			return query($sql);	

		// 	all the Articles table data
		case('dump_reviews'):

			$sql_columns = implode(',', $reviews);
			$sql = "SELECT $sql_columns FROM Reviews";
			return query($sql);	

		// 	all the Articles_Themes table data
		case('dump_themes'):

			$sql = "SELECT article_id, reviewer_id, theme FROM Themes 
					JOIN Articles_Themes ON Themes.theme_id = Articles_Themes.theme_id";
			return query($sql);	

		// 	all the Articles_Tags table data
		case('dump_tags'):
			$sql = "SELECT category, tag FROM Tags ORDER BY tag";
			$sql_alt = "SELECT category, article_id, reviewer_id, category, tag 
					FROM Tags JOIN Articles_Tags 
					ON Tags.tag_id = Articles_Tags.tag_id";
			return query($sql);

		case('places'):

			if (!$id) "SELECT tag FROM Tags WHERE category = 'places' ORDER BY tag";
			else { 

				$sql = ($id == '2') ? 
						"SELECT tag, COUNT(*) as count FROM Tags 
						JOIN Articles_Tags ON Articles_Tags.tag_id = Tags.tag_id 
						WHERE category = 'places' GROUP BY tag"
					: 	"SELECT tag FROM Tags 
						JOIN Articles_Tags ON Articles_Tags.tag_id = Tags.tag_id 
						WHERE category = 'places' ORDER BY tag";
			}								
			return query($sql);

		case('locations'):

				$sql = ($id == '1') ? 
						"SELECT location, COUNT(*) as count FROM Articles GROUP BY location"
					: 	"SELECT location FROM Articles GROUP BY location";

			return query($sql);

		case('data_table'):

			$table = ($id == '') ? 'Articles' : 'Images';
			$sql = "SELECT * FROM $table";			

			$dbh = db_connect();
			$results = $dbh->query($sql);

			while($row = $results->fetch(PDO::FETCH_NUM)) {

				$results_array[] = get_all_rows($row, $dbh, $id);
			}

			$json = (isset($results_array)) ? json_encode($results_array) : "<em>sorry bro, no results ...</em>";
			$dbh = null;
			$json = '{ "aaData" : ' . $json . '}';
			return $json;
	}
}


function get_all_rows($row, $dbh, $id) {

	$element = ($id == '') ? 'article' : 'image';
	$id_column = ($id == '') ? 'article_id' : 'img_id';
	$tags_table = ($id == '') ? 'Articles_Tags' : 'Images_Tags';
	$themes_table = ($id == '') ? 'Articles_Themes' : 'Images_Themes';
	$tags_table = ($id == '') ? 'Articles_Tags' : 'Images_Tags';

	$results_array[$element] = $row;
	$element_id = $row[0];

	$sql_tags = "SELECT GROUP_CONCAT(tag ORDER BY tag SEPARATOR '; ') as tags FROM $tags_table 
				JOIN Tags ON $tags_table.tag_id = Tags.tag_id 
				WHERE $id_column = $element_id
				AND $tags_table.if_main = 1";

	$sql_themes = "SELECT GROUP_CONCAT(theme ORDER BY theme SEPARATOR '; ') as themes FROM $themes_table
				JOIN Themes ON $themes_table.theme_id = Themes.theme_id
				WHERE $id_column = $element_id
				AND $themes_table.if_main = 1";

	$sql_reviews = ($id == '') ? "SELECT narration_pov, narration_embedded, narration_tense, narration_tense
					FROM Reviews JOIN Articles ON Articles.article_id = Reviews.article_id
					WHERE Articles.article_id = $element_id"
				: 0;

	if($sql_reviews != 0) { 

		$results_reviews = $dbh->query($sql_reviews);
		$results_array['review'] = $results_reviews ->fetch(PDO::FETCH_NUM);
	}

	$results_tags = $dbh->query($sql_tags);

	$results_array['tags'] = $results_tags ->fetch(PDO::FETCH_NUM);

	$results_themes = $dbh->query($sql_themes);
	$results_array['themes'] = $results_themes ->fetch(PDO::FETCH_NUM);

	return $results_array;
}			
