<?php

use Doublit\TestCase;
use SitPHP\Styles\Formatters\CliFormatter;
use SitPHP\Styles\Style;
use SitPHP\Styles\StyleManager;

class CliFormatterTest extends TestCase
{

    /*
     * Test format
     */
    function testFormat()
    {
        $style_manager = new StyleManager();
        $style = new Style($style_manager, 'cli');
        $parsed = $style->parse('my <cs color="red">message <cs color="blue" background-color="red" bold="true" blink="true" highlight="true" underline="true">style</cs></cs>');
        $this->assertEquals('my [31mmessage [0m[31m[34;41;1;4;5;7mstyle[0m[31m[0m[31m[0m', CliFormatter::format($parsed));
    }

    function testFormatWithUndefinedColorShouldFail()
    {
        $this->expectException(\InvalidArgumentException::class);
        $style_manager = new StyleManager();
        $style = new Style($style_manager, 'cli');
        $parsed = $style->parse('my <cs color="undefined">message</cs>');
        CliFormatter::format($parsed);
    }

    function testFormatWithBackgroundColorShouldFail()
    {
        $this->expectException(\InvalidArgumentException::class);
        $style_manager = new StyleManager();
        $style = new Style($style_manager, 'cli');
        $parsed = $style->parse('my <cs background-color="undefined">message</cs>');
        CliFormatter::format($parsed);
    }

    /*
     * Test remove formatting
     */
    function testRemoveFormatting()
    {
        $style_manager = new StyleManager();
        $style = new Style($style_manager, 'cli');
        $parsed = $style->parse('my <cs color="red">message <cs color="blue" background-color="red" bold="true" blink="true" highlight="true" underline="true">style</cs></cs>');
        $this->assertEquals('my message style', CliFormatter::unFormat(CliFormatter::format($parsed)));
    }
}
