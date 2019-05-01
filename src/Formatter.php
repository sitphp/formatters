<?php

namespace SitPHP\Formatters;

use DOMDocument;
use DOMElement;
use DOMNamedNodeMap;
use Exception;
use InvalidArgumentException;
use SitPHP\Formatters\Formatters\FormatterInterface;

class Formatter
{

    /**
     * @var FormatterInterface
     */
    private $formatter;
    private $tags_styles = [];

    private $style_methods_mapping = [
        'color' => 'setColor',
        'background-color' => 'setBackgroundColor',
        'bold' => 'bold',
        'underline' => 'underline',
        'blink' => 'blink',
        'highlight' => 'highlight'
    ];
    /**
     * @var FormatterManager
     */
    private $manager;

    function __construct(FormatterManager $manager, string $formatter)
    {
        $this->manager = $manager;
        $this->formatter = $this->validateFormatter($formatter);
    }

    /**
     * Return formatter
     *
     * @return FormatterInterface
     */
    function getFormatterClass()
    {
        return $this->formatter;
    }

    /*
     * Tag methods
     */

    /**
     * Build a new style
     *
     * @param string $name
     * @return TagStyle
     * @throws Exception
     */
    function buildTagStyle(string $name)
    {
        $style = new TagStyle();
        $this->setTagStyle($name, $style);
        return $style;
    }

    /**
     * Set a built style
     *
     * @param $name
     * @param TagStyle $style
     * @throws Exception
     */
    function setTagStyle($name, TagStyle $style)
    {
        $this->tags_styles[$name] = $style;
    }

    /**
     * Return a built style
     *
     * @param string $name
     * @return TagStyle
     * @throws Exception
     */
    function getTagStyle(string $name)
    {
        return $this->tags_styles[$name] ?? null;
    }

    /**
     * Remove tag style
     *
     * @param string $name
     */
    function removeTagStyle(string $name)
    {
        unset($this->tags_styles[$name]);
    }


    /*
     * Formatting methods
     */

    /**
     * Format string with formatter
     *
     * @param string $message
     * @param int|null $width
     * @return mixed
     * @throws Exception
     */
    function format(string $message, int $width = null)
    {
        $formatter_class = $this->getFormatterClass();
        $parsed = $this->parse($message, $width);
        return $formatter_class::format($parsed);
    }

    /**
     * UnFormat message
     *
     * @param string $message
     * @return mixed
     */
    function unFormat(string $message)
    {
        $formatter_class = $this->getFormatterClass();
        return $formatter_class::unFormat($message);
    }

    /**
     * @param string $message
     * @param int $width
     * @return string
     * @throws Exception
     */
    function raw(string $message, int $width = null)
    {
        if (null === $width) {
            return $message;
        }
        return $this->mb_chunk_split($message, $width);
    }


    /**
     * Remove string tags and optionally split to width
     *
     * @param string $text
     * @param int $width
     * @return string|null
     * @throws Exception
     */
    function plain(string $text, int $width = null)
    {
        return $this->parse($text, $width)->getText();
    }

    /**
     * Parse a text to an TextElement object
     *
     * @param string $text
     * @param int|null $width
     * @return TextElement
     * @throws Exception
     */
    function parse(string $text, int $width = null)
    {
        $prepared_text = $this->split($text, $width);
        $dom = new DOMDocument();
        // Should never happen
        try {
            $dom->loadXML('<node>' . $prepared_text . '</node>');
        } catch (Exception $e) {
            throw new InvalidArgumentException('Text "' . $text . '" could not be parsed : text should be in XML format');
        }
        $text = $this->domToOutputText($dom->documentElement);
        return $text;
    }

