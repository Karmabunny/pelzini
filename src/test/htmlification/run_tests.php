<?php
/*
Copyright 2008 Josh Heidenreich

This file is part of Pelzini.

Pelzini is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Pelzini is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Pelzini.  If not, see <http://www.gnu.org/licenses/>.
*/
?>
<style>
body {font-size: small;}
.success {background-color: #1ba63b; padding: 5px; margin-bottom: 5px;}
.failure {background-color: #f99c9c; padding: 5px; margin-bottom: 5px;}
div pre {border: 1px #777 solid;}
</style>


<?php
/**
* Runs a set of tests that check that the {@link htmlify_text()} function, which turns docu-text into HTML makes sence
*
* @since 0.2
* @author Josh
* @package Test suite
**/


require_once '../../processor/functions.php';

$tests = glob('*.txt');

foreach ($tests as $test_filename) {
  $result_filename = str_replace ('.txt', '.htm', $test_filename);
  
  $test = file_get_contents($test_filename);
  $expected_result = file_get_contents($result_filename);
  
  $real_result = htmlify_text($test);
  
  $expected_result = trim($expected_result);
  $real_result = trim($real_result);
  
  if ($real_result == $expected_result) {
    echo "<div class=\"success\">Test '{$test_filename}' was successful.</div>";
    $success++;
    
  } else {
    $real_result = htmlspecialchars ($real_result);
    $expected_result = htmlspecialchars ($expected_result);
    
    echo "<div class=\"failure\">Test '{$test_filename}' failed.<br>";
    echo "Result:<pre>{$real_result}</pre>";
    echo "Expected:<pre>{$expected_result}</pre>";
    echo '</div>';
    $failure++;
  }
}

$success = (int) $success;
$failure = (int) $failure;
echo "<p>{$success} test(s) were successful, {$failure} test(s) failed.</p>";
?>
