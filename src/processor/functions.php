<?php

function parse_doc_comment ($comment) {
	// rip all the comment stuff from the comment
	$comment = preg_replace ('/^\s*|\/?\**\/?/', '', $comment);

	// split into lines
	$lines = explode("\n", $comment);

	// process one line at a time
	$output = array();
	$buffer = null;
	$current = null;
	foreach ($lines as $line) {
		$line = trim ($line);
		if ($line == '') continue;

		// process special words
		if ($line[0] == '@') {
			list ($word, $value) = explode(' ', $line, 2);
		
			// basically the same action for all tags
			switch ($word) {
				case '@param':
				case '@return':
					if ($current != null) {
						parse_tag ($output, $current, $buffer);
						$buffer = null;
					}
					$current = $word;
					$buffer = $value;
					break;
			}

		// non tag - could be part of the summary, or could be a continuation of a tag
		} else {
			if ($current != null) {
				$buffer .= $line;

			} else {
				$current = '@summary';
				$buffer = $line;
			}
		}

	}

	if ($current != null) {
		parse_tag ($output, $current, $buffer);
	}

	return print_r($output, true);
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

?>
