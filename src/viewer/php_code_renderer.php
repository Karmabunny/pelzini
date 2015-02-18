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
 * Does code rendering for PHP
 *
 * @package Viewer
 * @author Josh Heidenreich
 * @since 0.3
 * @tag i18n-done
 **/

/**
 * Does code rendering for PHP
 **/
class PHPCodeRenderer
{

    /**
     * Returns PHP code which can be used for extending the specified class
     **/
    public function drawClassExtends($class_id)
    {
        $q = "SELECT name FROM classes WHERE id = {$class_id}";
        $res = db_query ($q);
        if (! $res) return;
        $class = db_fetch_assoc ($res);

        $date = date('Y-m-d');

        $out = '';

        $out .= "<?php\n";
        $out .= "/**\n";
        $out .= "* " . str(STR_RENDER_NEW_CLASS_DESC) . "\n";
        $out .= "* \n";
        $out .= "* @author " . str(STR_RENDER_YOUR_NAME) . ", {$date}\n";
        $out .= "**/\n";
        $out .= "class " . str(STR_RENDER_NEW_CLASS_NAME) . " extends {$class['name']} {\n";
        $out .= "    \n";

        $q = "SELECT name, arguments, visibility, description
      FROM functions
      WHERE classid = {$class_id}
        AND final = 0";
        $res = db_query ($q);
        while ($row = db_fetch_assoc ($res)) {
            if ($row['description']) {
                $row['description'] = strip_tags($row['description']);
                $row['description'] = trim($row['description']);
                $row['description'] = str_replace("\n", "\n    * ", $row['description']);

                $out .= "    /**\n";
                $out .= "    * {$row['description']}\n";
                $out .= "    **/\n";
            }

            $out .= "    {$row['visibility']} function {$row['name']} ({$row['arguments']}) {\n";
            $out .= "        // " . str(STR_RENDER_METHOD_COMMENT) . "\n";
            $out .= "    }\n";
            $out .= "    \n";
        }

        $out .= "}\n";
        $out .= "?>\n";

        return $out;
    }


}
