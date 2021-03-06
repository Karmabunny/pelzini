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
 * Contains the {@link CLexer} class
 *
 * @package Parsers
 * @author Josh
 * @since 0.2
 **/

/**
 * Tokenises a C file.
 **/
class CLexer
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
        ';' => TOKEN_SEMICOLON,
        '*' => TOKEN_ASTERIX
    );

    private $reserved_words = array(
        'auto', 'break', 'case', 'continue', 'default', 'do', 'else', 'enum', 'extern',
        'for', 'goto', 'if', 'inline', 'register', 'restrict', 'return', 'sizeof', 'static',
        'struct', 'switch', 'typedef', 'union', 'volatile', 'while'
    );

    private $token_words = array(
        'const' => TOKEN_CONST,
    );
    
    private $reserved_values = array('NULL');


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

        // strip comments
        $source = preg_replace('!/\*[^*].*?\*/!s', '', $source);

        $curr_line = 1;
        while ($offset < $length) {

            if (preg_match('/\G(\n|\r|\n\r)/', $source, $matches, PREG_OFFSET_CAPTURE, $offset)) {
                $curr_line++;
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
                    //echo "CHAR..."; flush();
                    continue 2;
                }
            }

            // Now use regular expressions to find various other tokens
            // If one is found, add it to the list and move on

            // Search for a preprocessor directive
            if (preg_match('/\G(#[a-z]+.*?)\n/s', $source, $matches, PREG_OFFSET_CAPTURE, $offset)) {
                $tokens[] = new Token(TOKEN_C_PREPROCESSOR, $matches[0][0]);
                $offset = $matches[0][1] + strlen($matches[0][0]);
                //echo "PREP..."; flush();
                continue;
            }

            // Search for a Docblock comment
            if (preg_match('/\G\/\*\*(.+?)\*\//s', $source, $matches, PREG_OFFSET_CAPTURE, $offset)) {
                $tokens[] = new Token(TOKEN_DOCBLOCK, $matches[0][0]);
                $offset = $matches[0][1] + strlen($matches[0][0]);
                //echo "DOCB..."; flush();
                continue;
            }

            // Search for a regular /* */ comment
            if (preg_match('/\G\/\*(.+?)\*\//s', $source, $matches, PREG_OFFSET_CAPTURE, $offset)) {
                $tokens[] = new Token(TOKEN_COMMENT, $matches[0][0]);
                $offset = $matches[0][1] + strlen($matches[0][0]);
                //echo "COMM..."; flush();
                continue;
            }

            // Search for a // comment
            if (preg_match('/\G\/\/.*\n/', $source, $matches, PREG_OFFSET_CAPTURE, $offset)) {
                $tokens[] = new Token(TOKEN_COMMENT, rtrim($matches[0][0]));
                $offset = $matches[0][1] + strlen($matches[0][0]);
                //echo "DBLS..."; flush();
                continue;
            }

            // Search for a double-quoted string
            if (preg_match('/\G"([^\"]|\.)*"/i', $source, $matches, PREG_OFFSET_CAPTURE, $offset)) {
                $tokens[] = new Token(TOKEN_STRING, $matches[0][0]);
                $offset = $matches[0][1] + strlen($matches[0][0]);
                //echo "STRD..."; flush();
                continue;
            }

            // Search for a single-quoted string
            if (preg_match('/\G\'([^\\\']|\.)*\'/i', $source, $matches, PREG_OFFSET_CAPTURE, $offset)) {
                $tokens[] = new Token(TOKEN_STRING, $matches[0][0]);
                $offset = $matches[0][1] + strlen($matches[0][0]);
                //echo "STRS..."; flush();
                continue;
            }

            // Search for reserved words. This list includes the future reserved words
            foreach ($this->reserved_words as $word) {
                if (preg_match('/\G' . $word . '/i', $source, $matches, PREG_OFFSET_CAPTURE, $offset)) {
                    $tokens[] = new Token(TOKEN_RESERVED_WORD, $word);
                    $offset = $matches[0][1] + strlen($matches[0][0]);
                    //echo "RESW..."; flush();
                    continue;
                }
            }

            // Search for reserved values
            foreach ($this->reserved_values as $value) {
                if (preg_match('/\G' . $value . '/i', $source, $matches, PREG_OFFSET_CAPTURE, $offset)) {
                    $tokens[] = new Token(TOKEN_RESERVED_VALUE, $value);
                    $offset = $matches[0][1] + strlen($matches[0][0]);
                    //echo "RESV..."; flush();
                    continue;
                }
            }

            // Search for token words - reserved words with meaning
            foreach ($this->token_words as $word => $token_type) {
                if (preg_match('/\G' . $word . '/i', $source, $matches, PREG_OFFSET_CAPTURE, $offset)) {
                    $tokens[] = new Token($token_type, $word);
                    $offset = $matches[0][1] + strlen($matches[0][0]);
                    //echo "TOKW..."; flush();
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
                    //echo "NUMB..."; flush();
                    continue;
                }
            }

            // Search for an indentifier
            if (preg_match('/\G[a-z$_][a-z0-9$_]*/i', $source, $matches, PREG_OFFSET_CAPTURE, $offset)) {
                $tokens[] = new Token(TOKEN_IDENTIFIER, $matches[0][0]);
                $offset = $matches[0][1] + strlen($matches[0][0]);
                //echo "IDEN..."; flush();
                continue;
            }

            //echo "OTHR..."; flush();
            $offset++;
        }

        //echo "\n"; flush();

        return $tokens;
    }


}


?>
