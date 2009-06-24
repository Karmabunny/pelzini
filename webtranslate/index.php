<?php
require_once 'head.php';
?>


<p>These are the current lanagues in the system</p>
<?php
$files = glob ('*.txt');
foreach ($files as $file) {
    $parts = explode ('.', $file);
    array_pop ($parts);
    $file = implode ('.', $parts);
    
    if ($file == 'english') continue;
    
    echo "<p><a href=\"lang.php?name={$file}\">{$file}</a></p>";
}
?>


<?php
require_once 'foot.php';
?>
