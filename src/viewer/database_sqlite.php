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
 * The SQLite wrapper functions
 * @package Viewer
 * @author Josh Heidenreich
 * @since 0.2
 **/


/**
 * Connects to the SQLite database
 **/
function db_connect($settings)
{
    global $db_connection;

    $db_connection = sqlite_open(
        $settings['filename']
    );
}


/**
 * Makes a query to the SQLite database
 **/
function db_query($q)
{
    global $db_connection;

    return sqlite_query($db_connection, $q);
}


/**
 * Escapses a string for use by the SQLite database
 **/
function db_escape($str)
{
    return sqlite_escape_string($str);
}


/**
 * Quotes a string as nessasary for use by hte SQLite database
 * The result will be different depending on the type of the input.
 *  - a number will be left as is
 *  - a string will be quoted
 *  - a null value will be returned as NULL
 **/
function db_quote($str)
{
    if ($str === null) {
        return 'NULL';

    } else if (is_int($str)) {
        return $str;

    } else {
        return "'" . sqlite_escape_string($str) . "'";
    }
}


/**
 * Fetches a SQLite result set as an associative array
 **/
function db_fetch_assoc($res)
{
    $row = sqlite_fetch_array($res, SQLITE_ASSOC);

    // MySQL and PostgreSQL will, for the query "SELECT classes.id, classes.name" return $row as [ 'id' => ... ; 'name' => ... ]
    // but SQLite returns it as [ 'classes.id' => ... ; 'classes.name' => ... ]
    // This code looks for a '.' in the name
    // and trims out everything after the dot, so that SQLite behaves like the others
    if ($row) {
        $new = array();
        foreach ($row as $key => $val) {
            $x = strpos($key, '.');
            if ($x !== false) {
                $key = substr($key, $x + 1);
            }

            $new[$key] = $val;
        }

        $row = $new;
    }

    return $row;
}


/**
 * Returns the number of rows in a SQLite result set
 **/
function db_num_rows($res)
{
    return sqlite_num_rows($res);
}


/**
 * Returns the number of rows affected by the last SQLite query that was executed.
 * SQLite has no affected rows system. Assume if there is a result, that rows were affected.
 **/
function db_affected_rows($res)
{
    if ($res) return 1;
    return 0;
}


/**
 * Returns the last unique ID generated by a query
 **/
function db_insert_id()
{
    global $db_connection;

    return sqlite_last_insert_rowid($q, $db_connection);
}


?>
