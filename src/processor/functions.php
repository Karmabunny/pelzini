<?php

/**
* Parses a DocBlock comment tag
* Accepts the raw comment text of the comment straight from the file, including all the stars in the middle
* Returns an array of tags. Each paremeter will contain an array of the tags that existed, one for each tag
* The summary is returned in a 'summary' tag.
*
* The output for a function with two param tags, a return tag and a summary will be something like the following:
* array {
*   ['@summary'] = '...',
*   ['@param'] {
*     [0] = '...',
*     [1] = '...'
*   },
*   ['@return'] {
*     [0] = '...',
*   }
* }
*
* @param string $comment The raw comment text
* @return The parsed comments, as per the example provided above
**/
function parse_doc_comment ($comment) {
	// rip all the comment stuff from the comment
	$comment = preg_replace('/^\s*|\/?\**\/?/', '', $comment);
  
	// split into lines
	$lines = explode("\n", $comment);

	// process one line at a time
	$output = array();
	$buffer = null;
	$current = null;
	foreach ($lines as $line) {
	  $line = preg_replace('/^\s/', '', $line);
		$line = rtrim ($line);
		if ($line == '') continue;

		// process special words
		if ($line[0] == '@') {
			list ($word, $value) = explode(' ', $line, 2);
		
			// tags
			if ($current != null) {
				parse_tag ($output, $current, $buffer);
				$buffer = null;
			}
			$current = $word;
			$buffer = $value;

		// non tag - could be part of the summary, or could be a continuation of a tag
		} else {
			if ($current != null) {
				$buffer .= "\n" . $line;

			} else {
				$current = '@summary';
				$buffer = $line;
			}
		}

	}

	if ($current != null) {
		parse_tag ($output, $current, $buffer);
	}

	return $output;
}

// Does the grunt work of the processing
function parse_tag (&$output, $tag, $buffer) {
	if ($tag == '@summary') {
		$output[$tag] = $buffer;
	} else {
		if (! isset($output[$tag])) {
			$output[$tag] = array();
		}
		$output[$tag][] = $buffer;
	}
}


function output_status($message) {
	echo $message . "<br>";
}

function get_filenames ($directory) {
	global $base_dir;

	$handle = opendir($base_dir . $directory);

	$files = array();
	while (($file = readdir($handle)) !== false) {
		if ($file[0] == '.') continue;
		if (is_dir($base_dir . $directory . $file)) {
			$files2 = get_filenames($directory . $file . '/');
			$files = array_merge($files, $files2);
		} else {
			$files[] = $directory . $file;
		}
	}

	closedir($handle);

	return $files;
}

?>
