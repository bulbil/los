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

$html = "<div class='row'>
		<div class='col-md-10 col-md-offset-1'>
		<a class='btn btn-warning pull-right' id='add-review' href='review-form.php?form=add'>
		<em>add new review</em>
		</a></div></div>";

echo $html;

$table_columns = array('title','volume','issue', 'date_published');

table_start($table_columns,2);

while ($row = $results->fetch(PDO::FETCH_ASSOC)) {

	table_row($row, $table_columns, $row['article_id']);
}

table_end();

include '../html/footer.html';