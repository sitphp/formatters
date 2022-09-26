<?php

namespace SitPHP\Formatters\Tests;

use InvalidArgumentException;
use SitPHP\Doubles\Double;
use SitPHP\Doubles\TestCase;
use SitPHP\Formatters\Formatters\Formatter;
use SitPHP\Formatters\Formatters\CliFormatter;
use SitPHP\Formatters\StyleTag;
use SitPHP\Formatters\TextElement;

class FormatterTest extends TestCase
{
    /*
     * Test format
     */
    function testFormat()
    {
        $formatter_double = Double::dummy(CliFormatter::class)->getClass();
        $formatter_double::_method('format')
            ->return('formatted')
            ->count(1);
        $formatter = new $formatter_double();
        $this->assertEquals('formatted', $formatter->format('my <cs color="red">message</cs>'));
    }

    function testFormatWithFormatter()
    {
        $formatter_double = Double::dummy(CliFormatter::class)->getClass();
        $formatter_double::_method('format')
            ->return('formatted')
            ->count(1);
        $formatter = new $formatter_double();
        $this->assertEquals('formatted', $formatter->format('my <cs color="red">message</cs>', null, $formatter_double));
    }

    /*
     * Test unformat
     */
    function testUnFormat()
    {
        $formatter_double = Double::dummy(CliFormatter::class)->getClass();
        $formatter_double::_method('unFormat')
            ->return('unformatted')
            ->count(1);
        $formatter = new $formatter_double();
        $this->assertEquals('unformatted', $formatter->unFormat('my <cs color="red">message</cs>'));
    }

    function testUnFormatWithFormatter()
    {
        $formatter_double = Double::dummy(CliFormatter::class)->getClass();
        $formatter_double::_method('unFormat')
            ->return('unformatted')
            ->count(1);
        $formatter = new $formatter_double();
        $this->assertEquals('unformatted', $formatter->unFormat('my <cs color="red">message</cs>', $formatter_double));
    }


    /*
     * Test style
     */
    function testGetTagStyle()
    {
        $formatter = new CliFormatter();
        $formatter->buildTagStyle('info')->setColor('blue');
        $this->assertInstanceOf(StyleTag::class, $formatter->getTagStyle('info'));
        $this->assertEquals('blue', $formatter->getTagStyle('info')->getColor());
    }

    function testRemoveTagStyle()
    {
        $formatter = new CliFormatter();
        $formatter->buildTagStyle('info')->setColor('blue');
        $formatter->removeTagStyle('info');
        $this->assertNull($formatter->getTagStyle('info'));
    }


    /*
     * Test parse
     */
    function testParseShouldReturnTextElement()
    {
        $formatter = new CliFormatter();
        $this->assertInstanceOf(TextElement::class, $formatter->parse('my text'));
    }

    function testParseShouldUnderstandCsTags()
    {
        $formatter = new CliFormatter();
        $text = $formatter->parse('my <cs>text</cs>');
        $content = $text->getContent();
        $this->assertEquals('my ', $content[0]);
        $this->assertInstanceOf(TextElement::class, $content[1]);
        $this->assertEquals('text', $content[1]->getContent()[0]);
    }

    function testParseShouldUnderstandStyleTags()
    {
        $formatter = new CliFormatter();
        $formatter->buildTagStyle('warning');
        $text = $formatter->parse('my <warning>text</warning>');
        $content = $text->getContent();
        $this->assertEquals('my ', $content[0]);
        $this->assertInstanceOf(TextElement::class, $content[1]);
        $this->assertEquals('text', $content[1]->getContent()[0]);
    }

    function testParseShouldIgnoreUndefinedTags()
    {
        $formatter = new CliFormatter();
        $text = $formatter->parse('my <undefined>text</undefined>');
        $content = $text->getText();
        $this->assertEquals('my <undefined>text</undefined>', $content);
    }

    function testParseShouldApplyCsStyle()
    {
        $formatter = new CliFormatter();
        $text = $formatter->parse('my <cs color="red" background-color="blue" bold="true" underline="true" blink="true" highlight="true">text</cs>');

        /** @var TextElement $content_1 */
        $content_1 = $text->getContent()[1];

        $this->assertEquals('red', $content_1->getColor());
        $this->assertEquals('blue', $content_1->getBackgroundColor());
        $this->assertTrue($content_1->isBold());
        $this->assertTrue($content_1->isUnderlined());
        $this->assertTrue($content_1->isBlinking());
        $this->assertTrue($content_1->isHighlighted());
    }

    function testParseShouldApplyCsStyleAttribute()
    {
        $formatter = new CliFormatter();
        $text = $formatter->parse('my <cs style="color:red;background-color:blue;bold;underline;blink;highlight">text</cs>');
        /** @var TextElement $content_1 */
        $content_1 = $text->getContent()[1];
        $this->assertEquals('red', $content_1->getColor());
        $this->assertEquals('blue', $content_1->getBackgroundColor());
        $this->assertTrue($content_1->isBold());
        $this->assertTrue($content_1->isUnderlined());
        $this->assertTrue($content_1->isBlinking());
        $this->assertTrue($content_1->isHighlighted());
    }

    function testParseWithUndefinedStyleAttributeShouldFail()
    {
        $this->expectException(InvalidArgumentException::class);
        $formatter = new CliFormatter();
        $formatter->parse('my <cs undefined="undefined">text</cs>');
    }

    function testParseWithEmptyAttributeShouldBeIgnored()
    {
        $formatter = new CliFormatter();
        $parsed = $formatter->parse('my <cs color="">message</cs>');
        /** @var TextElement $content_1 */
        $content_1 = $parsed->getContent()[1];
        $this->assertNull($content_1->getColor());
    }

