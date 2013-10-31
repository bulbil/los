<?php
//insert google spreadsheet data into db
session_start();
require '../html/header.html';
include '../html/masthead.html';
include '../includes/db.php';
include '../includes/utilities.php';
if($_GET['p'] == 'image') include '../includes/insert-img-csv.php';
else include '../includes/insert-csv.php';
require '../html/footer.html';