<?php

namespace SitPHP\Styles;

use Exception;
use LogicException;
use SitPHP\Styles\Formatters\CliFormatter;

class ParserManager
{

    private $formatters_alias = [];
    private $tags_styles = [];

    /**
     * Parser manager constructor
     *
     * @throws Exception
     */
    function __construct()
    {
        $this->setFormatterAlias('cli', CliFormatter::class);
    }

    /**
     * Set formatter alias
     *
     * @param string $name
     * @param string $class
     * @throws Exception
     */
    function setFormatterAlias(string $name, string $class)
    {
        $alias = array_search($class, $this->formatters_alias);
        if($alias && $alias != $name){
            throw new LogicException('Formatter '.$class.' is already set with alias "'.$alias.'"');
        }
        $this->formatters_alias[$name] = $class;
    }

    /**
     * Return formatter alias
     *
     * @param string $name
     * @return mixed|null
     * @throws Exception
     */
    function getFormatterAlias(string $name)
    {
        return $this->formatters_alias[$name] ?? null;
    }

    /**
     * Remove formatter alias
     *
     * @param string $name
     * @throws Exception
     */
    function removeFormatterAlias(string $name){
        unset($this->formatters_alias[$name]);
    }


    /**
     * Build a new style
     *
     * @param string $name
     * @return Style
     * @throws Exception
     */
    function buildTagStyle(string $name)
    {
        $style = new Style();
        $this->setTagStyle($name, $style);
        return $style;
    }

    /**
     * Set a built style
     *
     * @param $name
     * @param Style $style
     * @throws Exception
     */
    protected function setTagStyle($name, Style $style)
    {
        $this->tags_styles[$name] = $style;
    }

    /**
     * Return a built style
     *
     * @param string $name
     * @return Style
     * @throws Exception
     */
    function getTagStyle(string $name)
    {
        return $this->tags_styles[$name] ?? null;
    }

    /**
     * Remove tag style
     *
     * @param string $name
     */
    function removeTagStyle(string $name)
    {
        unset($this->tags_styles[$name]);
    }

    /**
     * Return new parser instance
     *
     * @param string $formatter
     * @return Parser
     * @throws Exception
     * @throws Exception
     */
    function parser(string $formatter = 'cli'){
        $parser = new Parser();
        $parser->setManager($this);
        $parser->setFormatter($formatter);
        foreach($this->tags_styles as $name => $tag_style){
            $parser->setTagStyle($name, $tag_style);
        }
        return $parser;
    }
}