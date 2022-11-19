<?php

namespace SitPHP\Formatters\Formatters;

use Exception;
use InvalidArgumentException;
use SitPHP\Formatters\TextElement;

class CliFormatter extends Formatter
{
    /**
     * @var string[]
     */
    static array $text_colors = [
        'black' => '30',
        'red' => '31',
        'green' => '32',
        'yellow' => '33',
        'blue' => '34',
        'purple' => '35',
        'cyan' => '36',
        'light_grey' => '37',
        'dark_grey' => '90',
        'light_red' => '91',
        'light_green' => '92',
        'light_yellow' => '93',
        'light_blue' => '94',
        'pink' => '95',
        'light_cyan' => '96',
        'white' => '97',
    ];

    /**
     * @var string[]
     */
    static array $background_colors = [
        'black' => '40',
        'red' => '41',
        'green' => '42',
        'yellow' => '43',
        'blue' => '44',
        'purple' => '45',
        'cyan' => '46',
        'light_grey' => '47',
        'dark_grey' => '100',
        'light_red' => '101',
        'light_green' => '102',
        'light_yellow' => '103',
        'light_blue' => '104',
        'pink' => '105',
        'light_cyan' => '106',
        'white' => '107',
    ];


    function format(string $message, int $width = null): string
    {
        $parsed = $this->parse($message, $width);
        if ($parsed === null) {
            return '';
        }
        return $this->doFormat($parsed);
    }

    /**
     * @param TextElement $message
     * @param $previous_style
     * @return string
     * @throws Exception
     */
    protected function doFormat(TextElement $message, $previous_style = null): string
    {
        $formatted = '';
        $style = self::makeStyleCode($message);
        if ($style !== null) {
            $formatted .= $style;
        }
        foreach ($message->getContent() as $item) {
            if (is_string($item)) {
                $formatted .= $item;
            } else {
                $formatted .= $this->doFormat($item, $style);
            }
        }
        if ($style !== null) {
            $formatted .= "\033[0m";
        }
        if (isset($previous_style)) {
            $formatted .= $previous_style;
        }
        return $formatted;
    }

    /**
     * @param string $message
     * @return string
     */
    function unFormat(string $message): string
    {
        return preg_replace('#\\033\[[0-9;]+m#', '', $message);
    }

    /**
     * @param TextElement $text
     * @return string|null
     */
    protected static function makeStyleCode(TextElement $text): ?string
    {
        $format_codes = [];
        $text_color = $text->getColor();
        if ($text_color !== null) {
            $color_code = self::getTextColorMapping($text_color);
            if ($color_code === null) {
                throw new InvalidArgumentException('Undefined "' . $text_color . '"" text color');
            }
            $format_codes[] = $color_code;
        }

        $background_color = $text->getBackgroundColor();
        if ($background_color !== null) {
            $color_code = self::getBackgroundColorMapping($background_color);
            if ($color_code === null) {
                throw new InvalidArgumentException('Undefined "' . $background_color . '" background color');
            }
            $format_codes[] = $color_code;
        }

        if ($text->isBold()) {
            $format_codes[] = '1';
        }
        if ($text->isUnderlined()) {
            $format_codes[] = '4';
        }
        if ($text->isBlinking()) {
            $format_codes[] = '5';
        }
        if ($text->isHighlighted()) {
            $format_codes[] = '7';
        }

        return !empty($format_codes) ? "\033[" . implode(';', $format_codes) . "m" : null;
    }

    /**
     * @param $color
     * @return string|null
     */
    protected static function getTextColorMapping($color): ?string
    {
        if ($color[0] == '#') {
            list($r, $g, $b) = self::hexToRGB($color);
            return '38;2;' . $r . ';' . $g . ';' . $b;
        }
        if (filter_var($color, FILTER_VALIDATE_INT)) {
            return in_array($color, self::$text_colors) ? $color : null;
        }
        return self::$text_colors[$color] ?? null;
    }

    /**
     * @param $color
     * @return string|null
     */
    protected static function getBackgroundColorMapping($color): ?string
    {
        if ($color[0] == '#') {
            list($r, $g, $b) = self::hexToRGB($color);
            return '48;2;' . $r . ';' . $g . ';' . $b;
        }
        if (filter_var($color, FILTER_VALIDATE_INT)) {
            return in_array($color, self::$background_colors) ? $color : null;
        }
        return self::$background_colors[$color] ?? null;
    }

    /**
     * @param string $color
     * @return array
     */
    protected static function hexToRGB(string $color): array
    {
        $r = hexdec(substr($color, 1, 2));
        $g = hexdec(substr($color, 3, 2));
        $b = hexdec(substr($color, 5));

        return [$r, $g, $b];
    }
}