<?php

namespace SitPHP\Formatters;

class StyleTag
{

    /**
     * @var
     */
    private $color;
    /**
     * @var
     */
    private $background_color;
    /**
     * @var bool
     */
    private $bold = false;
    /**
     * @var bool
     */
    private $underline = false;
    /**
     * @var bool
     */
    private $highlight = false;
    /**
     * @var bool
     */
    private $blink = false;


    /**
     * @param string $color
     * @return $this
     */
    function setColor(string $color): StyleTag
    {
        $this->color = $color;
        return $this;
    }

    /**
     * @return mixed
     */
    function getColor()
    {
        return $this->color;
    }

    /**
     * @param string $color
     * @return $this
     */
    function setBackgroundColor(string $color): StyleTag
    {
        $this->background_color = $color;
        return $this;
    }

    /**
     * @return mixed
     */
    function getBackgroundColor()
    {
        return $this->background_color;
    }

    /**
     * @param bool $bool
     * @return $this
     */
    function bold(bool $bool): StyleTag
    {
        $this->bold = $bool;
        return $this;
    }

    /**
     * @return bool
     */
    function isBold(): bool
    {
        return $this->bold;
    }

    /**
     * @param bool $bool
     * @return $this
     */
    function underline(bool $bool): StyleTag
    {
        $this->underline = $bool;
        return $this;
    }

    /**
     * @return bool
     */
    function isUnderlined(): bool
    {
        return $this->underline;
    }

    /**
     * @param bool $bool
     * @return $this
     */
    function blink(bool $bool): StyleTag
    {
        $this->blink = $bool;
        return $this;
    }

    /**
     * @return bool
     */
    function isBlinking(): bool
    {
        return $this->blink;
    }

    /**
     * @param bool $bool
     * @return $this
     */
    function highlight(bool $bool): StyleTag
    {
        $this->highlight = $bool;
        return $this;
    }

    /**
     * @return bool
     */
    function isHighlighted(): bool
    {
        return $this->highlight;
    }
}