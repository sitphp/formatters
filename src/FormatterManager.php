<?php

namespace SitPHP\Formatters;

use Exception;
use LogicException;
use SitPHP\Formatters\Formatters\CliFormatter;
use SitPHP\Formatters\Formatters\TextFormatter;

class FormatterManager
{

    private $formatter_classes = [];
    private $formatters = [];

    /**
     * Parser manager constructor
     *
     * @throws Exception
     */
    function __construct()
    {
        $this->setFormatter('cli', CliFormatter::class);
        $this->setFormatter('text', TextFormatter::class);
    }

    /**
     * Return new formatter instance
     *
     * @param string $name
     * @return Formatter
     * @throws Exception
     * @throws Exception
     */
    function getFormatter(string $name) : Formatter
    {
        if(!isset($this->formatters[$name])){
            $this->formatters[$name] = new Formatter($this, $name);
        }
        return $this->formatters[$name];
    }

    /**
     * Set formatter
     *
     * @param string $name
     * @param string $class
     * @throws Exception
     */
    function setFormatter(string $name, string $class)
    {
        $existing_formatter = array_search($class, $this->formatter_classes);
        if ($existing_formatter && $existing_formatter != $name) {
            throw new LogicException('Formatter ' . $class . ' is already set with name "' . $name . '"');
        }
        $this->formatter_classes[$name] = $class;
    }

    /**
     * Return formatter
     *
     * @param string $name
     * @return mixed|null
     * @throws Exception
     */
    function getFormatterClass(string $name)
    {
        return $this->formatter_classes[$name] ?? null;
    }

    /**
     * Remove formatter
     *
     * @param string $name
     * @throws Exception
     */
    function removeFormatter(string $name)
    {
        unset($this->formatters[$name]);
        unset($this->formatter_classes[$name]);
    }
}