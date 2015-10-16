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
 * Various useful functions
 *
 * @package Viewer
 * @author Josh Heidenreich
 * @since 0.1
 * @tag i18n-done
 **/


/**
 * Fixes all magically quoted strings in the given array or string
 *
 * @param mixed &$item The string or array in which to fix magic quotes
 * @return mixed The resultant string or array
 */
function fix_magic_quotes(&$item)
{
    if (is_array($item)) {
        // if a key is magically quoted, it needs to be modified - do key modifications after the loop is done,
        // so that the same data does not get fixed twice
        $key_replacements = array ();
        foreach ($item as $key => $val) {
            $new_key = stripslashes($key);
            if ($new_key != $key) $key_replacements[$key] = $new_key;
            $item[$key] = fix_magic_quotes ($val);
        }

        foreach ($key_replacements as $old_key => $new_key) {
            $item[$new_key] = $item[$old_key];
            unset ($item[$old_key]);
        }

    } else {
        $item = stripslashes($item);
    }

    return $item;
}


/**
 * Determines the link for a specified name (might be a class, an interface or a function)
 *
 * @param string $name The name to check
 * @return string A piece of HTML usable to represent the object, as a link if possible
 **/
function get_object_link($name)
{
    global $project;
    $sql_name = db_escape($name);

    // check classes
    $q = "SELECT id FROM classes WHERE name = '{$sql_name}' AND projectid = {$project['id']}";
    $res = db_query($q);
    if (db_num_rows($res) != 0) {
        return get_class_link($name);
    }

    // check interfaces
    $q = "SELECT id FROM interfaces WHERE name = '{$sql_name}' AND projectid = {$project['id']}";
    $res = db_query($q);
    if (db_num_rows($res) != 0) {
        return get_interface_link($name);
    }

    // check functions
    $q = "SELECT id FROM functions WHERE name = '{$sql_name}' AND classid IS NULL AND interfaceid IS NULL AND projectid = {$project['id']}";
    $res = db_query($q);
    if (db_num_rows($res) != 0) {
        return '<a href="function?name=' . urlencode($name) . '">' . htmlspecialchars($name) . '</a>';
    }

    return $name;
}


/**
 * Echos a list of all of the authors of a specifc item
 **/
function show_authors($link_id, $link_type)
{
    global $project;

    $q = "SELECT name, email, description FROM item_authors WHERE linkid = {$link_id} AND linktype = {$link_type} AND projectid = {$project['id']}";
    $res = db_query($q);

    if (db_num_rows($res) > 0) {
        echo '<h3>', str(STR_AUTHORS), '</h3>';

        echo '<ul>';
        while ($row = db_fetch_assoc ($res)) {
            $row['name'] = htmlspecialchars($row['name']);
            $row['email'] = htmlspecialchars($row['email']);

            echo "<li><a href=\"author?name={$row['name']}\">{$row['name']}</a>";

            if ($row['email']) {
                echo "<br><a href=\"mailto:{$row['email']}\">{$row['email']}</a>";
            }

            if ($row['description']) {
                echo "<br><small>{$row['description']}</small>";
            }

            echo '</li>';
        }
        echo '</ul>';
    }
}


/**
 * Shows the tables used by a specific file, function or class
 **/
function show_tables($link_id, $link_type)
{
    global $project;

    $q = "SELECT name, action, description FROM item_tables WHERE linkid = {$link_id} AND linktype = {$link_type} AND projectid = {$project['id']}";
    $res = db_query($q);

    if (db_num_rows($res) > 0) {
        echo '<h3>', str(STR_TABLES), '</h3>';

        echo '<ul>';
        while ($row = db_fetch_assoc ($res)) {
            $name_url = urlencode($row['name']);
            $row['name'] = htmlspecialchars($row['name']);

            echo "<li><i>{$row['action']}</i> <a href=\"table?name={$name_url}\">{$row['name']}</a>";

            if ($row['description']) {
                echo "<br><small>{$row['description']}</small>";
            }

            echo '</li>';
        }
        echo '</ul>';
    }
}


/**
 * Shows the 'see also' things for a specific file, function or class
 **/
function show_see_also($link_id, $link_type)
{
    global $project;

    $q = "SELECT name FROM item_see WHERE linkid = {$link_id} AND linktype = {$link_type} AND projectid = {$project['id']}";
    $res = db_query($q);

    if (db_num_rows($res) > 0) {
        echo '<h3>', str(STR_SEE_ALSO), '</h3>';

        echo '<ul>';
        while ($row = db_fetch_assoc ($res)) {
            echo '<li>', process_inline_link(array(1 => $row['name'])), '</li>';
        }
        echo '</ul>';
    }
}


/**
 * Echos a list of all of the authors of a specifc item
 **/
function show_tags($link_id, $link_type)
{
    global $project;

    $q = "SELECT name FROM item_info_tags WHERE linkid = {$link_id} AND linktype = {$link_type} AND projectid = {$project['id']}";
    $res = db_query($q);

    if (db_num_rows($res) > 0) {
        echo '<p class="tags">', str(STR_TAGS);

        while ($row = db_fetch_assoc ($res)) {
            $row['name'] = htmlspecialchars($row['name']);

            echo " &nbsp; <a href=\"tag?name={$row['name']}\">{$row['name']}</a>";
        }
    }
}


