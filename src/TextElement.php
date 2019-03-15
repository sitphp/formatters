<?php

namespace SitPHP\Styles;

use SitPHP\Services\ServiceTrait;

class TextElement
{
    use ServiceTrait;

    protected static $services = [
        'style' => Style::class,
        'parser' => Parser::class
    ];

    // User properties
    /** @var Style $style */
    protected $style;
    protected $style_name;
    protected $content = [];
    protected $text_color;
    protected $background_color;
    protected $bold = false;
    protected $underline = false;
    protected $highlight = false;
    protected $blink = false;

    function __construct($content = null, $style = null)
    {

        if(isset($content)){
            $this->addContent($content);
        }
        if(!isset($style)){
            $style = self::getServiceInstance('style');
        }
        $this->setStyle($style);
    }

    function addContent($content){
        if(!is_string($content) && !is_a($content, self::class)){
            throw new \InvalidArgumentException('Invalid $content type : expected string or instance of '.self::class);
        }
        $this->content[] = $content;
    }

    function setContent(array $content){
        $this->content = [];
        foreach($content as $item){
            $this->addContent($item);
        }
    }

    function getContent(){
        return $this->content;
    }

    function getText()
    {
        $text = '';
        foreach ($this->content as $content){
            if(is_string($content)){
                $text .= $content;
            } else {
                /** @var $content self */
                $text .= $content->getText();
            }
        }
        return $text;
    }

    function setStyle(Style $style)
    {
        $this->style = $style;
        return $this;
    }

    function getStyle()
    {
        return $this->style;
    }

    function setColor(string $color)
    {
        $this->style->setColor($color);
        return $this;
    }

    function getColor()
    {
        return $this->style->getColor();
    }

    function setBackgroundColor(string $color)
    {
        $this->style->setBackgroundColor($color);
        return $this;
    }

    function getBackgroundColor()
    {
        return $this->style->getBackgroundColor();
    }

    function bold(bool $bool)
    {
        $this->style->bold($bool);
        return $this;
    }

    function isBold()
    {
        return $this->style->isBold();
    }

    function underline(bool $bool)
    {
        $this->style->underline($bool);
        return $this;
    }

    function isUnderlined()
    {
        return $this->style->isUnderlined();
    }

    function blink(bool $bool)
    {
        $this->style->blink($bool);
        return $this;
    }

    function isBlinking()
    {
        return $this->style->isBlinking();
    }

    function highlight(bool $bool)
    {
        $this->style->highlight($bool);
        return $this;
    }

    function isHighlighted()
    {
        return $this->style->isHighlighted();
    }
}