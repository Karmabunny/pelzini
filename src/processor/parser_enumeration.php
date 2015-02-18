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
 * Contains the {@link ParserEnumeration} class
 *
 * @package Parser model
 * @author Josh Heidenreich
 * @since 0.3
 **/

/**
 * Represents an enumeration
 **/
class ParserEnumeration extends CodeParserItem {
    public $name;
    public $constants;
    public $description;
    public $virtual;

    public function __construct()
    {
        parent::__construct();

        $this->constants = array();
        $this->description = '';
        $this->virtual = 0;
    }


    /**
     * Applies the contents of a doc-block to this element
     *
     * @param $text The content of the DocBlock
     **/
    protected function processSpecificDocblockTags($docblock_tags)
    {
        $this->description = htmlify_text($docblock_tags['@summary']);
    }


    /**
     * Debugging use only
     **/
    public function dump()
    {
        echo '<div style="border: 1px pink solid;">';
        echo 'Enum: ', $this->name;
        foreach ($this->constants as $c) $c->dump();
        echo $this->description;

        parent::dump();
        echo '</div>';
    }


}


?>
