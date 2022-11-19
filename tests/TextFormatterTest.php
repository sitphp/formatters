<?php

namespace SitPHP\Formatters\Tests;

use SitPHP\Doubles\TestCase;
use SitPHP\Formatters\Formatters\CliFormatter;
use SitPHP\Formatters\Formatters\TextFormatter;

class TextFormatterTest extends TestCase
{
    function testFormat()
    {
        $formatter = new TextFormatter();
        $text = $formatter->format('my <cs color="red">message <cs color="blue" background-color="red" bold="true" blink="true" highlight="true" underline="true">style</cs></cs>');
        $this->assertEquals('my message style', $text);
    }

    function testWidth()
    {
        $formatter = new TextFormatter();
        $this->assertEquals('my text' . PHP_EOL . ' with w' . PHP_EOL . 'idth', $formatter->format('my <cs color="red">text</cs> with width', 7));
    }

    function testZeroNegativeWidth()
    {
        $formatter = new TextFormatter();
        $text = '<cs color="blue">message</cs>';
        $this->assertEquals('message', $formatter->format($text, 0));
        $this->assertEquals('message', $formatter->format($text, -3));
    }

    function testUnformat()
    {
        $formatter = new TextFormatter();
        $text = $formatter->unFormat('my message style');
        $this->assertEquals('my message style', $text);
    }
}