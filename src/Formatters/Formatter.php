<?php

namespace SitPHP\Formatters\Formatters;

use DOMDocument;
use DOMElement;
use DOMNamedNodeMap;
use Exception;
use InvalidArgumentException;
use SitPHP\Formatters\StyleTag;
use SitPHP\Formatters\TextElement;

abstract class Formatter
{

    /**
     * @var array
     */
    private array $tags_styles = [];

    /**
     * @var string[]
     */
    private array $style_methods_mapping = [
        'color' => 'setColor',
        'background-color' => 'setBackgroundColor',
        'bold' => 'bold',
        'underline' => 'underline',
        'blink' => 'blink',
        'highlight' => 'highlight'
    ];
    /**
     * @var string|null
     */
    private ?string $name;

    /**
     * @param string|null $name
     */
    function __construct(string $name = null)
    {
        $this->name = $name;
    }

    function getName(): ?string
    {
        return $this->name;
    }

    /*
     * Tag methods
     */

    /**
     * Build a new style
     *
     * @param string $name
     * @return StyleTag
     * @throws Exception
     */
    function buildTagStyle(string $name): StyleTag
    {
        $style = new StyleTag();
        $this->setTagStyle($name, $style);
        return $style;
    }

    /**
     * Set a built style
     *
     * @param $name
     * @param StyleTag $style
     * @throws Exception
     */
    function setTagStyle($name, StyleTag $style): void
    {
        $this->tags_styles[$name] = $style;
    }

    /**
     * Return a built style
     *
     * @param string $name
     * @return StyleTag|null
     */
    function getTagStyle(string $name): ?StyleTag
    {
        return $this->tags_styles[$name] ?? null;
    }

    /**
     * Remove tag style
     *
     * @param string $name
     */
    function removeTagStyle(string $name): void
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
    abstract function format(string $message, int $width = null): string;


    /**
     * UnFormat message
     *
     * @param string $message
     * @return mixed
     */
    abstract function unFormat(string $message): string;


    /**
     * Parse a text to an TextElement object
     *
     * @param string $text
     * @param int|null $width
     * @return TextElement|null
     * @throws Exception
     */
    function parse(string $text, int $width = null): ?TextElement
    {
        $prepared_text = $this->split($text, $width);
        $dom = new DOMDocument();
        try {
            $dom->loadXML('<node>' . $prepared_text . '</node>');
        } catch (Exception $e) {
            throw new InvalidArgumentException('Text "' . $text . '" could not be parsed : text should be in XML format');
        }
        return $this->domToOutputText($dom->documentElement);
    }

    /**
     * Split a string with tags in multiple lines with proper tags
     *
     * @param string $content
     * @param int|null $width
     * @param bool $encode_special_chars
     * @param bool $preserve_escaped_tags
     * @return string
     * @throws Exception
     */
    function split(string $content, int $width = null, bool $encode_special_chars = true, bool $preserve_escaped_tags = false): string
    {
        $opened_tags = [];
        $content_substr_start = 0;
        $current_line_char_count = 0;
        $splitted = '';

        preg_match_all('#\\\\?<\/?\s*([a-z1-9]+)\s*([^<>]*?)\s*>#i', $content, $matches, PREG_OFFSET_CAPTURE);
        foreach ($matches[0] as $match_key => $match) {

            $match_tag = $match[0];
            $match_pos = $match[1];

            // If closing tag doesn't match previously opened tag
            if (
                ($match_tag[0] == '\\')
                || (!empty($opened_tags) && $match_tag[1] === '/' && $matches[1][$match_key][0] !== end($opened_tags)['name'])
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
     * @param bool $preserve_escaped_tags
     * @return string
     */
    protected function splitText(string $text, &$current_line_char_count, array $opened_tags = [], int $width = null, bool $encode_special_chars = true, bool $preserve_escaped_tags = false): string
    {
        $text = strtr($text, ['\<' => '<']);
        if ($encode_special_chars) {
            $text = htmlspecialchars($text);
        }
        if ($width === null || $width < 0) {
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
            // Text part is empty : it's a line break
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


    /**
     * Transform dom element to text element
     *
     * @param DOMElement $dom
     * @param TextElement|null $text_el
     * @return TextElement|null
     * @throws Exception
     */
    protected function domToOutputText(DOMElement $dom, TextElement $text_el = null): ?TextElement
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
                    $tag_style = $this->getTagStyle($node->nodeName);
                    // Should never happen
                    if ($tag_style === null) {
                        $text_el->addContent('<' . $node->nodeName . '>' . $node->nodeValue . '</' . $node->nodeName . '>');
                        break;
                    }
                    $child_el = new TextElement();
                    $child_el->setStyle($tag_style);
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
     * @param TextElement $text
     * @param DOMNamedNodeMap $node_attributes
     */
    protected function applyNodeAttributes(TextElement $text, DOMNamedNodeMap $node_attributes): void
    {
        $attributes = [];
        foreach ($node_attributes as $name => $attribute) {
            $attributes[$name] = $attribute->nodeValue;
        }
        $this->applyArrayAttributes($text, $attributes);
    }

    /**
     * @param TextElement $styled_text
     * @param array $style
     * @return void
     */
    protected function applyArrayAttributes(TextElement $styled_text, array $style): void
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
                    $item_value = $style_item[1] ?? true;
                    $this->applyStyleItem($styled_text, $item_key, $item_value);
                }
            } else {
                $this->applyStyleItem($styled_text, $key, $value);
            }
        }
    }

    /**
     * @param $styled_text
     * @param $key
     * @param $value
     * @return void
     */
    protected function applyStyleItem($styled_text, $key, $value): void
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
    protected function resolveOpenTags(array $tags): string
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
    protected function resolveCloseTags(array $tags): string
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
     * @param array $open_tags
     * @param bool $escape_text_tags
     * @return string
     */
    protected function wrapTextWithTags(string $text, array $open_tags, bool $escape_text_tags = false): string
    {
        $tagged_text = $this->resolveOpenTags($open_tags);
        $tagged_text .= $escape_text_tags ? strtr($text, ['<' => '\<']) : $text;
        $tagged_text .= $this->resolveCloseTags($open_tags);

        return $tagged_text;
    }
}