<?php
session_start();
include '../html/header.html';
include '../html/masthead.html';
include '../includes/db.php';
include '../includes/utilities.php';

$reviewer_id = $_SESSION['reviewer_id'];
$dbh = db_connect();
$sql = "SELECT timestamp, Articles.article_id, title, issue, volume, date_published, reconciled 
		FROM Articles JOIN Reviews ON Articles.article_id = Reviews.article_id 
		WHERE reviewer_id = $reviewer_id 
		ORDER BY UNIX_TIMESTAMP(timestamp) DESC";

$results = $dbh->query($sql);

$html = "<div class='row'><div class='col-md-2 col-md-offset-1'><a href='add-review.php'><h4 style='color: #777'><em>add new review</em></h4></a></div></div>";
echo $html;

$table_columns = array('timestamp', 'id', 'title', 'issue', 'volume', 'date', 'reconciled');

table_start($table_columns);

while ($row = $results->fetch(PDO::FETCH_ASSOC)) {

	table_row($row, $row['article_id']);
}

table_end();

include '../html/footer.html';