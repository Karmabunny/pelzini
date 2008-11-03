<?php
/*
Copyright 2008 Josh Heidenreich

This file is part of docu.

Docu is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Docu is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with docu.  If not, see <http://www.gnu.org/licenses/>.
*/


$example_config = file_get_contents ('config.example.php');

$example_config = htmlspecialchars ($example_config);
$example_config = preg_replace ('/\/\*(.*?)\*\//s', '<span class="ml-comment">/*$1*/</span>', $example_config);
$example_config = preg_replace ('/(?<!:)\/\/(.*?)\n/m', "<span class=\"sl-comment\">//$1</span>\n", $example_config);
?>

<style>
pre {
  border: 1px #777 solid;
  background-color: #EEE;
  padding: 10px;
  margin: 1em;
}

.ml-comment { color: #33F; }
.sl-comment { color: #888; }
</style>

<h1>Config creation tool</h1>
<p>The example config is:</p>

<pre>
<?= $example_config; ?>
</pre>

<p>To create a config file, copy the contents of this file into a file called 'config.php', and put it into the processor directory.</p>