/**
 * Gets HTML for a version, based on the version id
 *
 * @table select versions Will only select once, results are stored in a static array
 **/
function get_since_version($version_id)
{
    global $project;

    $version_id = (int) $version_id;

    $q = "SELECT name FROM versions WHERE id = {$version_id} AND projectid = {$project['id']}";
    $res = db_query($q);
    $row = db_fetch_assoc($res);

    return $row['name'];
}


/**
 * Processes inline tags within text
 *
 * @param string $text the input text
 * @return string The output, with inline text replaced
 **/
function process_inline($text)
{
    $callback = 'process_inline_link';
    $text = preg_replace_callback('/{@link ([^}]*?)}/i', $callback, $text);
    $text = preg_replace_callback('/{@see ([^}]*?)}/i', $callback, $text);
    return $text;
}


/**
 * Replaces the content of a @link or @see tag with its actual link.
 * The content is defines as the part after @link or @see, up to the closing curly bracket
 *
 * @param array $matches Matches produced by a preg_* function
 * @return string HTML for the link to the item, or plain text if no link could be found
 **/
function process_inline_link(array $matches)
{
    global $project;

    $original_text = $matches[1];

    @list($text, $link_text) = explode(' ', $original_text, 2);
    if ($link_text == '') $link_text = $text;

    $text = trim($text);
    $text_sql = db_quote($text);

    if (preg_match('/^(?:https?|ftp|mailto|telnet|ssh|rsync):/', $text)) {
        // It's a URL
        return "<a href=\"{$text}\">{$link_text}</a>";

    } else if (strpos($text, '::') !== false) {
        // It's a class member
        list ($class, $member) = explode('::', $text, 2);

        $class_sql = db_quote($class);
        $q = "SELECT id, name FROM classes WHERE name LIKE {$class_sql} AND projectid = {$project['id']}";
        $res = db_query($q);
        if ($row = db_fetch_assoc($res)) {
            $class_id = $row['id'];
            $class_name = $row['name'];

            if (substr($member, -2) == '()') {
                $member = trim(substr($member, 0, -2));
            }
            $text_sql = db_quote($member);

            // member functions
            $q = "SELECT id, name FROM functions WHERE name LIKE {$text_sql} AND classid = {$class_id}";
            $res = db_query($q);
            if ($row = db_fetch_assoc($res)) {
                return get_function_link($class_name, $row['name'], $link_text);
            }

            // member variables
            $q = "SELECT id, name FROM variables WHERE name LIKE {$text_sql} AND classid = {$class_id}";
            $res = db_query($q);
            if ($row = db_fetch_assoc($res)) {
                return get_class_link($class, null, $link_text);
            }

            return $link_text;
        }
    }

    // Look for classes
    $q = "SELECT id, name FROM classes WHERE name LIKE {$text_sql} AND projectid = {$project['id']}";
    $res = db_query($q);
    if ($row = db_fetch_assoc($res)) {
        return get_class_link($row['name'], null, $link_text);
    }

    // Look for files
    $file = $text;
    if ($file[0] != '/') $file = '/' . $file;
    $file_sql = db_quote($file);
    $q = "SELECT id, name FROM files WHERE name LIKE {$file_sql} AND projectid = {$project['id']}";
    $res = db_query($q);
    if ($row = db_fetch_assoc($res)) {
        return "<a href=\"file?id={$row['id']}\">{$link_text}</a>";
    }

    // Look for constants
    $q = "SELECT id, name, fileid FROM constants WHERE name LIKE {$text_sql} AND projectid = {$project['id']}";
    $res = db_query($q);
    if ($row = db_fetch_assoc($res)) {
        return "<a href=\"file?id={$row['fileid']}#constants\">{$link_text}</a>";
    }

    if (substr($text, -2) == '()') {
        $text = trim(substr($text, 0, -2));
        $text_sql = db_quote($text);
    }

    // Look for functions
    $q = "SELECT id, name FROM functions WHERE name LIKE {$text_sql} AND classid IS NULL AND interfaceid IS NULL AND projectid = {$project['id']}";
    $res = db_query($q);
    if ($row = db_fetch_assoc($res)) {
        return get_function_link(null, $row['name'], $link_text);
    }

    // Look for documents
    // This is very last, and is done against the original full text (you cannot define an alternate name for the link of a document)
    $orig_text = db_quote($original_text);
    $q = "SELECT id, name FROM documents WHERE name LIKE {$orig_text}";
    $res = db_query($q);
    if ($row = db_fetch_assoc($res)) {
        $row['name'] = urlencode($row['name']);
        return "<a href=\"document?name={$row['name']}\">{$original_text}</a>";
    }

    return $original_text;
}


/**
 * Replaces an inline @link or @see with the plain-text version of that @link or @see.
 * This is used in places where excessive links are overkill.
 *
 * @param string $text the input text
 * @return string The output, with inline text replaced
 **/
