<?php

namespace SitPHP\Formatters;

use Exception;
use LogicException;
use SitPHP\Formatters\Formatters\CliFormatter;

class FormatterManager
{

    private $formatters = [];

    /**
     * Parser manager constructor
     *
     * @throws Exception
     */
    function __construct()
    {
        $this->setFormatter('cli', CliFormatter::class);
    }

    /**
     * Return new formatter instance
     *
     * @param string $formatter
     * @return Formatter
     * @throws Exception
     * @throws Exception
     */
    function formatter(string $formatter){
        $style = new Formatter($this, $formatter);
        return $style;
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
        $existing_formatter = array_search($class, $this->formatters);
        if($existing_formatter && $existing_formatter != $name){
            throw new LogicException('Formatter '.$class.' is already set with name "'.$name.'"');
        }
        $this->formatters[$name] = $class;
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
        return $this->formatters[$name] ?? null;
    }

    /**
     * Remove formatter
     *
     * @param string $name
     * @throws Exception
     */
    function removeFormatter(string $name){
        unset($this->formatters[$name]);
    }
}