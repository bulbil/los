<?php
session_start();
// are you logged in? otherwise can't see
if(!isset($_SESSION['username'])) { $_SESSION['login_error'] = '1'; header('Location: home.php'); }

if(isset($_SESSION['confirm'])) unset($_SESSION['confirm']);
if(isset($_SESSION['db_data'])) unset($_SESSION['db_data']);
if(isset($_SESSION['form_data'])) unset($_SESSION['form_data']);

require '../html/header.html';
include '../html/masthead.html';
include '../includes/db.php';
include '../includes/utilities.php';
include '../html/form.html';
include '../html/form-bib.html';
include '../html/form-tags.html';
include '../html/form-narration.html';
include '../html/form-summary.html';
include '../html/form-image.html';
require '../html/footer.html';