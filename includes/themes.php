<?php

include 'db.php';
include 'utilities.php';

$gcsv = 'https://docs.google.com/spreadsheet/pub?key=0AhsvAy6KBj1HdHJQSlpKS3NIdjZBTDNxa0YyZFVyS2c&output=csv';
$handle = fopen($gcsv, 'r');

while ($row = fgetcsv($handle)) {

	$themes[] = array_combine(array('main', 'secondary','notes'), $row);
}

fclose($handle);

unset($themes[0]);

$sql = "INSERT INTO Themes(theme, if_secondary) VALUES (:theme, :if_secondary)";

try {
$themesPDO = db_connect();
$stmt = $themesPDO->prepare($sql);

foreach($themes as $theme) { 

$label_main = preg_replace('/\./', '', ucfirst(trim($theme['main'])));
$stmt->bindValue('theme', $label_main);
$stmt->bindValue('if_secondary', false);

echo $label_main . '<br />';
$stmt->execute();

if($theme['secondary']) {

	$secondary = string_format($theme['secondary'],'array');
	foreach($secondary as $value) {
		$value = preg_replace('/\./', '', $value);
		echo $label_main . '--' . trim($value) . '<br />';
		$stmt->bindValue('theme', $label_main . '--' . trim($value));
		$stmt->bindValue('if_secondary', true);
		$stmt->execute();

	}
}
}
} catch (PDOException $e) { $e->getMessage(); } 