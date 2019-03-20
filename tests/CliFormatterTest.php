<?php

use Doublit\TestCase;
use SitPHP\Styles\Formatters\CliFormatter;
use SitPHP\Styles\Parser;

class CliFormatterTest extends TestCase
{

    /*
     * Test format
     */
    function testFormat(){
        $parser = new Parser('cli');
        $parsed = $parser->parse('my <cs color="red">message <cs color="blue" background-color="red" bold="true" blink="true" highlight="true" underline="true">style</cs></cs>');
        $this->assertEquals('my [31mmessage [0m[31m[34;41;1;4;5;7mstyle[0m[31m[0m[31m[0m',CliFormatter::format($parsed));
    }

    function testFormatWithUndefinedColorShouldFail(){
        $this->expectException(\InvalidArgumentException::class);
        $parser = new Parser('cli');
        $parsed = $parser->parse('my <cs color="undefined">message</cs>');
        CliFormatter::format($parsed);
    }

    function testFormatWithBackgroundColorShouldFail(){
        $this->expectException(\InvalidArgumentException::class);
        $parser = new Parser('cli');
        $parsed = $parser->parse('my <cs background-color="undefined">message</cs>');
        CliFormatter::format($parsed);
    }

    /*
     * Test remove formatting
     */
    function testRemoveFormatting(){
        $parser = new Parser('cli');
        $parsed = $parser->parse('my <cs color="red">message <cs color="blue" background-color="red" bold="true" blink="true" highlight="true" underline="true">style</cs></cs>');
        $this->assertEquals('my message style', CliFormatter::removeFormatting(CliFormatter::format($parsed)));
    }
}
