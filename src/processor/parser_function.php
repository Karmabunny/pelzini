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
 * Contains the {@link ParserFunction} class
 *
 * @package Parser model
 * @author Josh Heidenreich
 * @since 0.1
 **/

/**
 * Represents a function
 **/
class ParserFunction extends CodeParserItem {
    public $name;
    public $args;
    public $visibility;
    public $abstract;
    public $description;
    public $return_type;
    public $return_description;
    public $static;
    public $final;


    public function __construct()
    {
        parent::__construct();

        $this->args = array();
        $this->visibility = 'public';
        $this->static = false;
        $this->final = false;
    }


    /**
     * Processes Javadoc tags that are specific to this PaserItem
     **/
    protected function processSpecificDocblockTags($docblock_tags)
    {
        $this->description = htmlify_text(@$docblock_tags['@summary']);
    }


    /**
     * Does post-pasing processing of this ParserFunction.
     * Specifically, loads types for the function arguments
     **/
    public function post_load()
    {
        $args = array();
        foreach ($this->args as $arg) {
            $args[$arg->name] = $arg;
        }

        // Do arguments
        $params = @$this->docblock_tags['@param'];
        if ($params != null) {
            foreach ($params as $idx => $param_tag) {
                $parts = preg_split('/\s+/', $param_tag, 3);

                if (isset($args[$parts[0]])) {
                    // name type desc
                    $arg = $args[$parts[0]];
                    if (!$arg->type) $arg->type = $parts[1];
                    unset($parts[0], $parts[1]);
                    
                } else if (isset($arg_types[$parts[1]])) {
                    // type name desc
                    $arg = $args[$parts[1]];
                    if (!$arg->type) $arg->type = $parts[0];
                    unset($parts[0], $parts[1]);
                    
                } else {
                    // type desc
                    $arg = $this->args[$idx];
                    if (!$arg->type) $arg->type = $parts[0];
                    unset($parts[0]);
                }
                
                $arg->description = htmlify_text(implode(' ', $parts));
            }
        }

        // Do return value
        $return = @$this->docblock_tags['@return'];
        if ($return == null) @$return = $this->docblock_tags['@returns'];
        if ($return != null) {
            $return = array_pop($return);
            @list($this->return_type, $this->return_description) = preg_split('/\s+/', $return, 2);
            $this->return_description = htmlify_text($this->return_description);
        }
    }


    /**
     * Debugging use only
     **/
    public function dump()
    {
        echo '<div style="border: 1px red solid;">';
        echo $this->visibility . ' ';
        echo $this->name;
        if ($this->abstract) echo '<br>abstract';
        if ($this->static) echo '<br>static';
        if ($this->final) echo '<br>static';
        echo '<br>' . $this->description;
        foreach ($this->args as $a) $a->dump();

        parent::dump();
        echo '</div>';
    }


}


?>
