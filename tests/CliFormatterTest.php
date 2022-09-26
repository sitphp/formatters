<?php

namespace SitPHP\Formatters\Tests;

use InvalidArgumentException;
use SitPHP\Doubles\TestCase;
use SitPHP\Formatters\Formatters\CliFormatter;

class CliFormatterTest extends TestCase
{

    /*
     * Test format
     */
    function testFormat()
    {
        $formatter = new CliFormatter();
        $message = 'my <cs color="red">message <cs color="blue" background-color="red" bold="true" blink="true" highlight="true" underline="true">style</cs></cs>';
        $this->assertEquals('my [31mmessage [0m[31m[34;41;1;4;5;7mstyle[0m[31m[0m[31m[0m', $formatter->format($message));
    }

    function testFormatWithInt()
    {
        $formatter = new CliFormatter();
        $message = 'my <cs color="31">message <cs color="34" background-color="41" bold="true" blink="true" highlight="true" underline="true">style</cs></cs>';
        $this->assertEquals('my [31mmessage [0m[31m[34;41;1;4;5;7mstyle[0m[31m[0m[31m[0m', $formatter->format($message));
    }

    function testFormatWithHex(){
        $formatter = new CliFormatter();
        $message = 'my <cs color="31">message <cs color="#ffffff" background-color="#999999">style</cs></cs>';
        $this->assertEquals('my [31mmessage [0m[31m[38;2;255;255;255;48;2;153;153;153mstyle[0m[31m[0m[31m[0m', $formatter->format($message));
    }

    function testFormatWithUndefinedColorShouldFail()
    {
        $this->expectException(InvalidArgumentException::class);
        $formatter = new CliFormatter();
        $formatter->format('my <cs color="undefined">message</cs>');
    }

    function testFormatWithBackgroundColor()
    {
        $this->expectException(InvalidArgumentException::class);
        $formatter = new CliFormatter();
        $formatter->format('my <cs background-color="undefined">message</cs>');
    }

    /*
     * Test remove formatting
     */
    function testRemoveFormatting()
    {
        $formatter = new CliFormatter();
        $message = 'my <cs color="red">message <cs color="blue" background-color="red" bold="true" blink="true" highlight="true" underline="true">style</cs></cs>';
        $this->assertEquals('my message style', $formatter->unFormat($formatter->format($message)));
    }
}
