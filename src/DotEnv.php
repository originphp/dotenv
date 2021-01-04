<?php

/**
 * OriginPHP Framework
 * Copyright 2018 - 2021 Jamiel Sharief.
 *
 * Licensed under The MIT License
 * The above copyright notice and this permission notice shall be included in all copies or substantial
 * portions of the Software.
 *
 * @copyright   Copyright (c) Jamiel Sharief
 * @link        https://www.originphp.com
 * @license     https://opensource.org/licenses/mit-license.php MIT License
 */
declare(strict_types = 1);
namespace Origin\DotEnv;

/**
 * A quick and simple .env file loader and parser
 * - Single line comments
 * - Comments after env e.g. USERNAME=foo # this is a comment
 * - Values can be quoted with " or '
 * - Multilines can parsed
 */
use \Exception;
use \InvalidArgumentException;

class DotEnv
{
    /**
     * Loads an .env file
     *
     * @return array
     */
    public function load(string $directory, string $file = '.env') : array
    {
        $file = $directory  . DIRECTORY_SEPARATOR . $file;
        if (is_readable($file)) {
            $lines = file($file);
            $env = $this->parse($lines);
            foreach ($env as $key => $value) {
                $this->env($key, $value);
            }

            return $env;
        }
        throw new InvalidArgumentException(sprintf('%s could not be found.', $file)); # Security
    }

    /**
     * Wraps the env setting
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    protected function env(string $key, $value) : void
    {
        $_ENV[$key] = $value;
    }

    /**
     * Processes the parsed lines
     *
     * @param array $lines
     * @return array
     */
    protected function parse(array $lines) : array
    {
        $env = [];
        $capture = false;
        $quotes = '"';
        foreach ($lines as $row) {
            $row = trim($row);
            if ($row === '' || substr($row, 0, 1) === '#') {
                continue;
            }
            
            if (substr($row, 0, 7) === 'export ') {
                $row = substr($row, 7);
            }

            # Comment Stripper
            $row = preg_replace('% # .*%', '', $row);
            
            # Parse
            if (strpos($row, '=') !== false) {
                list($key, $value) = explode('=', $row, 2);
                $key = trim(strtoupper($key));
                $env[$key] = $this->value($value);
            }

            # Capture Multiline
            if ($capture && substr($row, -1) === $quotes) {
                $env[$capture] .= "\n". rtrim($row, '"');
                $capture = false;
            } elseif ($capture) {
                $env[$capture] .= "\n". $row;
            } elseif (in_array(substr($value, 0, 1), ['"',"'"]) && ! in_array(substr($row, -1), ['"',"'"])) {
                $capture = $key;
                $quotes = substr($value, 0, 1);
            }
        }

        if ($capture) {
            throw new Exception(sprintf('Invalid value for `%s` ', $capture));
        }
        # Remove final quotes
        foreach ($env as $key => $value) {
            $env[$key] = is_string($value) ? trim($value, "\"'") : $value;
        }

        return $env;
    }

    /**
     * Prepares a value that has been parsed
     *
     * @param mixed $value
     * @return mixed
     */
    protected function value($value)
    {
        if ($value === 'null') {
            return null;
        }
        if ($value === 'true' || $value === 'false') {
            return $value === 'true' ? true : false;
        }
        if (is_numeric($value)) {
            return is_int($value) ?  intval($value) : floatval($value);
        }

        $value = str_replace('\n', "\n", $value);

        return is_string($value) ?  trim($value) : $value;
    }
}
