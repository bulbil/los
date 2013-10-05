<?php
session_start();
if(!isset($_SESSION['username'])) { echo "<a href='../index.php'><em>sorry bro, not logged in ...</em></a>"; die; }
include 'db.php';
include 'utilities.php';
$p = (isset($_GET['p'])) ? $_GET['p'] : '';
$article_id = (isset($_GET['id'])) ? $_GET['id'] : '';
$reviewer2_id = (isset($_GET['rid'])) ? $_GET['rid'] : '';
$if_image = (isset($_GET['img'])) ? $_GET['img'] : 0;
echo return_json($p, $article_id, $_SESSION['reviewer_id'], $reviewer2_id, $if_image);
