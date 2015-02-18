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
 * Contains the {@link ParserItem} class
 *
 * @package Parser model
 * @author Josh Heidenreich
 * @since 0.2
 **/

/**
 * The top-level class of all parser items. Almost all ParserItems should extend {@link CodeParserItem}.
 **/
abstract class ParserItem
{

    /**
     * In almost all cases this method should be overwritten, but it does not have to be.
     * All overwriting classes must call this method at the beginning of their constructor:
     *   parent::__construct();
     **/
    protected function __construct()
        {}


    /**
     * Is used for debugging.
     **/
    abstract protected function dump();

}


?>