function delink_inline($text)
{
    $callback = 'process_inline_delink';
    $text = preg_replace_callback('/{@link ([^}]*?)}/i', $callback, $text);
    $text = preg_replace_callback('/{@see ([^}]*?)}/i', $callback, $text);
    return $text;
}


/**
 * Replaces the content of a @link or @see tag with the plain text version of the link
 * The content is defines as the part after @link or @see, up to the closing curly bracket
 *
 * @param array $matches Matches produced by a preg_* function
 * @return string The plain text version of a link
 **/
function process_inline_delink(array $matches)
{
    $original_text = $matches[1];
    $link = explode(' ', $original_text, 2);

    return isset($link[1]) ? $link[1] : $link[0];
}


function show_function_usage($function_id)
{
    $q = "SELECT functions.name, functions.static, GROUP_CONCAT(returns.type SEPARATOR '|') AS returntypes,
      classes.name AS class
    FROM functions
    LEFT JOIN classes ON functions.classid = classes.id
    LEFT JOIN returns ON returns.functionid = functions.id
    WHERE functions.id = {$function_id}";
    $res = db_query ($q);
    $function = db_fetch_assoc($res);

    echo '<div class="function-usage">';

    if ($function['returntypes']) {
        echo htmlspecialchars($function['returntypes']), ' ';
    } else {
        echo 'unknown ';
    }

    if ($function['class']) {
        if ($function['static']) {
            echo "{$function['class']}::";
        } else {
            echo "\${$function['class']}->";
        }
    }

    echo '<b>', $function['name'], '</b> ( ';

    $q = "SELECT name, type, defaultvalue FROM arguments WHERE functionid = {$function_id} ORDER BY id";
    $res = db_query($q);
    $j = 0;
    $num_close = 0;
    while ($row = db_fetch_assoc ($res)) {
        $row['name'] = htmlspecialchars($row['name']);
        $row['type'] = htmlspecialchars($row['type']);
        if ($row['type'] == '') $row['type'] = 'mixed';

        if ($row['defaultvalue'] !== null) echo '[';
        if ($j++ > 0) echo ', ';

        echo " {$row['type']} {$row['name']} ";
        if ($row['defaultvalue'] !== null) $num_close++;
    }
    echo str_repeat(']', $num_close);
    echo ' );';
    echo '</div>';
}


/**
* Return a link to a given file
*
* @param string $filename The file to return a link for
* @return string HTML of a complete A link to the file
**/
function get_file_link($filename)
{
    return '<a href="file?name=' . urlencode($filename) . '">' . htmlspecialchars($filename) . '</a>';
}


/**
* Return a link to the source view for a file
*
* @param string $filename The file to return a link for
* @param int $linenum Line number to highlight
* @return string HTML of a complete A link to the file
**/
function get_source_link($filename, $linenum = null)
{
    $source_url = 'file_source?name=' . urlencode($filename);
    $out = '<a href="' . htmlspecialchars($source_url) . '">' . htmlspecialchars($filename) . '</a>';

    if ($linenum) {
        $source_url .= '&highlight=' . $linenum;
        if ($linenum > 5) {
            $source_url .= '#src-lines-' . ($linenum - 5);
        }
        $out .= ' line ';
        $out .= '<a href="' . htmlspecialchars($source_url) . '">' . $linenum . '</a>';
    }

    return $out;
}


/**
* Return a link to a given class
*
* @param string $class The name of the class to return a link for
* @param string $link_text Text to show on the link; defaults to the class name
* @return string HTML of a complete A link to the class
**/
function get_class_link($class, $filename = null, $link_text = null)
{
    $url = 'class?name=' . urlencode($class);
    if ($filename) $url .= '&file=' . urlencode($filename);
    return '<a href="' . htmlspecialchars($url) . '">' . htmlspecialchars($link_text ?: $class) . '</a>';
}


/**
* Return a link to a given interface
*
* @param string $interface The name of the interface to return a link for
* @return string HTML of a complete A link to the interface
**/
function get_interface_link($interface)
{
    return '<a href="interface?name=' . urlencode($interface) . '">' . htmlspecialchars($interface) . '</a>';
}


/**
* Return a link to a given namespace
*
* @param string $namespace The name of the namespace to return a link for
**/
function get_namespace_link($namespace)
{
    return '<a href="namespace?name=' . urlencode($namespace) . '">' . htmlspecialchars($namespace) . '</a>';
}


/**
* Return a link to a given function
*
* @param string $class The name of the class or interface a function is a member of. Use NULL for non-class functions
* @param string $function The name of the function to return a link for
* @param string $link_text Text to show on the link; defaults to the function name
* @return string HTML of a complete A link to the function
**/
function get_function_link($class, $function, $link_text = null)
{
    $out = '';
    if ($class) {
        $out .= '<a href="function?name=' . urlencode($function) . '&memberof=' . urlencode($class) . '">';
    } else {
        $out .= '<a href="function?name=' . urlencode($function) . '">';
    }
    $out .= htmlspecialchars($link_text ?: $function);
    $out .= '</a>';
    return $out;
}


function redirect($url)
{
    header('Location: ' . $url);
    exit(0);
}
