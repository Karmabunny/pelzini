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
    public $throws;
    public $visibility;
    public $abstract;
    public $description;
    public $returns;
    public $static;
    public $final;


    public function __construct()
    {
        parent::__construct();

        $this->args = array();
        $this->throws = array();
        $this->returns = array();
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
                    
                } else if (isset($args[$parts[1]])) {
                    // type name desc
                    $arg = $args[$parts[1]];
                    if (!$arg->type) $arg->type = $parts[0];
                    unset($parts[0], $parts[1]);
                    
                } else {
                    // type desc
                    $arg = @$this->args[$idx];
                    if (!$arg) continue;
                    if (!$arg->type) $arg->type = $parts[0];
                    unset($parts[0]);
                }
                
                $arg->description = htmlify_text(implode(' ', $parts));
            }
        }

        // Combine @throw and @throws docblock tags
        $throws = array();
        if (isset($this->docblock_tags['@throw'])) {
            $throws = $this->docblock_tags['@throw'];
        }
        if (isset($this->docblock_tags['@throws'])) {
            $throws = array_merge($throws, $this->docblock_tags['@throws']);
        }

        // Process throws
        foreach ($throws as $throws_tag) {
            if ($throws_tag == '') $throws_tag = 'Exception';
            $parts = preg_split('/\s+/', $throws_tag, 2);
            $throw = new ParserThrow();
            $throw->exception = $parts[0];
            $throw->description = htmlify_text(@$parts[1]);
            $this->throws[] = $throw;
        }

        // Do return value
        if (isset($this->docblock_tags['@return'])) {
            $this->returns = array();
            foreach ($this->docblock_tags['@return'] as $return_tag) {
                $parts = preg_split('/\s+/', $return_tag, 2);
                $types = explode('|', $parts[0]);
                foreach ($types as $t) {
                    $return = new ParserReturn();
                    $return->type = trim($t);
                    $return->description = htmlify_text(@$parts[1]);
                    $this->returns[] = $return;
                }
            }
        }

        // If there is only one return tag, and the type ends in ?
        // Nuke the ? and create a new return tag called "null"
        if (count($this->returns) == 1) {
            if (substr($this->returns[0]->type, -1) == '?') {
                $this->returns[0]->type = substr($this->returns[0]->type, 0, -1);
                $null_return = new ParserReturn();
                $null_return->type = 'null';
                $this->returns[] = $null_return;
            }
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
        foreach ($this->throws as $t) $t->dump();
        foreach ($this->returns as $r) $r->dump();

        parent::dump();
        echo '</div>';
    }


}


?>
