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
 * The MySQL wrapper functions
 * @package Viewer
 * @author Josh Heidenreich
 * @since 0.2
 **/


/**
 * Connects to the MySQL database
 **/
function db_connect($settings)
{
    global $db_connection;

    $db_connection = new PDO(
        "mysql:host={$settings['server']};dbname={$settings['name']};charset=utf8",
        $settings['username'],
        $settings['password']
    );
    $db_connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}


/**
 * Makes a query to the MySQL database
 **/
function db_query($q)
{
    global $db_connection;
    return $db_connection->query($q);
}


/**
 * Quotes a string as nessasary for use by hte MySQL database
 * The result will be different depending on the type of the input.
 *  - a number will be left as is
 *  - a string will be quoted
 *  - a null value will be returned as NULL
 **/
function db_quote($str)
{
    global $db_connection;

    if ($str === null) {
        return 'NULL';

    } else if (is_int($str)) {
        return $str;

    } else {
        return $db_connection->quote($str);
    }
}


/**
 * Fetches a MySQL result set as an associative array
 **/
function db_fetch_assoc($res)
{
    return $res->fetch(PDO::FETCH_ASSOC);
}


/**
 * Returns the number of rows in a MySQL result set
 **/
function db_num_rows($res)
{
    return $res->rowCount();
}


/**
 * Returns the number of rows affected by the last MySQL query that was executed
 **/
function db_affected_rows($res)
{
    return $res->rowCount();
}


/**
 * Returns the last unique ID generated by a query
 **/
function db_insert_id()
{
    global $db_connection;
    return $db_connection->lastInsertId();
}
