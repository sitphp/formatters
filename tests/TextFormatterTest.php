<?php

namespace SitPHP\Formatters\Tests;

use SitPHP\Doubles\TestCase;
use SitPHP\Formatters\FormatterManager;

class TextFormatterTest extends TestCase
{
    function testFormat()
    {
        $formatter_manager = new FormatterManager();
        $formatter = $formatter_manager->getFormatter('text');
        $text = $formatter->format('my <cs color="red">message <cs color="blue" background-color="red" bold="true" blink="true" highlight="true" underline="true">style</cs></cs>');
        $this->assertEquals('my message style', $text);
    }

    function testUnformat(){
        $formatter_manager = new FormatterManager();
        $formatter = $formatter_manager->getFormatter('text');
        $text = $formatter->unFormat('my message style');
        $this->assertEquals('my message style', $text);
    }
}