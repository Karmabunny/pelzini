<?php
require_once 'i18n/strings.php';

/**
 * This is a set of functions for internationalisation.
 *
 * The main functions are:
 *   {@link loadLanguage} for loading a language file
 *   {@link str} for returning a language string
 *   {@link setParam} for manually setting a replacement parameter
 *   {@link clearParams} for clearing all replacement parameters
 *
 * @author Josh Heidenreich, 2009-05-05
 **/


/**
 * Loads a language file
 *
 * If you are using a language which may not be complete, it is advisable
 * to load another language which you know is complete first (e.g. English)
 * This will prevent empty strings being returned.
 *
 * @author Josh Heidenreich, 2009-05-05
 *
 * @param string The name of the language. Should correspond to a language file (without the extension)
 * @return boolean True on success, false on failure
 **/
function loadLanguage($language)
{
    global $strings;

    $file_lines = @file(dirname(__FILE__) . "/i18n/{$language}.txt");
    if ($file_lines == null) return false;

    foreach ($file_lines as $line) {
        $line = preg_replace('/;(.*)$/', '', $line);
        $line = trim($line);
        if ($line == '') continue;

        $parts = preg_split('/\s+/', $line, 2);

        $string_id = @constant($parts[0]);
        if ($string_id == null) continue;
        $strings[$string_id] = $parts[1];
    }

    unset ($file_lines);

    return true;
}


/**
 * Returns the original string from the string table, without any parameter replacement
 **/
function getOriginalString($string_constant)
{
    global $strings;

    return $strings[$string_constant];
}


/**
 * Sets a named param for use by the {@link str} function
 *
 * @author Josh Heidenreich, 2009-05-05
 *
 * @param string $name The name of the parameter
 * @param mixed $value The value of the parameter
 **/
function setParam($name, $value)
{
    global $string_params;

    $name = strtoupper($name);
    if ($value == '') $value = null;
    $string_params[$name] = $value;
}


/**
 * Clears all named parameters that are used by the {@link str} function
 *
 * @author Josh Heidenreich, 2009-05-05
 **/
function clearParams()
{
    global $string_params;

    $string_params = array();
}


/**
 * Outputs a string, and sets arguments
 *
 * This function also allows additional functions after the string constant, printf style.
 * Because the language system uses named arguments, arguments should be specified in pairs
 * The first argument should be the param name, the second argument should be the param
 *
 * @author Josh Heidenreich, 2009-05-05
 *
 * @param int $string_constant The constant representing the string to return
 * @return string The string, with replacements made
 **/
function str($string_constant)
{
    global $strings, $string_params;

    $string_constant = (int) $string_constant;
    if ($string_constant == 0) return "!! UNKNOWN STRING CONSTANT !!";

    $num_args = func_num_args();
    if ($num_args % 2 == 0) return "!! ERROR INCORECT ARGS !!";

    $index = 1;
    for ($index = 1; $index < func_num_args(); $index++) {
        $arg = func_get_arg($index);

        if ($name == '') {
            $name = $arg;
        } else {
            setParam ($name, $arg);
            $name = '';
        }
    }

    $str = param_replace($strings[$string_constant]);
    return $str;
}


/**
 * Replaces params in strings
 *
 * Replacements can be in the following forms:
 *   {PARAM}
 *   Does a replacement with a named parameter, PARAM, specified in any case
 *
 *   {#PLURAL|PARAM|SINGLE|MULTIPLE}
 *   Does a replacement with SINGLE if PARAM (any case) is 1, and with MULTIPLE if PARAM is anything else
 *
 *   {#NL}
 *   Adds a new line to the output
 *
 *   {#URLENC|PARAM}
 *   Returns the specified parameter, urlencode()ed.
 *
 *   {#HTMLENC|PARAM}
 *   Returns the specified parameter, htmlspecialchars()ed.
 *
 * @author Josh Heidenreich, 2009-05-05
 *
 * @param string $str The string to do replacements to
 * @return string The replaced string
 **/
function param_replace($str)
{
    return preg_replace_callback('/\{([^}]+?)\}/', 'param_replace_inner', $str);
}


/**
 * Does the actual legwork for param_replace
 *
 * @author Josh Heidenreich, 2009-05-05
 *
 * @param array $matches The matches array returned by preg_replace_callback
 **/
function param_replace_inner($matches)
{
    global $string_params;

    $replace_code = $matches[1];

    if (strncasecmp($replace_code, '#PLURAL', 7) == 0) {
        $parts = explode('|', $replace_code);
        $var_name = strtoupper($parts[1]);

        if ($string_params[$var_name] == 1) {
            return $parts[2];
        } else {
            return $parts[3];
        }

    } else if (strncasecmp($replace_code, '#NL', 3) == 0) {
        return "<br>";

    } else if (strncasecmp($replace_code, '#URLENC', 7) == 0) {
        $parts = explode('|', $replace_code);
        $replace_code = strtoupper($parts[1]);

        $replace_code = strtoupper($replace_code);
        return urlencode($string_params[$replace_code]);

    } else if (strncasecmp($replace_code, '#HTMLENC', 8) == 0) {
        $parts = explode('|', $replace_code);
        $replace_code = strtoupper($parts[1]);

        $replace_code = strtoupper($replace_code);
        return htmlspecialchars($string_params[$replace_code]);

    } else {
        $replace_code = strtoupper($replace_code);
        return $string_params[$replace_code];
    }

    return "!! ERROR REPLACING '{$replace_code}' !!";
}


?>
