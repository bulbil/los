<?php
session_start();
include '../html/header.html';
include '../html/masthead.html';
include '../includes/db.php';
include '../includes/utilities.php';

$html = "<div class='row'>";

$html .= "<div class='form-group' id='columns-chooser'></div>";

$html .= "<table class='table table-striped' id='data-table'>
		  <tbody></tbody>
		  </table>
		  </div>";

echo $html;

include '../html/footer.html';