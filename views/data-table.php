<?php
session_start();
include '../html/header.html';
include '../html/masthead.html';
include '../includes/db.php';
include '../includes/utilities.php';

$html = "<div class='row'>";

$html .= "<div class='dropdown' id='columns-chooser'>
		  <a data-toggle='dropdown' class='btn btn-warning' href='#'>Select Columns <span class='caret'></span></a>";

$html .= "<ul class='dropdown-menu' id='columns-chooser' role='menu' aria-labelledby='dLabel'>
		  </ul>
		  </div>";

$html .= "<table class='table table-striped' id='data-table'>
		  <tbody></tbody>
		  </table>
		  </div>";

echo $html;

include '../html/footer.html';