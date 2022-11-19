<?php

namespace SitPHP\Formatters\Formatters;

use Exception;

class TextFormatter extends Formatter
{

    /**
     * @param string $message
     * @param int|null $width
     * @return string
     * @throws Exception
     */
    function format(string $message, int $width = null): string
    {
        $parsed = $this->parse($message, $width);
        if ($parsed === null) {
            return '';
        }
        return $parsed->getText();
    }

    /**
     * @param string $message
     * @return string
     */
    function unFormat(string $message): string
    {
        return $message;
    }
}