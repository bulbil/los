<?php
session_start();

if(isset($_SESSION['confirm'])) unset($_SESSION['confirm']);
if(isset($_SESSION['db_data'])) unset($_SESSION['db_data']);
if(isset($_SESSION['form_data'])) unset($_SESSION['form_data']);

require '../html/header.html';
include '../html/masthead.html';
include '../includes/db.php';
include '../includes/utilities.php';
include '../html/form.html';
include '../html/form-bib.html';
include '../html/rec-form-tags.html';
include '../html/rec-form-narration.html';
include '../html/rec-form-summary.html';
require '../html/footer.html';