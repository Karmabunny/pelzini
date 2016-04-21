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
 * Function arguments
 *
 * @package Processor
 * @author Josh
 * @since 0.1
 **/

/**
 * Represents a function argument
 **/
class ParserArgument extends ParserItem {
    public $name;
    public $type;
    public $byref;
    public $description;
    public $default;

    public function __construct()
    {
        parent::__construct();
        $this->byref = false;
    }

    /**
     * Debugging use only
     **/
    public function dump()
    {
        echo '<div style="border: 1px green solid;">';
        echo 'Name: ' . $this->name;
        echo '<br>Type: ' . $this->type;
        echo '<br>ByRef: ' . $this->byref;
        echo '<br>Default: ' . $this->default;
        echo '<br>Description: ' . $this->description;
        echo '</div>';
    }

}
