<?

$gcsv = 'https://docs.google.com/spreadsheet/pub?key=0AtVEb6YM9oi8dDE0cEx1eVpqN2pBQkpxVjdpeGZ4WkE&output=csv';
$handle = fopen($gcsv, 'r');
$keys = fgetcsv($handle);

while ($line = fgetcsv($handle)) {

	$csv[] = array_combine($keys, $line);
}
fclose($handle);

foreach($csv as $line) {

	foreach($line as $key=>$value) echo $key.': '.$value.' ';
	echo '<br /><br />';
}