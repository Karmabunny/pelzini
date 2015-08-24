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
 * This class is used to represent a token that has been tokenised using a Lexer. (e.g. the JavascriptLexer)
 * These tokens are used to create various ParserItems, by passing them to an Analyser (e.g. the JavascriptAnalyser)
 **/
class Token
{
    private $type;
    private $value;
    private $linenum;

    static $curr_linenum = 1;

    public function __construct($type, $value = null)
    {
        $this->linenum = self::$curr_linenum;
        $this->type = $type;
        $this->value = $value;
    }


    /**
     * Gets the type of this token
     **/
    public function gettype()
    {
        return $this->type;
    }


    /**
     * Gets the value of this token
     **/
    public function getValue()
    {
        return $this->value;
    }


    /**
     * Set the "current" line number. New tokens have a line number set to this figure.
     *
     * @param int $line
     **/
    public static function setCurrLineNum($line)
    {
        self::$curr_linenum = $line;
    }


    /**
     * Increment the "current" line number. New tokens have a line number set to this figure.
     **/
    public static function setIncrLineNum($incr = 1)
    {
        self::$curr_linenum += $incr;
    }


    /**
     * Gets the line number this toekn
     **/
    public function getLineNum()
    {
        return $this->linenum;
    }


    /**
     * Uses some PHP cleverness to get the name of the constant
     * that this token referres to.
     * Good for debugging
     **/
    public function getTypeName()
    {
        $constants = get_defined_constants();
        foreach ($constants as $name => $val) {
            if (strncmp($name, 'TOKEN_', 6) === 0 and $val == $this->type) {
                return $name;
            }
        }

        return null;
    }

}
