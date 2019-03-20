<?php

namespace SitPHP\Styles;

use SitPHP\Services\ServiceTrait;
use SitPHP\Styles\Formatters\CliFormatter;
use SitPHP\Styles\Formatters\FormatterInterface;

class Parser
{
    use ServiceTrait;

    protected static $services = [
        'style' => Style::class,
        'text_element' => TextElement::class
    ];

    // Internal static properties
    protected static $initialized = false;
    protected static $style_methods_mapping = [];

    // User static properties
    protected static $formatters_alias = [];

    /** @var FormatterInterface $formatter */
    protected $formatter;
    protected $tags_styles = [];


    /**
     * Initialize the class
     *
     * @throws \Exception
     */
    protected static function init()
    {
        if (self::$initialized) {
            return;
        }
        self::$initialized = true;

        self::setFormatterAlias('cli', CliFormatter::class);

        self::setStyleMethodMapping('color', 'setColor');
        self::setStyleMethodMapping('background-color', 'setBackgroundColor');
        self::setStyleMethodMapping('bold', 'bold');
        self::setStyleMethodMapping('underline', 'underline');
        self::setStyleMethodMapping('blink', 'blink');
        self::setStyleMethodMapping('highlight', 'highlight');
    }

    /**
     * Set formatter alias
     *
     * @param string $name
     * @param string $class
     * @throws \Exception
     */
    static function setFormatterAlias(string $name, string $class)
    {
        self::init();
        self::$formatters_alias[$name] = $class;
    }

    /**
     * Return formatter alias
     *
     * @param string $name
     * @return mixed|null
     * @throws \Exception
     */
    static function getFormatterAlias(string $name)
    {
        self::init();
        return self::$formatters_alias[$name] ?? null;
    }

    /**
     * Remove formatter alias
     *
     * @param string $name
     * @throws \Exception
     */
    static function removeFormatterAlias(string $name){
        self::init();
        unset(self::$formatters_alias[$name]);
    }

    static function reset(){
        self::$formatters_alias = [];
        self::$style_methods_mapping = [];
        self::$initialized = false;
    }

    /**
     * Parser constructor.
     * @param string|null $formatter
     * @throws \Exception
     */
    function __construct(string $formatter = null)
    {
        self::init();
        if($formatter !== null){
            $this->setFormatter($formatter);
        }
    }

    function setFormatter(string $formatter){
        $formatter_class = self::expectFormatter($formatter);
        $this->formatter = $formatter_class;
    }

    function getFormatter(){
        return $this->formatter;
    }


    protected static function expectFormatter($formatter)
    {
        if (null !== self::getFormatterAlias($formatter)) {
            $formatter = self::getFormatterAlias($formatter);
        }
        if (!class_exists($formatter)) {
            throw new \InvalidArgumentException('Invalid formatter "' . $formatter . '" : expected alias or existing class');
        }
        if (!is_subclass_of($formatter, FormatterInterface::class)) {
            throw new \InvalidArgumentException('Invalid formatter class "' . $formatter . '" : expected subclass of ' . FormatterInterface::class);
        }
        return $formatter;
    }

    /**
     * Format string with formatter
     *
     * @param string $message
     * @param int|null $width
     * @return mixed
     * @throws \Exception
     */
    function format(string $message, int $width = null)
    {
        if(null === $formatter = $this->getFormatter()){
            throw new \LogicException('Formatter should be set.');
        }
        $parsed = $this->parse($message, $width);
        return $formatter::format($parsed);
    }

    /**
     * Call removeFormatting on given formatter
     *
     * @param string $message
     * @return mixed
     * @throws \Exception
     */
    function removeFormatting(string $message)
    {
        if(null === $formatter = $this->getFormatter()){
            throw new \LogicException('Formatter should be set.');
        }
        $formatter = $this->getFormatter();
        return $formatter::removeFormatting($message);
    }

    /**
     * Build a new style
     *
     * @param string $name
     * @return Style
     * @throws \Exception
     */
    function buildTagStyle(string $name)
    {
        /* @var Style $style */
        $style = self::getServiceInstance('style');
        $this->setTagStyle($name, $style);
        return $style;
    }

    /**
     * Return a built style
     *
     * @param string $name
     * @return Style
     * @throws \Exception
     */
    function getTagStyle(string $name)
    {
        return $this->tags_styles[$name] ?? null;
    }

    function removeTagStyle(string $name)
    {
        unset($this->tags_styles[$name]);
    }


    /**
     * @param string $text
     * @return string|null
     * @throws \Exception
     */
    function toString(string $text)
    {
        return $this->parse($text)->getText();
    }

