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
 * PHP Parser
 * @package Parsers
 * @author Josh Heidenreich
 * @since 0.1
 **/

/**
 * This is the parser for PHP files. It converts a file from the raw PHP into a document tree
 *
 * @todo Convert to new parser system (e.g. Javascript parser)
 * @todo Add support for namespaces, even on PHP < 5.3
 **/
class PhpParser
{

    /**
     * Parses a specific file
     **/
    function parseFile($base_dir, $filename)
    {
        // You can enable the following if you want to debug the parser
        // If you enable the line after it (the 'strpos' line) instead,
        // it will only debug files containing 'test' (e.g. 'php_test.php')
        $debug = false;
        // if (strpos ($filename, 'test') !== false) $debug = true;

        $source = @file_get_contents($base_dir . $filename);
        if ($source == null) return null;

        $tokens = @token_get_all($source);


        $current_file = new ParserFile ();
        $current_file->name = $filename;
        $current_file->source = $source;

        unset ($source);

        // the vars that make it tick
        $current_function = null;
        $inside_function = null;
        $current_class = null;
        $inside_class = null;
        $current_constant = null;
        $next = null;
        $namespace = 0;
        $brace_count = 0;
        $abstract = false;
        $static = false;
        $final = false;
        $next_comment = null;
        $file_has_comment = false;
        $return = false;

        // debugger
        if ($debug) {
            echo '<style>';
            echo 'span {color: green;}';
            echo 'h3 {border: 4px black solid; padding: 3px; margin-top: 2em;}';
            echo 'i {color: gray;}';
            echo '</style>';

            echo '<h3>', htmlspecialchars($filename), '</h3>';
            echo '<pre>';
        }

        $argument = null;
        $visibility = null;
        $param_type = null;
        foreach ($tokens as $token) {
            if (is_array($token) and $token[0] == T_WHITESPACE) continue;

            // debugger
            if ($debug) {
                echo "\n";
                if (is_string($token)) {
                    echo "BARE TEXT\n<i>" . htmlspecialchars($token) . "</i>\n";
                } else {
                    echo htmlspecialchars(token_name($token[0])) . "\n<i>" . htmlspecialchars(str_replace("\n", '\n', $token[1])) . "</i>\n";
                }
            }

            if ($return) {
                if ($token !== ';') {
                    $inside_function->has_return_stmt = true;
                }
                $return = false;
            }

            if (is_string($token)) {
                // opening of a function or class block
                if ($token == '{') {
                    // opening of function
                    if ($current_function != null) {
                        if ($inside_class != null) {
                            if ($visibility != null) {
                                $current_function->visibility = $visibility;
                                $visibility = null;
                            }
                            $inside_class->functions[] = $current_function;

                        } else {
                            $current_file->functions[] = $current_function;
                        }

                        $current_function->post_load();
                        $inside_function = $current_function;
                        $current_function = null;
                        $argument = null;

                        // opening of class
                    } else if ($current_class != null) {
                        if ($visibility != null) {
                            $current_class->visibility = $visibility;
                            $visibility = null;
                        }
                        $current_file->classes[] = $current_class;
                        $inside_class = $current_class;
                        $current_class = null;
                        $next = null;

                    } else {
                        $brace_count++;
                    }


                    // function in an interface
                } else if ($token == ';') {
                	if ($namespace == 1) {
                		$namespace = 2;
                	} else if ($current_function != null) {
                        if ($visibility != null) {
                            $current_function->visibility = $visibility;
                            $visibility = null;
                        }
                        $current_function->post_load();
                        $inside_class->functions[] = $current_function;
                        $current_function = null;
                    }


                    // closing of a class or function block
                } else if ($token == '}') {
                    if ($brace_count == 0) {
                        if ($inside_function != null) {
                            $inside_function = null;
                        } else if ($inside_class != null) {
                            $inside_class = null;
                        }

                    } else {
                        $brace_count--;
                    }
                }

            } else {
                // token array
                list($id, $text, $linenum) = $token;

                switch ($id) {
                case T_CURLY_OPEN:
                    $brace_count++;
                    break;


                case T_DOC_COMMENT:
                    if ($next_comment and ! $file_has_comment) {
                        $current_file->applyComment($next_comment);
                        $next_comment = null;
                        $file_has_comment = true;
                    }
                    $next_comment = $text;
                    break;


                case T_FUNCTION:
                    if ($inside_function != null) {
                        break;
                    }
                    $current_function = new ParserFunction();
                    $current_function->linenum = $linenum;
                    if ($abstract) {
                        $current_function->abstract = true;
                        $abstract = false;
                    }
                    if ($static) {
                        $current_function->static = true;
                        $static = false;
                    }
                    if ($final) {
                        $current_function->final = true;
                        $final = false;
                    }
                    if ($next_comment) {
                        $current_function->applyComment($next_comment);
                        $next_comment = null;
                    }
                    $param_type = null;
                    break;


                case T_CLASS:
                    $current_class = new ParserClass();
                    $current_class->linenum = $linenum;
                    if ($abstract) {
                        $current_class->abstract = true;
                        $abstract = false;
                    } else if ($final) {
                        $current_class->final = true;
                        $final = false;
                    }
                    if ($next_comment) {
                        $current_class->applyComment($next_comment);
                        $next_comment = null;
                    }
                    break;


                case T_INTERFACE:
                    $current_class = new ParserInterface();
                    $current_class->linenum = $linenum;
                    if ($next_comment) {
                        $current_class->applyComment($next_comment);
                        $next_comment = null;
                    }
                    break;


                    // variables are added according to scope
                    // will become a ParserVariable or a ParserArgument
                case T_VARIABLE:
                    if ($current_function != null) {
                        $argument = new ParserArgument();
                        $argument->linenum = $linenum;
                        $argument->name = $text;
                        if ($param_type != null) {
                            $argument->type = $param_type;
                            $param_type = null;
                        }
                        $current_function->args[] = $argument;

                    } else if (($inside_class != null) && ($inside_function == null)) {
                        $variable = new ParserVariable();
                        $variable->linenum = $linenum;
                        $variable->name = $text;
                        $variable->visibility = $visibility ?: 'private';
                        $visibility = null;
                        if ($static) {
                            $variable->static = true;
                            $static = false;
                        }
                        if ($next_comment) {
                            $variable->applyComment($next_comment);
                            $next_comment = null;
                        }
                        $inside_class->variables[] = $variable;
                    }
                    break;


                    // A string my become an extends, implements
                    // function name or class name
                    // it could also be 'define' or 'null'
                case T_STRING:
                    if ($next != null) {
                        if ($next == T_EXTENDS) {
                            $current_class->extends = $text;
                        } else if ($next == T_IMPLEMENTS) {
                            $current_class->implements[] = $text;
                            break;
                        } else if ($next == T_NAMESPACE and $namespace == 0) {
                            $current_file->namespace = array($text);
                            $namespace = 1;
                        } else if ($next == T_NS_SEPARATOR and $namespace == 1) {
                        	$current_file->namespace[] = $text;
                        }
                        $next = null;

                    } else if (strcasecmp($text, 'null') == 0) {
                        if ($current_constant) {
                            $current_constant->value = 'NULL';
                            $current_file->constants[] = $current_constant;
                            $current_constant = null;

                        } else if ($argument) {
                            $argument->default = 'NULL';
                        }

                    } else if (strcasecmp($text, 'true') == 0) {
                        if ($current_constant) {
                            $current_constant->value = 'TRUE';
                            $current_file->constants[] = $current_constant;
                            $current_constant = null;

                        } else if ($argument) {
                            $argument->default = 'TRUE';
                        }

                    } else if (strcasecmp($text, 'false') == 0) {
                        if ($current_constant) {
                            $current_constant->value = 'FALSE';
                            $current_file->constants[] = $current_constant;
                            $current_constant = null;

                        } else if ($argument) {
                            $argument->default = 'FALSE';
                        }

                    } else if ($current_function != null) {
                        if ($current_function->name == '') {
                            $current_function->name = $text;
                        } else {
                            $param_type = $text;
                        }

                    } else if ($current_class != null) {
                        $current_class->name = $text;

                    } else if (strcasecmp($text, 'define') == 0) {
                        $current_constant = new ParserConstant();
                        $current_constant->linenum = $linenum;
                        if ($next_comment) {
                            $current_constant->applyComment($next_comment);
                            $next_comment = null;
                        }

                    }
                    break;


                case T_ARRAY:
                    if ($current_function != null) {
                        $param_type = $text;
                    }
                    break;


                case T_CONSTANT_ENCAPSED_STRING:
                    // removes quotes, etc
                    $name_search = array("\'", '\"', "'", '"');
                    $name_replace = array("'", '"', '', '');
                    $text = str_replace($name_search, $name_replace, $text);

                    if ($current_constant) {
                        if ($current_constant->name == null) {
                            $current_constant->name = $text;
                        } else {
                            $current_constant->value = $text;
                            $current_file->constants[] = $current_constant;
                            $current_constant = null;
                        }

                    } else if ($argument) {
                        $argument->default = $text;
                    }
                    break;


                case T_LNUMBER:
                case T_DNUMBER:
                    if ($current_constant) {
                        if ($current_constant->name != null) {
                            $current_constant->value = $text;
                            $current_file->constants[] = $current_constant;
                            $current_constant = null;
                        }

                    } else if ($argument) {
                        $argument->default = $text;
                    }
                    break;


                    // visibility
                case T_PRIVATE:
                    $visibility = 'private';
                    break;

                case T_PROTECTED:
                    $visibility = 'protected';
                    break;

                case T_PUBLIC:
                    $visibility = 'public';
                    break;


                    // the next token after one of these does the grunt work
                case T_EXTENDS:
                case T_IMPLEMENTS:
                case T_NAMESPACE:
                case T_NS_SEPARATOR:
                    $next = $id;
                    break;

                case T_ABSTRACT:
                    $abstract = true;
                    break;

                case T_STATIC:
                    if (! $inside_function) $static = true;
                    break;

                case T_FINAL:
                    $final = true;
                    break;

                case T_RETURN:
                    if ($inside_function) {
                        $return = true;
                    }
                    break;

                }
            }
        }

        // If there is a comment left that never got assigned,
        // assign it to the file
        if ($next_comment and ! $file_has_comment) {
            $current_file->applyComment($next_comment);
            $next_comment = null;
            $file_has_comment = true;
        }

        if ($debug) echo '</pre>';


        return $current_file;
    }


}


?>
