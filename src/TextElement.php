<?php

namespace SitPHP\Formatters;

use InvalidArgumentException;

class TextElement
{
    /**
     * @var StyleTag
     */
    private StyleTag $style;
    /**
     * @var array
     */
    private array $content = [];

    /**
     * @param $content
     * @param $style
     */
    function __construct($content = null, $style = null)
    {

        if (isset($content)) {
            $this->addContent($content);
        }
        if (!isset($style)) {
            $style = new StyleTag();
        }
        $this->setStyle($style);
    }

    /**
     * @param $content
     * @return void
     */
    function addContent($content): void
    {
        if (!is_string($content) && !is_a($content, self::class)) {
            throw new InvalidArgumentException('Invalid $content type : expected string or instance of ' . self::class);
        }
        $this->content[] = $content;
    }

    /**
     * @param array $content
     * @return void
     */
    function setContent(array $content): void
    {
        $this->content = [];
        foreach ($content as $item) {
            $this->addContent($item);
        }
    }

    /**
     * @return array
     */
    function getContent(): array
    {
        return $this->content;
    }

    /**
     * @return string
     */
    function getText(): string
    {
        $text = '';
        foreach ($this->content as $content) {
            if (is_string($content)) {
                $text .= $content;
            } else {
                /** @var $content self */
                $text .= $content->getText();
            }
        }
        return $text;
    }

    /**
     * @param StyleTag $style
     * @return $this
     */
    function setStyle(StyleTag $style): TextElement
    {
        $this->style = $style;
        return $this;
    }

    /**
     * @return StyleTag
     */
    function getStyle(): StyleTag
    {
        return $this->style;
    }

    /**
     * @param string $color
     * @return $this
     */
    function setColor(string $color): TextElement
    {
        $this->style->setColor($color);
        return $this;
    }

    /**
     * @return string|null
     */
    function getColor(): ?string
    {
        return $this->style->getColor();
    }

    /**
     * @param string $color
     * @return $this
     */
    function setBackgroundColor(string $color): TextElement
    {
        $this->style->setBackgroundColor($color);
        return $this;
    }

    /**
     * @return string|null
     */
    function getBackgroundColor(): ?string
    {
        return $this->style->getBackgroundColor();
    }

    /**
     * @param bool $bool
     * @return $this
     */
    function bold(bool $bool): TextElement
    {
        $this->style->bold($bool);
        return $this;
    }

    /**
     * @return bool
     */
    function isBold(): bool
    {
        return $this->style->isBold();
    }

    /**
     * @param bool $bool
     * @return $this
     */
    function underline(bool $bool): TextElement
    {
        $this->style->underline($bool);
        return $this;
    }

    /**
     * @return bool
     */
    function isUnderlined(): bool
    {
        return $this->style->isUnderlined();
    }

    /**
     * @param bool $bool
     * @return $this
     */
    function blink(bool $bool): TextElement
    {
        $this->style->blink($bool);
        return $this;
    }

    /**
     * @return bool
     */
    function isBlinking(): bool
    {
        return $this->style->isBlinking();
    }

    /**
     * @param bool $bool
     * @return $this
     */
    function highlight(bool $bool): TextElement
    {
        $this->style->highlight($bool);
        return $this;
    }

    /**
     * @return bool
     */
    function isHighlighted(): bool
    {
        return $this->style->isHighlighted();
    }
}