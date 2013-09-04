<?php
if(isset($_SESSION)) session_destroy();
header('Location: php/home.php');
return;