    /**
     * Parse a text to an OutputText object
     *
     * @param string $text
     * @param int|null $width
     * @return TextElement
     * @throws \Exception
     */
    function parse(string $text, int $width = null)
    {
        $prepared_text = self::split($text, $width);
        $dom = new \DOMDocument();
        // Should never happen
        try {
            $dom->loadXML('<node>' . $prepared_text . '</node>');
        } catch (\Exception $e) {
            throw new \InvalidArgumentException('Text "' . $text . '" could not be parsed : text should be in XML format');
        }
        $text = self::domToOutputText($dom->documentElement);

        return $text;
    }

    /**
     * Transform dom element to text element
     *
     * @param \DOMElement $dom
     * @param \SitPHP\Styles\TextElement $text
     * @throws \Exception
     * @return \SitPHP\Styles\TextElement
     */
    protected function domToOutputText(\DOMElement $dom, TextElement $text = null)
    {
        if (!isset($text)) {
            /** @var TextElement $text */
            $text = self::getServiceInstance('text_element');
        }
        foreach ($dom->childNodes as $node) {
            switch ($node->nodeName) {
                case '#text':
                    $text->addContent($node->nodeValue);
                    break;
                case 'cs':
                    $child_content = self::getServiceInstance('text_element');
                    if (isset($node->attributes)) {
                        self::applyNodeAttributes($child_content, $node->attributes);
                    }
                    $text->addContent($child_content);
                    $this->domToOutputText($node, $child_content);
                    break;
                default:
                    $text_style = $this->getTagStyle($node->nodeName);
                    // Should never happen
                    if ($text_style === null) {
                        throw new \Exception('Undefined style "' . $node->nodeName . '"');
                    }
                    $child_content = self::getServiceInstance('text_element');
                    $child_content->setStyle($text_style);
                    if (isset($node->attributes)) {
                        self::applyNodeAttributes($child_content, $node->attributes);
                    }
                    $text->addContent($child_content);
                    self::domToOutputText($node, $child_content);
                    break;
            }
        }
        return $text;

    }

    /**
     * Split a string with tags in multiple lines with proper tags
     *
     * @param string $content
     * @param int|null $width
     * @param bool $encode_special_chars
     * @return string
     * @throws \Exception
     */
    function split(string $content, int $width = null, bool $encode_special_chars = true)
    {
        if (isset($width) && $width < 0) {
            throw new \InvalidArgumentException('Invalid $width argument : expected positive int');
        }
        $opened_tags = [];
        $content_substr_start = 0;
        $current_line_char_count = 0;
        $splitted = '';

        preg_match_all('#\\\\?<\/?\s*([a-z1-9]+)\s*([^<>]*?)\s*>#i', $content, $matches, PREG_OFFSET_CAPTURE);

        foreach ($matches[0] as $match_key => $match) {

            $match_tag = $match[0];
            $match_pos = $match[1];

            // If closing tag doesnt match previously opened tag or is unknown tag
            if (($match_tag[0] == '\\')
                || ($match_tag[1] === '/' && $matches[1][$match_key][0] !== end($opened_tags)['name'])
                || ($matches[1][$match_key][0] != 'cs' && $this->getTagStyle($matches[1][$match_key][0]) === null)
            ) {
                continue;
            }
            // If tag is escaped
            $text_before = substr($content, $content_substr_start, $match_pos - $content_substr_start);
            // Split text before tag
            $splitted .= self::splitText($text_before, $current_line_char_count, $opened_tags, $width, $encode_special_chars);

            $content_substr_start = $match_pos + strlen($match_tag);

            // Update matched tags
            if ($match_tag[1] === '/') {
                array_pop($opened_tags);
            } else {
                $opened_tags[] = [
                    'name' => $matches[1][$match_key][0],
                    'attributes' => $matches[2][$match_key][0]
                ];
            }
        }
        // Split after last tag
        $text_after = substr($content, $content_substr_start);
        $splitted .= self::splitText($text_after, $current_line_char_count, $opened_tags, $width, $encode_special_chars);

        return $splitted;
    }

    /**
     * Set a built style
     *
     * @param $name
     * @param Style $style
     * @throws \Exception
     */
    protected function setTagStyle($name, Style $style)
    {
        $this->tags_styles[$name] = $style;
    }

    /**
     * Set style method mapping
     *
     * @param string $style
     * @param string $method
     */
    protected static function setStyleMethodMapping(string $style, string $method)
    {
        self::$style_methods_mapping[$style] = $method;
    }

    protected static function applyNodeAttributes(TextElement $text, \DOMNamedNodeMap $node_attributes)
    {
        $attributes = [];
        foreach ($node_attributes as $name => $attribute) {
            $attributes[$name] = $attribute->nodeValue;
        }
        self::applyArrayAttributes($text, $attributes);
    }

