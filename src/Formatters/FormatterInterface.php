<?php

namespace SitPHP\Styles\Formatters;

use SitPHP\Styles\TextElement;

interface FormatterInterface
{
    static function format(TextElement $text);
    static function unFormat(string $text);
}