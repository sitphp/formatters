<?php

namespace SitPHP\Styles;


class TagStyle
{

    private $color;
    private $background_color;
    private $bold = false;
    private $underline = false;
    private $highlight = false;
    private $blink = false;


    function setColor(string $color)
    {
        $this->color = $color;
        return $this;
    }

    function getColor()
    {
        return $this->color;
    }

    function setBackgroundColor(string $color)
    {
        $this->background_color = $color;
        return $this;
    }

    function getBackgroundColor()
    {
        return $this->background_color;
    }

    function bold(bool $bool)
    {
        $this->bold = $bool;
        return $this;
    }

    function isBold()
    {
        return $this->bold;
    }

    function underline(bool $bool)
    {
        $this->underline = $bool;
        return $this;
    }

    function isUnderlined()
    {
        return $this->underline;
    }

    function blink(bool $bool)
    {
        $this->blink = $bool;
        return $this;
    }

    function isBlinking()
    {
        return $this->blink;
    }

    function highlight(bool $bool)
    {
        $this->highlight = $bool;
        return $this;
    }

    function isHighlighted()
    {
        return $this->highlight;
    }
}