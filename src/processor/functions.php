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


/**
 * Useful functions
 *
 * @package Processor
 * @author Josh
 * @since 0.1
 **/

/**
 * Automatically loads the classes that are needed
 **/
function processor_autoload($class)
{
    $filename = preg_replace('/([A-Z])/', '_$1', $class);
    $filename = strtolower(__DIR__ . '/' . substr($filename, 1) . '.php');
    if (file_exists($filename)) require_once $filename;
}

spl_autoload_register('processor_autoload');

require_once __DIR__ . '/constants.php';
require_once __DIR__ . '/misc_classes.php';


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
 * @return array The parsed comments, as per the example provided above
 **/
function parse_doc_comment($comment)
{
    $comment = preg_replace('/^\/\*\*/', '', $comment);
    $comment = preg_replace('/\*\/$/m', '', $comment);
    $comment = preg_replace('/^\s*\**/m', '', $comment);


    // split into lines
    $lines = explode("\n", $comment);

    // process one line at a time
    $output = array();
    $buffer = null;
    $current = null;
    foreach ($lines as $line) {

        $line = preg_replace('/^\s/', '', $line);
        $line = rtrim($line);
        $trimline = ltrim($line);

        if ($current != null and $current != '@summary' and $trimline == '') continue;

        // process special words
        if ($trimline != '' and $trimline[0] == '@') {
            @list($word, $value) = preg_split('/\s+/', $trimline, 2);

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


/**
 * Processes the parsing of an individual tag
 **/
function parse_tag(&$output, $tag, $buffer)
{
    if ($tag == '@summary') {
        $output[$tag] = $buffer;

    } else {
        if (! isset($output[$tag])) {
            $output[$tag] = array();
        }
        $output[$tag][] = $buffer;
    }
}


/**
 * Outputs a status message
 *
 * @param string $message The message to output
 **/
function output_status($message)
{
    if (PHP_SAPI == 'cli') {
        $message = preg_replace('/<a[^>]* href=[\'"](.+?)[\'"][^>]*>(.*?)<\/a>/i', '$2 [LINK: $1]', $message);
        echo strip_tags($message) . "\n";

    } else {
        echo $message . "<br>";
    }
    flush();
}


/**
 * Gets all the filenames in a directory and in the subdirectories
 **/
function get_filenames($base_dir, $directory, array $exclude_dirs)
{
    $exclude_dir = preg_replace('!^/!', '', $directory);
    if (in_array($exclude_dir, $exclude_dirs)) return null;

    $handle = opendir($base_dir . $directory);
    if ($handle === false) return null;

    $files = array();
    while (($file = readdir($handle)) !== false) {
        if ($file[0] == '.') continue;

        if (is_dir($base_dir . $directory . '/'. $file)) {
            // If its a directory, get the files in it
            $files2 = get_filenames($base_dir, $directory . '/'. $file, $exclude_dirs);
            if (is_array($files2)) {
                $files = array_merge($files, $files2);
            }

        } else {
            // Otherwise just add the file to our list
            $files[] = $directory . '/'. $file;
        }
    }

    closedir($handle);

    return $files;
}


/**
 * This will take the provided text, and turn it into HTML
 * If it contains HTML, it will validate it, otherwise it
 * will wrap everything in a PRE
 *
 * This function also removes extra spaces from the beginning of lines
 * but will do so in a manner that indenting is preserved
 **/
function htmlify_text($text)
{
    if ($text == '') return null;

    // if the code contains block level HTML, output it as is
    // todo: come up with a smarter solution
    $has_block_html = preg_match('/<(p|div|pre|table)( .*)?>/i', $text);
    if ($has_block_html) {
        return $text;
    }

    // otherwise, we do clever indenting
    $lines = explode("\n", $text);
    $min_num_spaces = 1000;
    foreach ($lines as $line) {
        if (trim($line) == '') continue;
        $num_spaces = 0;
        for ($i = 0; $i < strlen($line); $i++) {
            if ($line[$i] != ' ') break;
            ++$num_spaces;
        }
        $min_num_spaces = min($min_num_spaces, $num_spaces);
    }

    // remove trailing whitespace
    $text = '';
    $j = 0;
    foreach ($lines as $line) {
        if ($j++ > 0) $text .= "\n";
        $text .= substr($line, $min_num_spaces);
    }

    // remove invalid HTML tags
    $text = str_replace('&', '&amp;', $text);
    $replacer = function(array $matches) {
        return htmlify_check_tag(stripslashes($matches[0]), stripslashes($matches[1]));
    };
    $text = preg_replace_callback('/<\/?([a-z]+)(?>\s|"[^"]*"|\'[^\']*\'|[^\'">])*>/i', $replacer, $text);
    $text = preg_replace('/<([^\/a-z])/i', '&lt;$1', $text);
    $text = preg_replace('/([^"\'a-z])>/i', '$1&gt;', $text);
    $text = str_replace('"', '&quot;', $text);

    // wrap it all in a PRE
    $text = "<pre>\n{$text}</pre>";

    return $text;
}


/**
 * Does processing on a single HTML tag, as provided by a regex in {@link htmlify_text}
 *
 * @param string $full_match The full tag that was found (e.g. '<select name="blah">')
 * @param string $tag_name The name of the tag that was found (e.g. 'select')
 * @return string What the tag should be replaced with
 **/
function htmlify_check_tag($full_match, $tag_name)
    {;
    // valid tags get used as-is
    $valid_tags = array ('b', 'i', 'em', 'strong');
    if (in_array($tag_name, $valid_tags)) {
        return $full_match;
    }

    // everything else gets encoded
    $full_match = str_replace('<', '&lt;', $full_match);
    $full_match = str_replace('>', '&gt;', $full_match);
    return $full_match;
}


/**
 * Processes the javadoc tags for a specific parser item
 **/
function process_javadoc_tags(CodeParserItem $parser_item, $parent)
{
    if ($parent != null) {
        $parent->cascadeTags($parser_item);
    }

    $parser_item->processTags();
}


?>
