<?php
session_start();
include '../html/header.html';
include '../html/masthead.html';
include '../includes/utilities.php';

$table_columns = array('date','title', 'author', 'main');

table_start($table_columns, 'data-table');
table_end();

include '../html/footer.html';