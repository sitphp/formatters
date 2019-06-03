<?php

namespace SitPHP\Formatters\Formatters;

use InvalidArgumentException;
use SitPHP\Formatters\TextElement;

class CliFormatter implements FormatterInterface
{
    static $text_colors = [
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

    static $background_colors = [
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

    static function format(TextElement $text, $previous_style = null){
        $formatted = '';
        $style = self::makeStyleCode($text);
        if($style !== null){
            $formatted .= $style;
        }
        foreach($text->getContent() as $item){
            if(is_string($item)){
                $formatted .= $item;
            } else {
                $formatted .= self::format($item, $style);
            }
        }
        if($style !== null) {
            $formatted .= "\033[0m";
        }
        if(isset($previous_style)){
            $formatted .= $previous_style;
        }
        return $formatted;
    }

    static function unFormat(string $text){
        return preg_replace('#\\033\[[0-9;]+m#','', $text);
    }

    protected static function makeStyleCode(TextElement $text){
        $format_codes = [];
        $text_color = $text->getColor();
        if($text_color !== null){
            $color_code = self::getTextColorMapping($text_color);
            if ($color_code === null) {
                throw new InvalidArgumentException('Undefined "'.$text_color.'"" text color');
            }
            $format_codes[] =  $color_code;
        }
        $background_color = $text->getBackgroundColor();
        if($background_color !== null){
            $color_code = self::getBackgroundColorMapping($background_color);
            if ($color_code === null) {
                throw new InvalidArgumentException('Undefined "'.$background_color.'" background color');
            }
            $format_codes[] = $color_code;
        }
        if($text->isBold()){
            $format_codes[] = '1';
        }
        if($text->isUnderlined()){
            $format_codes[] = '4';
        }
        if($text->isBlinking()){
            $format_codes[] = '5';
        }
        if($text->isHighlighted()){
            $format_codes[] = '7';
        }

        $style_code = !empty($format_codes) ? "\033[".implode(';',$format_codes)."m" : null;
        return $style_code;
    }

    protected static function getTextColorMapping($color){
        if(filter_var($color, FILTER_VALIDATE_INT)){
            return in_array($color, self::$text_colors) ? $color : null;
        }
        return self::$text_colors[$color] ?? null;
    }

    protected static function getBackgroundColorMapping($color){
        if(filter_var($color, FILTER_VALIDATE_INT)){
            return in_array($color, self::$background_colors) ? $color : null;
        }
        return self::$background_colors[$color] ?? null;
    }
}