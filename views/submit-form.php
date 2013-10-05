<?php
session_start();

if(!isset($_SESSION['username'])) { $_SESSION['login_error'] = '1'; header('Location: home.php'); }

include '../html/header.html';
include '../html/masthead.html';
include '../includes/db.php';
include '../includes/utilities.php';
include '../includes/insert-form.php';

include '../includes/render-submit-form.php';
include '../html/footer.html';