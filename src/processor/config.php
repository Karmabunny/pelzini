<?php
/*
Copyright 2015 Josh Heidenreich

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
 * Contains the {@link Config} class
 *
 * @package Processor
 * @author Josh Heidenreich
 * @since 0.4
 **/

/**
 * Stores the processor configuration
 **/
class Config {
    protected $project_name;
    protected $project_code;
    protected $license_text;
    protected $transformers = array();
    protected $outputters = array();
    protected $base_directory;
    protected $exclude_directories = array();
    protected $docs_directory;


    /**
    * Load and validate a config file
    *
    * @param string $filename The filename to load
    * @return bool True on success, false on failure
    **/
    public function load($filename) {
        $dpgOutputters = array();
        $dpgTransformers = array();
        $dpgLanguages = array('php', 'js');

        require $filename;

        if (!isset($dpgProjectName)) {
            echo "ERROR:\nRequired config option '\$dpgProjectName' not set.\n";
            return false;
        }

        if (!isset($dpgProjectCode)) {
            echo "ERROR:\nRequired config option '\$dpgProjectCode' not set.\n";
            return false;
        }

        // Don't allow chars which will mess up the friendly urls.
        if (preg_match('/[^-_A-Za-z0-9]/', $dpgProjectCode)) {
            echo "ERROR:\nInvalid characters for '\$dpgProjectCode' specified.\n";
            echo "Valid characters are A-Z, a-z, 0-9, dash and underscore.\n";
            return false;
        }

        if (@count($dpgOutputters) == 0) {
            if (file_exists(__DIR__ . '/../database.config.php')) {
                $result = $this->loadSharedDatabaseConfig();
                if ($result === null) {
                    echo "ERROR:\nConfig file 'database.config.php' was found but could not be parsed.\n";
                    return false;
                } else {
                    $dpgOutputters[] = $result;
                }
            } else {
                echo "ERROR:\nRequired config option '\$dpgOutputters' not set and 'database.config.php' file not found.\n";
                return false;
            }
        }

        if (!$dpgBaseDirectory or !file_exists($dpgBaseDirectory)) {
            echo "ERROR:\nRequired config option '\$dpgBaseDirectory' not set or directory does not exist.\n";
            return false;
        }

        $this->project_name = $dpgProjectName;
        $this->project_code = $dpgProjectCode;
        $this->license_text = $dpgLicenseText;
        $this->transformers = $dpgTransformers;
        $this->outputters = $dpgOutputters;
        $this->base_directory = $dpgBaseDirectory;
        $this->exclude_directories = $dpgExcludeDirectories;
        $this->docs_directory = $dpgDocsDirectory;
        $this->languages = $dpgLanguages;

        return true;
    }


    /**
     * If no outputters have been specified, load the common "database.config.php" file
     * and use the settings in there to create one
     *
     * @return Outputter If a outputter could be parsed from the configuration
     * @return null On error
     */
    private function loadSharedDatabaseConfig() {
        require __DIR__ . '/../database.config.php';

        switch ($dvgDatabaseEngine) {
            case 'mysql':
                return new MysqlOutputter(
                    $dvgDatabaseSettings['username'],
                    $dvgDatabaseSettings['password'],
                    $dvgDatabaseSettings['server'],
                    $dvgDatabaseSettings['name']
                );

            case 'postgresql':
                return new PostgresqlOutputter(
                    $dvgDatabaseSettings['username'],
                    $dvgDatabaseSettings['password'],
                    $dvgDatabaseSettings['server'],
                    $dvgDatabaseSettings['name']
                );

            case 'sqlite':
                return new SqliteOutputter(
                    $dvgDatabaseSettings['filename']
                );

            default:
                return null;
        }
    }


    /**
    * @return string The name of the project
    **/
    public function getProjectName() {
        return $this->project_name;
    }


    /**
    * @return string The project code, to allow for multiple projects per db
    **/
    public function getProjectCode() {
        return $this->project_code;
    }


    /**
    * @return string The license text to show in the viewer
    **/
    public function getLicenseText() {
        return $this->license_text;
    }


    /**
    * @return array Transformer classes
    **/
    public function getTransformers() {
        return $this->transformers;
    }


    /**
    * @return array Outputter classes
    **/
    public function getOutputters() {
        return $this->outputters;
    }


    /**
    * @return string The base directory for indexing
    **/
    public function getBaseDirectory() {
        return $this->base_directory;
    }


    /**
    * @return string The base directory for indexing
    **/
    public function getExcludeDirectories() {
        return $this->exclude_directories;
    }


    /**
    * @return string The directory containing documentation
    **/
    public function getDocsDirectory() {
        return $this->docs_directory;
    }

}
