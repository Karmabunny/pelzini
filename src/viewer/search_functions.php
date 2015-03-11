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
 * Back-end functions behind search
 * Allows for searching outside of search.php
 *
 * @package Viewer
 * @author Josh Heidenreich
 * @since 0.1
 * @tag i18n-done
 **/


/**
 * Does a source search.
 *
 * @return boolean True if some results were found, false if nothing was found
 **/
function search_source($query, $case_sensitive = false)
{
    global $project;

    // Determine the match string
    // #ITEM# will be replaced in the specific search query
    // for for classes, #ITEM# will become classes.name
    $this_match_string = "files.source LIKE '%" . db_escape($query) . "%'";
    if ($case_sensitive) $this_match_string = "BINARY files.source LIKE '%" . db_escape($query) . "%'";

    $q = "SELECT files.id, files.name AS filename, files.source
    FROM files
    WHERE {$this_match_string}
      AND files.projectid = {$project['id']}
    ORDER BY files.name";
    $res = db_query ($q);
    $num_files = db_num_rows ($res);

    if ($num_files != 0) {
        echo '<h3>', str(STR_SOURCE_CODE_RESULT, 'num', $num_files), '</h3>';

        $regex_search = htmlspecialchars($query);
        $regex_search = '/(' . preg_quote($regex_search, '/'). ')/';
        if (! $case_sensitive) $regex_search .= 'i';
        $url_keyword = urlencode($query);

        $num_lines = 0;
        $alt = false;
        echo '<div class="list">';
        while ($row = db_fetch_assoc ($res)) {
            $row['filename'] = htmlspecialchars($row['filename']);

            $class = 'item';
            if ($alt) $class .= '-alt';

            echo "<div class=\"{$class}\">";
            echo "<img src=\"assets/icon_remove.png\" alt=\"\" title=\"Hide this result\" onclick=\"hide_content(event)\" class=\"showhide\">";
            echo "<p>";
            echo "<strong><a href=\"file?id={$row['id']}\">{$row['filename']}</a></strong> &nbsp; ";
            echo "<small><a href=\"file_source?id={$row['id']}&keyword={$url_keyword}\">Highlighted file source</a></small>";
            echo "</p>\n";

            // Finds the lines, and highlights the term
            echo "<p class=\"content\">";
            $lines = explode("\n", $row['source']);
            foreach ($lines as $num => $line) {
                if (stripos($line, $query) !== false) {
                    $num++;
                    $line = htmlspecialchars($line);
                    $line = preg_replace($regex_search, "<span class=\"highlight\">\$1</span>", $line);

                    $source_url = "file_source?id={$row['id']}&highlight={$num}";
                    if ($num > 5) $source_url .= '#line' . ($num - 5);

                    $num_lines++;

                    echo "Line <a href=\"{$source_url}\">{$num}</a>: <code>{$line}</code><br>";
                }
            }
            echo "</p>";
            echo "</div>";

            $alt = ! $alt;
        }

        echo "<div class=\"summary\">";
        echo '<p>', str(STR_NUM_SOURCE_RESULTS, 'lines', $num_lines, 'files', $num_files), '</p>';
        echo "</div>";

        echo '</div>';


        return true;

    } else {
        return false;
    }
}


?>
