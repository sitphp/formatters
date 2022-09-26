<?php

namespace SitPHP\Formatters\Tests;

use SitPHP\Doubles\TestCase;
use SitPHP\Formatters\Formatters\TextFormatter;

class TextFormatterTest extends TestCase
{
    function testFormat()
    {
        $formatter = new TextFormatter();
        $text = $formatter->format('my <cs color="red">message <cs color="blue" background-color="red" bold="true" blink="true" highlight="true" underline="true">style</cs></cs>');
        $this->assertEquals('my message style', $text);
    }

    function testUnformat()
    {
        $formatter = new TextFormatter();
        $text = $formatter->unFormat('my message style');
        $this->assertEquals('my message style', $text);
    }
}