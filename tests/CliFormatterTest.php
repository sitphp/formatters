<?php

use SitPHP\Doubles\TestCase;
use SitPHP\Formatters\Formatters\CliFormatter;
use SitPHP\Formatters\Formatter;
use SitPHP\Formatters\FormatterManager;

class CliFormatterTest extends TestCase
{

    /*
     * Test format
     */
    function testFormat()
    {
        $formatter_manager = new FormatterManager();
        $formatter = new Formatter($formatter_manager, 'cli');
        $parsed = $formatter->parse('my <cs color="red">message <cs color="blue" background-color="red" bold="true" blink="true" highlight="true" underline="true">style</cs></cs>');
        $this->assertEquals('my [31mmessage [0m[31m[34;41;1;4;5;7mstyle[0m[31m[0m[31m[0m', CliFormatter::format($parsed));
    }

    function testFormatWithInt()
    {
        $formatter_manager = new FormatterManager();
        $formatter = new Formatter($formatter_manager, 'cli');
        $parsed = $formatter->parse('my <cs color="31">message <cs color="34" background-color="41" bold="true" blink="true" highlight="true" underline="true">style</cs></cs>');
        $this->assertEquals('my [31mmessage [0m[31m[34;41;1;4;5;7mstyle[0m[31m[0m[31m[0m', CliFormatter::format($parsed));
    }

    function testFormatWithUndefinedColorShouldFail()
    {
        $this->expectException(\InvalidArgumentException::class);
        $formatter_manager = new FormatterManager();
        $formatter = new Formatter($formatter_manager, 'cli');
        $parsed = $formatter->parse('my <cs color="undefined">message</cs>');
        CliFormatter::format($parsed);
    }

    function testFormatWithBackgroundColorShouldFail()
    {
        $this->expectException(\InvalidArgumentException::class);
        $formatter_manager = new FormatterManager();
        $formatter = new Formatter($formatter_manager, 'cli');
        $parsed = $formatter->parse('my <cs background-color="undefined">message</cs>');
        CliFormatter::format($parsed);
    }

    /*
     * Test remove formatting
     */
    function testRemoveFormatting()
    {
        $formatter_manager = new FormatterManager();
        $formatter = new Formatter($formatter_manager, 'cli');
        $parsed = $formatter->parse('my <cs color="red">message <cs color="blue" background-color="red" bold="true" blink="true" highlight="true" underline="true">style</cs></cs>');
        $this->assertEquals('my message style', CliFormatter::unFormat(CliFormatter::format($parsed)));
    }
}
