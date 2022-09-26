<?php

namespace SitPHP\Formatters\Formatters;


use SitPHP\Formatters\TextElement;

class TextFormatter extends Formatter
{

    /**
     * @param TextElement $message
     * @return string
     */
    function doFormat(TextElement $message): string
    {
        return $message->getText();
    }

    /**
     * @param string $message
     * @return string
     */
    function doUnFormat(string $message): string
    {
        return $message;
    }
}