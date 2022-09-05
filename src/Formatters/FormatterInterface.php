<?php

namespace SitPHP\Formatters\Formatters;

use SitPHP\Formatters\TextElement;

interface FormatterInterface
{
    static function format(TextElement $text);

    static function unFormat(string $text);
}