    function testEscapedTagsShouldBeIgnored()
    {
        $formatter = new CliFormatter();
        $parsed = $formatter->parse('my <cs color="red">me\<cs>ssage</cs>');
        $this->assertEquals('my me<cs>ssage', $parsed->getText());
    }

    function testParseInvalidMessageShouldFail()
    {
        $this->expectException(InvalidArgumentException::class);
        $formatter = new CliFormatter();
        $formatter->parse('my [31mmessage [0m[31m[34;41;1;4;5;7mstyle[0m[31m[0m[31m[0m');
    }

    /*
     * Test plain
     */
    function testPlain()
    {
        $formatter = new CliFormatter();
        $formatter->buildTagStyle('warning');
        $this->assertEquals('my text with warning and <undefined>undefined</undefined>', $formatter->plain('my <cs color="red">text</cs> with <warning>warning</warning> and <undefined>undefined</undefined>'));
    }

    function testPlainWidth()
    {
        $formatter = new CliFormatter();
        $this->assertEquals('my text' . PHP_EOL . ' with w' . PHP_EOL . 'idth', $formatter->plain('my <cs color="red">text</cs> with width', 7));
    }

    function testPlainZeroNegativeWidth()
    {
        $formatter = new CliFormatter();
        $text = '<cs color="blue">message</cs>';
        $this->assertEquals('message', $formatter->plain($text, 0));
        $this->assertEquals('message', $formatter->plain($text, -3));
    }

    /*
     * Test raw
     */
    function testRaw()
    {
        $formatter = new CliFormatter();
        $text = '<cs color="blue"></cs>';
        $this->assertEquals($text, $formatter->raw($text));
    }

    function testRawWidth()
    {
        $formatter = new CliFormatter();
        $this->assertEquals('<cs color=' . PHP_EOL . '"blue">hel' . PHP_EOL . 'lo</cs>', $formatter->raw('<cs color="blue">hello</cs>', 10));
    }

    function testRawZeroNegativeWidth()
    {
        $formatter = new CliFormatter();
        $text = '<cs color="blue"></cs>';
        $this->assertEquals($text, $formatter->raw($text, -3));
        $this->assertEquals($text, $formatter->raw($text, 0));
    }

    /*
     * Test split
     */
    function testSplitWithoutChanges()
    {
        $formatter = new CliFormatter();
        $message_1 = 'my <cs color="red" style="bold">message</cs>';
        $message_2 = 'my message';
        $this->assertEquals($message_1, $formatter->split($message_1));
        $this->assertEquals($message_2, $formatter->split($message_2));
    }

    function testSplitWidth()
    {
        $formatter = new CliFormatter();
        $message = 'my <cs color="red" style="bold">message</cs>';
        $expected = 'my <cs color="red" style="bold">mes</cs>' . "\n" . '<cs color="red" style="bold">sage</cs>';

        $this->assertEquals($expected, $formatter->split($message, 6));
    }

    function testSplitWithZeroWidth()
    {
        $formatter = new CliFormatter();
        $message = 'my <cs color="red" style="bold">message</cs>';
        $this->assertEquals($message, $formatter->split($message, 0));
    }

    function testSplitShouldRespectLineBreaks()
    {
        $formatter = new CliFormatter();
        $message_1 = "my \n<cs color='red' style='bold'>message</cs>";
        $expected_1 = "my \n<cs color='red' style='bold'>mes</cs>\n<cs color='red' style='bold'>sag</cs>\n<cs color='red' style='bold'>e</cs>";
        $message_2 = "my <cs color='red' style='bold'>mess\nage</cs>";
        $expected_2 = "my \n<cs color='red' style='bold'>mes</cs>\n<cs color='red' style='bold'>s</cs>\n<cs color='red' style='bold'>age</cs>";

        $message_3 = "my <cs color='red' style='bold'>\nmessage</cs>";
        $expected_3 = "my \n<cs color='red' style='bold'>mes</cs>\n<cs color='red' style='bold'>sag</cs>\n<cs color='red' style='bold'>e</cs>";

        $message_4 = "my <cs color='red' style='bold'>\n\nmessage</cs>";
        $expected_4 = "my \n\n<cs color='red' style='bold'>mes</cs>\n<cs color='red' style='bold'>sag</cs>\n<cs color='red' style='bold'>e</cs>";

        $message_5 = 'my <cs color="red" style="bold">mes' . "\n" . 'sage</cs>';
        $expected_5 = 'my <cs color="red" style="bold">mes</cs>' . "\n" . '<cs color="red" style="bold">sage</cs>';

        $message_6 = "my <cs color='red' style='bold'>\n</cs>";
        $expected_6 = "my \n";

        $this->assertEquals($expected_1, $formatter->split($message_1, 3));
        $this->assertEquals($expected_2, $formatter->split($message_2, 3));
        $this->assertEquals($expected_3, $formatter->split($message_3, 3));
        $this->assertEquals($expected_4, $formatter->split($message_4, 3));
        $this->assertEquals($expected_5, $formatter->split($message_5, 6));
        $this->assertEquals($expected_6, $formatter->split($message_6, 6));
    }

    function testSplitWithPreserveEscapedTags()
    {
        $formatter = new CliFormatter();
        $this->assertEquals('<cs>message with \<error></cs>' . PHP_EOL . '<cs>escape\</error> tag</cs>', $formatter->split('<cs>message with \<error>escape\</error> tag</cs>', 20, false, true));
    }

}

class ParserTestFormatter extends Formatter
{

    function doFormat(TextElement $message): string
    {
        return '';
    }

    function doUnFormat(string $message): string
    {
        return '';
    }
}