    /**
     * Split a string with tags in multiple lines with proper tags
     *
     * @param string $content
     * @param int|null $width
     * @param bool $encode_special_chars
     * @return string
     * @throws Exception
     */
    function split(string $content, int $width = null, bool $encode_special_chars = true, bool $preserve_escaped_tags = false)
    {
        if (isset($width) && $width < 0) {
            throw new InvalidArgumentException('Invalid $width argument : expected positive int');
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

            // Get text before tab
            $text_before = substr($content, $content_substr_start, $match_pos - $content_substr_start);

            // Split text before tag
            $splitted .= $this->splitText($text_before, $current_line_char_count, $opened_tags, $width, $encode_special_chars, $preserve_escaped_tags);

            // Move offset to next tag
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
        $splitted .= $this->splitText($text_after, $current_line_char_count, $opened_tags, $width, $encode_special_chars, $preserve_escaped_tags);

        return $splitted;
    }

    /**
     * @param string $text
     * @param $current_line_char_count
     * @param array $opened_tags
     * @param int|null $width
     * @param bool $encode_special_chars
     * @return bool|string
     */
    protected function splitText(string $text, &$current_line_char_count, $opened_tags = [], int $width = null, bool $encode_special_chars = true, bool $preserve_escaped_tags = false)
    {
        $text = strtr($text, ['\<' => '<']);
        if ($encode_special_chars) {
            $text = htmlspecialchars($text);
        }
        if ($width === null) {
            return $this->wrapTextWithTags($text, $opened_tags, $preserve_escaped_tags);
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
            // Text part is empty : its a line break
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

            // Width is zero, just wrap every line with tags
            if ($width === 0) {
                $splitted .= $this->wrapTextWithTags($text_part, $opened_tags, $preserve_escaped_tags);
                continue;
            }

            // Current line is not full, fill it
            if ($current_line_char_count > 0 && $current_line_char_count < $width) {
                $splitted_part = mb_substr($text_part, 0, $width - $current_line_char_count);
                $splitted .= $this->wrapTextWithTags($splitted_part, $opened_tags, $preserve_escaped_tags);

                $text_part = mb_substr($text_part, $width - $current_line_char_count);
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
            $text_part_line_count = ceil($text_part_length / $width);
            $text_part_substr_start = 0;
            for ($i = 1; $i <= $text_part_line_count; $i++) {
                if ($i > 1) {
                    $splitted .= "\n";
                    $current_line_char_count = 0;
                }
                // Last line
                if ($i == $text_part_line_count) {
                    $last_line_text = mb_substr($text_part, $text_part_substr_start);
                    $current_line_char_count = mb_strlen($last_line_text);
                    $split_part = $last_line_text;
                } // Other lines
                else {
                    $split_part = mb_substr($text_part, $text_part_substr_start, $width);
                    $text_part_substr_start += $width;
                }
                $splitted .= $this->wrapTextWithTags($split_part, $opened_tags, $preserve_escaped_tags);
            }
        }
        return $splitted;
    }

    protected function mb_chunk_split(string $string, int $chunklen = 76, $end = PHP_EOL)
    {
        $chars = preg_split("//u", $string, null, PREG_SPLIT_NO_EMPTY);
        $array = array_chunk($chars, $chunklen);
        $string_chunks = [];
        foreach ($array as $item) {
            $string_chunks[] = implode('', $item);
        }
        return implode($end, $string_chunks);
    }


    /**
     * Transform dom element to text element
     *
     * @param DOMElement $dom
     * @param TextElement $text_el
     * @return TextElement
     * @throws Exception
     */
    protected function domToOutputText(DOMElement $dom, TextElement $text_el = null)
    {
        if (!isset($text_el)) {
            $text_el = new TextElement();
        }
        foreach ($dom->childNodes as $node) {
            switch ($node->nodeName) {
                case '#text':
                    $text_el->addContent($node->nodeValue);
                    break;
                case 'cs':
                    $child_el = new TextElement();
                    if (isset($node->attributes)) {
                        $this->applyNodeAttributes($child_el, $node->attributes);
                    }
                    $text_el->addContent($child_el);
                    $this->domToOutputText($node, $child_el);
                    break;
                default:
                    $text_style = $this->getTagStyle($node->nodeName);
                    // Should never happen
                    if ($text_style === null) {
                        throw new Exception('Undefined style "' . $node->nodeName . '"');
                    }
                    $child_el = new TextElement();
                    $child_el->setStyle($text_style);
                    if (isset($node->attributes)) {
                        $this->applyNodeAttributes($child_el, $node->attributes);
                    }
                    $text_el->addContent($child_el);
                    $this->domToOutputText($node, $child_el);
                    break;
            }
        }
        return $text_el;

    }

    /**
     * Validate formatter
     *
     * @param $formatter
     * @return mixed
     * @throws Exception
     */
    protected function validateFormatter($formatter)
    {
        if (null !== $this->manager->getFormatterClass($formatter)) {
            $formatter = $this->manager->getFormatterClass($formatter);
        }
        if (!class_exists($formatter)) {
            throw new InvalidArgumentException('Invalid formatter "' . $formatter . '" : expected formatter name or formatter class');
        }
        if (!is_subclass_of($formatter, FormatterInterface::class)) {
            throw new InvalidArgumentException('Invalid formatter class "' . $formatter . '" : expected subclass of ' . FormatterInterface::class);
        }
        return $formatter;
    }

    /**
     * @param TextElement $text
     * @param DOMNamedNodeMap $node_attributes
     */
    protected function applyNodeAttributes(TextElement $text, DOMNamedNodeMap $node_attributes)
    {
        $attributes = [];
        foreach ($node_attributes as $name => $attribute) {
            $attributes[$name] = $attribute->nodeValue;
        }
        $this->applyArrayAttributes($text, $attributes);
    }

    protected function applyArrayAttributes(TextElement $styled_text, array $style)
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
                    $this->applyStyleItem($styled_text, $item_key, $item_value);
                }
            } else {
                $this->applyStyleItem($styled_text, $key, $value);
            }
        }
    }

    protected function applyStyleItem($styled_text, $key, $value)
    {
        if (!isset($this->style_methods_mapping[$key])) {
            throw new InvalidArgumentException('Undefined style ' . $key);
        }
        $style_method = $this->style_methods_mapping[$key];
        $styled_text->$style_method($value);
    }



    /**
     * Return string of opening tags from tags array
     *
     * @param array $tags
     * @return string
     */
    protected function resolveOpenTags(array $tags)
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
    protected function resolveCloseTags(array $tags)
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
     * @param bool $escape_text_tags
     * @return string
     */
    protected function wrapTextWithTags(string $text, array $tags, bool $escape_text_tags = false)
    {
        $tagged_text = $this->resolveOpenTags($tags);
        $tagged_text .= $escape_text_tags ? strtr($text, ['<' => '\<']) : $text;
        $tagged_text .= $this->resolveCloseTags($tags);

        return $tagged_text;
    }
}