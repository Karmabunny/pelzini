<?php
require_once '../i18n.php';


$_GET['lang'] = trim($_GET['lang']);

if ($_GET['lang'] != 'english') {
	$res = loadLanguage ($_GET['lang']);
}

if (! $res) {
	echo "<p>Unknown language specified. Please specify a valid language.</p>";
	echo '<form action="update_lang.php" method="get">';
	echo '<p>Language: <input type="text" name="lang" value=""></p>';
	echo '<p><input type="submit" value="Process langauge"></p>';
	echo '</form>';
	exit;
}


header ('Content-type: text/plain');

$lang_lines = file ($_GET['lang'] . '.txt');
foreach ($lang_lines as $line) {
	if (strncmp($line, ';;', 2) == 0) {
		echo $line;
	}
}


$eng_lines = file ('english.txt');
$index = 0;
foreach ($eng_lines as $orig_line) {
	$orig_line = trim ($orig_line);
	if (strncmp($orig_line, ';;', 2) == 0) continue;
	
	$line = preg_replace ('/;(.*)$/', '', $orig_line);
    $line = trim ($line);
    
    if ($line == '') {
    	echo $orig_line . "\n";
    	continue;
   	}
    
    $parts = preg_split ('/\s+/', $line, 2);
    
    $translation = getOriginalString(@constant($parts[0]));
    if (! $translation) {
    	$translation = str_pad($parts[1], 85, ' ', STR_PAD_RIGHT) . '   ; needs translating';
    }
    
    echo str_pad($parts[0], 40, ' ', STR_PAD_RIGHT), $translation, "\n";
}

?>
