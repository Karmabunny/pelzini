<?php
header ('Content-type: text/plain');

$output = fopen ('strings.php', 'w');
fwrite ($output, "<?php\n");
fwrite ($output, "/**\n");
fwrite ($output, "* This is the language constants file.\n");
fwrite ($output, "* It is generated by {@link lang_conv.php}.\n");
fwrite ($output, "**/\n\n");

$file_lines = file ('english.txt');
$index = 0;
foreach ($file_lines as $line) {
    $line = preg_replace ('/;(.*)$/', '', $line);
    $line = trim ($line);
    if ($line == '') continue;
    
    $parts = preg_split ('/\s+/', $line, 2);
    
    echo "Writing constant {$parts[0]}\n";
    $index++;
    fwrite ($output, "define ({$parts[0]}, {$index});\n");
}
fwrite ($output, "?>\n");

?>