    protected static function applyArrayAttributes(TextElement $styled_text, array $style)
    {
        foreach ($style as $key => $value) {
            if (empty($value)) {
                continue;
            }
            if ($key === 'style') {
                $style_parts = explode(';', $value);
                $style_parts = array_map('trim', $style_parts);
                foreach ($style_parts as $style_part) {
                    $style_item = explode(':', $style_part);
                    $item_key = $style_item[0];
                    $item_value = isset($style_item[1]) ? $style_item[1] : true;
                    self::applyStyleItem($styled_text, $item_key, $item_value);
                }
            } else {
                self::applyStyleItem($styled_text, $key, $value);
            }
        }
    }

    protected static function applyStyleItem($styled_text, $key, $value)
    {
        if (!isset(self::$style_methods_mapping[$key])) {
            throw new \InvalidArgumentException('Undefined style ' . $key);
        }
        $style_method = self::$style_methods_mapping[$key];
        $styled_text->$style_method($value);
    }

    /**
     * @param string $text
     * @param $current_line_char_count
     * @param array $opened_tags
     * @param int|null $width
     * @param bool $encode_special_chars
     * @return bool|string
     */
    protected static function splitText(string $text, &$current_line_char_count, $opened_tags = [], int $width = null, bool $encode_special_chars = true)
    {

        $text = strtr($text, ['\<' => '<']);
        if ($encode_special_chars) {
            $text = htmlspecialchars($text);
        }
        if ($width === null) {
            return self::wrapTextWithTags($text, $opened_tags);
        }
        if ($text === '') {
            return $text;
        }
        if ($text == "\n") {
            return $text;
        }
        $splitted = '';
        // If text starts with a repetition of 1 or more "\n"
        if (preg_match("#^(\n)(\\1)*#", $text, $matches)) {
            if (count($matches) === 2) {
                $splitted .= "\n";
                $current_line_char_count = 0;
            }
            $text = substr($text, 1);
        }

        $text_parts = explode("\n", $text);
        foreach ($text_parts as $text_key => $text_part) {
            // Text part is empty which means its a line break
            if ($text_part === '') {
                $splitted .= "\n";
                $current_line_char_count = 0;
                continue;
            }
            // New line
            if ($text_key > 0) {
                $splitted .= "\n";
                $current_line_char_count = 0;
            }

            // Undefined width, just wrap every line with tags
            if ($width === 0) {
                $splitted .= self::wrapTextWithTags($text_part, $opened_tags);
                continue;
            }

            // Current line is not full, fill it
            if ($current_line_char_count > 0 && $current_line_char_count < $width) {
                $splitted_part = mb_substr($text_part, 0, $width - $current_line_char_count);
                $text_part = mb_substr($text_part, $width - $current_line_char_count);
                $splitted .= self::wrapTextWithTags($splitted_part, $opened_tags);
                $current_line_char_count += mb_strlen($splitted_part);

                // Nothing left to add
                if ($text_part === '') {
                    continue;
                }
            }

            // End of the line reached
            if ($current_line_char_count == $width) {
                $splitted .= "\n";
                $current_line_char_count = 0;
            }

            // Resolve text part lines
            $text_part_length = mb_strlen($text_part);
            $text_part_lines = ceil($text_part_length / $width);
            $text_part_substr_start = 0;
            for ($i = 1; $i <= $text_part_lines; $i++) {
                if ($i > 1) {
                    $splitted .= "\n";
                    $current_line_char_count = 0;
                }
                // Last line
                if ($i == $text_part_lines) {
                    $last_line_text = mb_substr($text_part, $text_part_substr_start);
                    $current_line_char_count = mb_strlen($last_line_text);
                    $split_part = $last_line_text;
                } // Other lines
                else {
                    $split_part = mb_substr($text_part, $text_part_substr_start, $width);
                    $text_part_substr_start += $width;
                }
                $splitted .= self::wrapTextWithTags($split_part, $opened_tags);
            }
        }
        return $splitted;
    }


    /**
     * Return string of opening tags from tags array
     *
     * @param array $tags
     * @return string
     */
    protected static function resolveOpenTags(array $tags)
    {
        $open_tags = '';
        foreach ($tags as $tag) {
            $open_tags .= '<' . $tag['name'];
            if (!empty($tag['attributes'])) {
                $open_tags .= ' ' . $tag['attributes'];
            }
            $open_tags .= '>';
        }
        return $open_tags;
    }

    /**
     * Return string of closing tags from tags array
     *
     * @param array $tags
     * @return string
     */
    protected static function resolveCloseTags(array $tags)
    {
        $close_tags = '';
        foreach (array_reverse($tags) as $tag) {
            $close_tags .= '</' . $tag['name'] . '>';
        }
        return $close_tags;
    }

    /**
     * Return text wrapped with tags
     *
     * @param string $text
     * @param array $tags
     * @return string
     */
    protected static function wrapTextWithTags(string $text, array $tags)
    {
        $tagged_text = self::resolveOpenTags($tags);
        $tagged_text .= $text;
        $tagged_text .= self::resolveCloseTags($tags);

        return $tagged_text;
    }
}