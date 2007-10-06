<?php

if (!defined('T_ML_COMMENT')) {
    define('T_ML_COMMENT', T_COMMENT);
} else {
    define('T_DOC_COMMENT', T_ML_COMMENT);
}


function tokenise ($filename) {

	$source = file_get_contents($filename);
	$tokens = token_get_all($source);

	$source = htmlentities($source);
	echo "<pre>{$source}</pre>";

	//echo "<pre>" . print_r ($tokens, true) . "</pre>";


	$current_file = new ParserFile ();
	$current_file->name = $filename;


	// the vars that make it tick
	$current_function = null;
	$inside_function = null;
	$current_class = null;
	$inside_class = null;
	$next = null;
	$brace_count = 0;
	$abstract = false;
	$next_comment = null;


	foreach ($tokens as $token) {
	    if (is_string($token)) {
			// opening of a function or class block
			if ($token == '{') {
				// opening of function
				if ($current_function != null) {
					if ($inside_class != null) {
						$current_function->visibility = $visibility;
						$visibility = null;
						$inside_class->functions[] = $current_function;
					} else {
						$current_file->functions[] = $current_function;
					}
					$current_function->post_load();
					$inside_function = $current_function;		
					$current_function = null;

				// opening of class
				} else if ($current_class != null) {
					$current_class->visibility = $visibility;
					$visibility = null;
					$current_file->classes[] = $current_class;
					$inside_class = $current_class;
					$current_class = null;
					$next = null;

				} else {
					$brace_count++;
				}
			

			// function in an interface
			} else if ($token == ';') {
				if ($current_function != null) {
					$current_function->visibility = $visibility;
					$visibility = null;
					$inside_class->functions[] = $current_function;
					$current_function = null;
				}


			// closing of a class or function block			
			} else if ($token == '}') {
				if ($brace_count == 0) {
					if ($inside_function != null) {
						$inside_function = null;
					} else {
						$inside_class = null;
					}

				} else {
					$brace_count--;
				}		
			}

	    } else {
	        // token array
	        list($id, $text) = $token;

	        switch ($id) {
	            case T_DOC_COMMENT:
	                $next_comment = $text;
	                break;

				case T_FUNCTION:
					$current_function = new ParserFunction();
					if ($abstract) {
						$current_function->abstract = true;
						$abstract = false;
					}
					if ($next_comment) {
						$current_function->apply_comment($next_comment);
						$next_comment = null;
					}
					break;

				case T_CLASS:
					$current_class = new ParserClass();
					if ($abstract) {
						$current_class->abstract = true;
						$abstract = false;
					}
					if ($next_comment) {
						$current_class->apply_comment($next_comment);
						$next_comment = null;
					}
					break;

				case T_INTERFACE:
					$current_class = new ParserInterface();
					if ($next_comment) {
						$current_class->apply_comment($next_comment);
						$next_comment = null;
					}
					break;


				// variables are added according to scope
				// will become a ParserVariable or a ParserParameter
				case T_VARIABLE:
					if ($current_function != null) {
						$parameter = new ParserParameter();
						$parameter->name = $text;
						if ($param_type != null) {
							$parameter->type = $param_type;
							$param_type = null;
						}
						$current_function->params[] = $parameter;

					} else if (($inside_class != null) && ($inside_function == null)) {
						$variable = new ParserVariable();
						$variable->name = $text;
						$variable->visibility = $visibility;
						$visibility = null;
						$inside_class->variables[] = $variable;
					}
					break;

				// A string my become an extends, implements
				// function name or class name
				case T_STRING:
					if ($next != null) {
						if ($next == T_EXTENDS) {
							$current_class->extends = $text;
							$next = null;
						} else if ($next == T_IMPLEMENTS) {
							$current_class->implements[] = $text;
						}

					} else if ($current_function != null) {
						if ($current_function->name == '') {
							$current_function->name = $text;
						} else {
							$param_type = $text;
						}

					} else if ($current_class != null) {
						$current_class->name = $text;

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
					$next = $id;
					break;

				case T_ABSTRACT:
					$abstract = true;
					break;

	

				//default:
				//	echo '<p>' . token_name($id) . ' &nbsp; ' . $text . '</p>';
	        }
	    }
	}


	$current_file->dump();

}

?>
