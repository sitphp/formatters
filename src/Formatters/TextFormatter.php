<?php

namespace SitPHP\Formatters\Formatters;


use SitPHP\Formatters\TextElement;

class TextFormatter implements FormatterInterface
{

    static function format(TextElement $text)
    {
        return $text->getText();
    }

    static function unFormat(string $text)
    {
        return $text;
    }
}