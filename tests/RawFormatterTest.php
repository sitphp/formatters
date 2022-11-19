<?php

namespace SitPHP\Formatters\Tests;

use SitPHP\Doubles\TestCase;
use SitPHP\Formatters\Formatters\RawFormatter;

class RawFormatterTest extends TestCase
{
    function testFormat()
    {
        $formatter = new RawFormatter();
        $text = $formatter->format('<cs color="blue"></cs>');
        $this->assertEquals('<cs color="blue"></cs>', $text);
    }

    function testWidth()
    {
        $formatter = new RawFormatter();
        $this->assertEquals('<cs color=' . PHP_EOL . '"blue">hel' . PHP_EOL . 'lo</cs>',  $formatter->format('<cs color="blue">hello</cs>', 10));
    }

    function testZeroNegativeWidth()
    {
        $formatter = new RawFormatter();
        $text = '<cs color="blue"></cs>';
        $this->assertEquals($text, $formatter->format($text, -3));
        $this->assertEquals($text, $formatter->format($text, 0));
    }

    function testUnformat()
    {
        $formatter = new RawFormatter();
        $text = $formatter->unFormat('my message style');
        $this->assertEquals('my message style', $text);
    }
}