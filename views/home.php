<?php
session_start();
include '../html/header.html';
include '../includes/utilities.php';
include '../html/masthead.html';
if(isset($_SESSION['login_error'])) unset($_SESSION['login_error']);
require '../html/home.html';
require '../html/footer.html';