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
 * Contains the {@link Outputter} class
 *
 * @package Outputters
 * @author Josh
 * @since 0.2
 **/

/**
 * The top-level class for all outputters
 **/
abstract class Outputter
{

    /**
     * Does the actual outputting of the file objects (and their sub-objects)
     *
     * @param array $parser_items The ParserItem(s) to save
     * @param Config $config The project config
     * @return boolean True on success, false on failure
     **/
    abstract function output($parser_items, Config $config);

}


?>
