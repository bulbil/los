<?php
session_start();
// are you logged in? otherwise can't see
if(!isset($_SESSION['username'])) { $_SESSION['login_error'] = '1'; header('Location: home.php'); }

include '../html/header.html';
include '../html/masthead.html';
include '../includes/db.php';
include '../includes/utilities.php';

unset_session_vars();

$reviewer_id = $_SESSION['reviewer_id'];
$dbh = db_connect();

// starts the tables
include '../html/reviewer-table.html';

// starts the articles table
$sql = "SELECT timestamp, Articles.article_id, title, issue, volume, date_published, reconciled 
		FROM Articles JOIN Reviews ON Articles.article_id = Reviews.article_id 
		WHERE reviewer_id = $reviewer_id 
		ORDER BY UNIX_TIMESTAMP(timestamp) DESC";

$results = $dbh->query($sql);

$table_columns = array('title','volume','issue', 'date_published');

$html = table_start($table_columns, 'articles', 1);

while ($row = $results->fetch(PDO::FETCH_ASSOC)) {
	$html .= table_row($row, $table_columns, $row['article_id']);
}

$html .= table_end();

// starts the images table
$html .= "<div class='tab-pane fade' id='images'>";

$table_columns = array('img_caption', 'freestanding', 'img_volume','img_issue', 'img_date');

$sql = "SELECT timestamp, Images.img_id, img_caption, Images.article_id, img_issue, img_volume, img_date 
		FROM Images JOIN Image_Reviews ON Images.img_id = Image_Reviews.img_id 
		WHERE reviewer_id = $reviewer_id 
		ORDER BY UNIX_TIMESTAMP(timestamp) DESC";

$results = $dbh->query($sql);

$html .= table_start($table_columns, 'images', 1);

while ($row = $results->fetch(PDO::FETCH_ASSOC)) {
	$html .= table_row($row, $table_columns, $row['img_id'], 'image');
}

$html .= table_end();

$html .= "</div>
		  </div>";

echo $html;	
$dbh = null;
include '../html/footer.html';