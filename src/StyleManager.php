<?php

namespace SitPHP\Styles;

use Exception;
use LogicException;
use SitPHP\Styles\Formatters\CliFormatter;

class StyleManager
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
     * Return new parser instance
     *
     * @param string $formatter
     * @return Style
     * @throws Exception
     * @throws Exception
     */
    function style(string $formatter = null){
        $style = new Style($this);
        if($formatter !== null){
            $style->setFormatter($formatter);
        }
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