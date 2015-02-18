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
 * This file loads the config for the Pelzini processor
 *
 * @since 0.1
 * @author Josh
 * @package Processor
 **/


$dpgOutputterSettings = array();
$dpgTransformerSettings = array();

$supported_args = array(
    array('--config',  1, 'set_config', 'Loads a config file.'),
    array('--help',    0, 'show_help', 'Shows this help'),
);


// process arguments
if (PHP_SAPI == 'cli') {
    unset ($_SERVER['argv'][0]);

    $holding_args = null;
    $arg = null;
    foreach ($_SERVER['argv'] as $argument) {
        // if we are not trying to get params for an argument
        // look to see if we have a valid arg
        if (! $arg) {
            foreach ($supported_args as $supported_arg) {
                if (strncasecmp($supported_arg[0], $argument, strlen($supported_arg[0])) == 0) {
                    $arg = $supported_arg;
                    $holding_args = array();
                    break;
                }
            }

            // if none found, show help
            if (! $arg) {
                echo "Invalid arguments specified\n\n";
                show_help(null);
            }

        } else {
            // if we are trying to fill an argument, fill it
            $holding_args[] = $argument;
        }

        // if the argument is full, execute it
        // all arguments are functions, except config
        // which has to be in the global namespace
        if (count($holding_args) == $arg[1]) {
            if ($arg[2] == 'set_config') {
                $dpgOutputters = array();
                $dpgTransformers = array();


                if (! file_exists($holding_args[0])) {
                    echo "Invalid config file specified!\n";
                    exit;
                }

                require_once $holding_args[0];
                $config_found = true;

            } else {
                call_user_func($arg[2], $holding_args);
            }
            $arg = null;
        }
    }

    if ($arg) {
        echo "Arguments are incomplete!\n";
        exit;
    }
}

// ensure we have a base dir
if (!file_exists($dpgBaseDirectory)) {
    header('Content-type: text/plain');
    echo "ERROR:\n";
    echo "Base directory '{$dpgBaseDirectory}' not found.\n";
    exit;
}


/**
 * Shows a help message
 **/
function show_help($args)
{
    global $supported_args;

    echo "This is the Pelzini processor\n\nSupported arguments:\n";
    foreach ($supported_args as $arg) {
        echo str_pad($arg[0], 20), '  ', $arg[3], "\n";
    }

    exit;
}


?>
