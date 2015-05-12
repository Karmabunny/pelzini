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
 * Contains the {@link JavascriptLexer} class
 *
 * @package Parsers
 * @author Josh
 * @since 0.2
 **/

/**
 * Tokenises a javascript file.
 **/
class JavascriptLexer
{
    // Should this be common for all lexers?
    private $single_characters = array(
        '(' => TOKEN_OPEN_NORMAL_BRACKET,
        ')' => TOKEN_CLOSE_NORMAL_BRACKET,
        '{' => TOKEN_OPEN_CURLY_BRACKET,
        '}' => TOKEN_CLOSE_CURLY_BRACKET,
        '[' => TOKEN_OPEN_SQUARE_BRACKET,
        ']' => TOKEN_CLOSE_SQUARE_BRACKET,
        '=' => TOKEN_EQUALS,
        '.' => TOKEN_PERIOD,
        ',' => TOKEN_COMMA,
        ';' => TOKEN_SEMICOLON
    );

    private $reserved_words = array(
        'break', 'else', 'new', 'var', 'case', 'finally', 'return', 'void', 'catch',
        'for', 'switch', 'while', 'do', 'continue', 'function', 'this', 'with', 'default', 'if', 'throw',
        'delete', 'in', 'try', 'instanceof', 'typeof',

        'abstract', 'enum', 'int', 'short', 'boolean', 'export', 'interface', 'static', 'byte', 'extends',
        'long', 'super', 'char', 'final', 'native', 'synchronized', 'class', 'float', 'package', 'throws',
        'const', 'goto', 'private', 'transient', 'debugger', 'implements', 'protected', 'volatile'
    );

    private $reserved_values = array('null', 'true', 'false');


    /**
     * Resets any state variables used by this class back to their initial state
     **/
    public function resetState()
        {}


    /**
     * Should return an array of zero or more Token objects
     **/
    public function process($source)
    {
        $offset = 0;
        $length = strlen($source);
        $tokens = array();

		Token::setCurrLineNum(1);
        while ($offset < $length) {

			if (preg_match('/\G(\n|\r|\n\r)/', $source, $matches, PREG_OFFSET_CAPTURE, $offset)) {
                Token::setIncrLineNum();
                $offset = $matches[0][1] + strlen($matches[0][0]);
                //echo "LINE..."; flush();
                continue;
            }

            // Firstly, look for single character tokens
            // Should this be common for all lexers?
            foreach ($this->single_characters as $char => $token_type) {
                if ($source[$offset] == $char) {
                    $tokens[] = new Token($token_type, $char);
                    $offset++;
                    continue 2;
                }
            }

            // Now use regular expressions to find various other tokens
            // If one is found, add it to the list and move on

            // Search for a Docblock comment
            if (preg_match('/\G\/\*\*(.+?)\*\//s', $source, $matches, PREG_OFFSET_CAPTURE, $offset)) {
                $tokens[] = new Token(TOKEN_DOCBLOCK, $matches[0][0]);
                $offset = $matches[0][1] + strlen($matches[0][0]);
                continue;
            }

            // Search for a regular /* */ comment
            if (preg_match('/\G\/\*(.+?)\*\//s', $source, $matches, PREG_OFFSET_CAPTURE, $offset)) {
                $tokens[] = new Token(TOKEN_COMMENT, $matches[0][0]);
                $offset = $matches[0][1] + strlen($matches[0][0]);
                continue;
            }

            // Search for a // comment
            if (preg_match('/\G\/\/.*\n/', $source, $matches, PREG_OFFSET_CAPTURE, $offset)) {
                $tokens[] = new Token(TOKEN_COMMENT, rtrim($matches[0][0]));
                $offset = $matches[0][1] + strlen($matches[0][0]);
                continue;
            }

            // Search for a double-quoted string
            if (preg_match('/\G"([^\"]|\.)*"/i', $source, $matches, PREG_OFFSET_CAPTURE, $offset)) {
                $tokens[] = new Token(TOKEN_STRING, $matches[0][0]);
                $offset = $matches[0][1] + strlen($matches[0][0]);
                continue;
            }

            // Search for a single-quoted string
            if (preg_match('/\G\'([^\\\']|\.)*\'/i', $source, $matches, PREG_OFFSET_CAPTURE, $offset)) {
                $tokens[] = new Token(TOKEN_STRING, $matches[0][0]);
                $offset = $matches[0][1] + strlen($matches[0][0]);
                continue;
            }

            // Search for reserved words. This list includes the future reserved words
            foreach ($this->reserved_words as $word) {
                if (preg_match('/\G' . $word . '/i', $source, $matches, PREG_OFFSET_CAPTURE, $offset)) {

                    // Some reserved words get a specific token - basiclly anything that is understood by the analyser
                    // everything else just gets the generic 'reserved word' token.
                    switch ($word) {
                    case 'function':
                        $tokens[] = new Token(TOKEN_FUNCTION);
                        break;

                    default:
                        $tokens[] = new Token(TOKEN_RESERVED_WORD, $word);
                        break;
                    }

                    $offset = $matches[0][1] + strlen($matches[0][0]);
                    continue;
                }
            }

            // Search for reserved values
            foreach ($this->reserved_values as $value) {
                if (preg_match('/\G' . $value . '/i', $source, $matches, PREG_OFFSET_CAPTURE, $offset)) {
                    $tokens[] = new Token(TOKEN_RESERVED_VALUE, $value);
                    $offset = $matches[0][1] + strlen($matches[0][0]);
                    continue;
                }
            }

            // Search for a number
            $number_expressions = array(
                '/\G0x[0-9A-F]+/i',
                '/\G[0-9]+/'
            );
            foreach ($number_expressions as $expression) {
                if (preg_match($expression, $source, $matches, PREG_OFFSET_CAPTURE, $offset)) {
                    $tokens[] = new Token(TOKEN_NUMBER, $matches[0][0]);
                    $offset = $matches[0][1] + strlen($matches[0][0]);
                    continue;
                }
            }

            // Search for an indentifier
            if (preg_match('/\G[a-z$_][a-z0-9$_]*/i', $source, $matches, PREG_OFFSET_CAPTURE, $offset)) {
                $tokens[] = new Token(TOKEN_IDENTIFIER, $matches[0][0]);
                $offset = $matches[0][1] + strlen($matches[0][0]);
                continue;
            }

            $offset++;
        }

        return $tokens;
    }


}


?>
