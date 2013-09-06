<?php
session_start();
include 'db.php';
include 'utilities.php';

$p = (isset($_GET['p'])) ? $_GET['p'] : '';
$id = (isset($_GET['id'])) ? $_GET['id'] : '';
echo return_json($p, $id, $_SESSION['reviewer_id']);