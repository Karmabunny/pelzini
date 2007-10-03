<?php

if (!defined('T_ML_COMMENT')) {
    define('T_ML_COMMENT', T_COMMENT);
} else {
    define('T_DOC_COMMENT', T_ML_COMMENT);
}


function tokenise ($filename) {
	$functions = array();
	$classes = array();


	$source = file_get_contents($filename);
	$tokens = token_get_all($source);

	$source = htmlentities($source);
	echo "<pre>{$source}</pre>";

	//echo "<pre>" . print_r ($tokens, true) . "</pre>";



	$current_function = null;
	$inside_function = null;
	$current_class = null;
	$inside_class = null;


	foreach ($tokens as $token) {
	    if (is_string($token)) {
			// 1-char token
			if ($token == '{') {
				if ($current_function != null) {
					if ($inside_class != null) {
						$current_function->visibility = $visibility;
						$visibility = null;
						$inside_class->functions[] = $current_function;
					} else {
						$functions[] = $current_function;
					}
					$inside_function = $current_function;		
					$current_function = null;

				} else if ($current_class != null) {
					$current_class->visibility = $visibility;
					$visibility = null;
					$classes[] = $current_class;
					$inside_class = $current_class;
					$current_class = null;
				}
				
			} else if ($token == '}') {
				if ($inside_function != null) {
					$inside_function = null;
				} else {
					$inside_class = null;
				}
			}

	    } else {
	        // token array
	        list($id, $text) = $token;
	 
	        switch ($id) {
	            case T_COMMENT:
	            case T_ML_COMMENT:
	            case T_DOC_COMMENT:
	                // no action on comments
	                break;

				case T_FUNCTION:
					$current_function = new ParserFunction();
					break;

				case T_CLASS:
					$current_class = new ParserClass();
					break;

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

				case T_STRING:
					if ($current_function != null) {
						if ($current_function->name == '') {
							$current_function->name = $text;
						} else {
							$param_type = $text;
						}

					} else if ($current_class != null) {
						$current_class->name = $text;
					}
					break;


				case T_PRIVATE:
					$visibility = 'private';
					break;

				case T_PROTECTED:
					$visibility = 'protected';
					break;

				case T_PUBLIC:
					$visibility = 'public';
					break;



	            default:
					//echo $id . ' = ' . token_name($id) .  '<BR>';
	                break;
	        }
	    }
	}


	foreach ($functions as $func) $func->dump();
	foreach ($classes as $class) $class->dump();

}

?>
