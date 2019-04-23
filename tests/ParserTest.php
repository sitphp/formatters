<?php

use Doublit\Doublit;
use Doublit\TestCase;
use SitPHP\Styles\Formatters\CliFormatter;
use SitPHP\Styles\Formatters\FormatterInterface;
use SitPHP\Styles\Parser;
use SitPHP\Styles\ParserManager;
use SitPHP\Styles\Style;
use SitPHP\Styles\TextElement;

class ParserTest extends TestCase
{
    /*
     * Test get/set formatter
     */

    function testGetSetFormatter(){
        $parser = new Parser();
        $parser->setFormatter(ParserTestFormatter::class);
        $this->assertEquals(ParserTestFormatter::class, $parser->getFormatter());
    }

    function testSetUndefinedFormatterShouldFail()
    {
        $this->expectException(\InvalidArgumentException::class);
        $parser = new Parser();
        $parser->setFormatter('undefined');
    }

    function testSetInvalidFormatterShouldFail()
    {
        $this->expectException(\InvalidArgumentException::class);
        $parser = new Parser();
        $parser->setFormatter(__CLASS__);
    }

    /*
     * Test format
     */
    function testFormat()
    {
        $format_double = Doublit::dummy(CliFormatter::class)->getClass();
        $format_double::_method('format')
            ->stub('formatted')
            ->count(1);
        $parser_manager = new ParserManager();
        $parser_manager->setFormatterAlias('my_formatter', $format_double);
        $parser = $parser_manager->parser('my_formatter');
        $this->assertEquals('formatted', $parser->format('my <cs color="red">message</cs>'));
    }
    function testFormatWithUndefinedFormatterShouldFail()
    {
        $this->expectException(LogicException::class);
        $parser = new Parser();
        $parser->format('message');
    }

    /*
     * Test unformat
     */
    function testUnFormat()
    {
        $format_double = Doublit::dummy(CliFormatter::class)->getClass();
        $format_double::_method('unFormat')
            ->stub('unformatted')
            ->count(1);
        $parser_manager = new ParserManager();
        $parser_manager->setFormatterAlias('my_formatter', $format_double);
        $parser = $parser_manager->parser('my_formatter');
        $this->assertEquals('unformatted', $parser->unFormat('my <cs color="red">message</cs>'));
    }

    function testUnFormatWithUndefinedFormatterShouldFail()
    {
        $this->expectException(LogicException::class);
        $parser = new Parser();
        $parser->unFormat('message');
    }


    /*
     * Test style
     */
    function testGetTagStyle()
    {
        $parser = new Parser();
        $parser->buildTagStyle('info')->setColor('blue');
        $this->assertInstanceOf(Style::class, $parser->getTagStyle('info'));
        $this->assertEquals('blue', $parser->getTagStyle('info')->getColor());
    }
    function testRemoveTagStyle()
    {
        $parser = new Parser();
        $parser->buildTagStyle('info')->setColor('blue');
        $parser->removeTagStyle('info');
        $this->assertNull($parser->getTagStyle('info'));
    }

    /*
     * Test parse
     */
    function testParseShouldReturnTextElement()
    {
        $parser = new Parser();
        $this->assertInstanceOf(TextElement::class, $parser->parse('my text'));
    }

    function testParseShouldUnderstandCsTags()
    {
        $parser = new Parser();
        $text = $parser->parse('my <cs>text</cs>');
        $content = $text->getContent();
        $this->assertEquals('my ', $content[0]);
        $this->assertInstanceOf(TextElement::class, $content[1]);
        $this->assertEquals('text', $content[1]->getContent()[0]);
    }

    function testParseShouldUnderstandStyleTags()
    {
        $parser = new Parser();
        $parser->buildTagStyle('warning');
        $text = $parser->parse('my <warning>text</warning>');
        $content = $text->getContent();
        $this->assertEquals('my ', $content[0]);
        $this->assertInstanceOf(TextElement::class, $content[1]);
        $this->assertEquals('text', $content[1]->getContent()[0]);
    }

    function testParseShouldIgnoreUndefinedTags()
    {
        $parser = new Parser();
        $text = $parser->parse('my <undefined>text</undefined>');
        $content = $text->getContent();
        $this->assertEquals('my <undefined>text</undefined>', $content[0]);
    }

    function testParseShouldApplyCsStyle()
    {
        $parser = new Parser();
        $text = $parser->parse('my <cs color="red" background-color="blue" bold="true" underline="true" blink="true" highlight="true">text</cs>');

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
        $parser = new Parser();
        $text = $parser->parse('my <cs style="color:red;background-color:blue;bold;underline;blink;highlight">text</cs>');
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
        $this->expectException(\InvalidArgumentException::class);
        $parser = new Parser();
        $parser->parse('my <cs undefined="undefined">text</cs>');
    }

    function testParseWithEmptyAttributeShouldBeIgnored()
    {
        $parser = new Parser();
        $parsed = $parser->parse('my <cs color="">message</cs>');
        /** @var TextElement $content_1 */
        $content_1 = $parsed->getContent()[1];
        $this->assertNull($content_1->getColor());
    }

    function testEscapedTagsShouldBeIgnored()
    {
        $parser = new Parser();
        $parsed = $parser->parse('my <cs color="red">me\<cs>ssage</cs>');
        $this->assertEquals('my me<cs>ssage', $parsed->getText());
    }

    function testParseInvalidMessageShouldFail(){
        $this->expectException(\InvalidArgumentException::class);
        $parser = new Parser();
        $parser->parse('my [31mmessage [0m[31m[34;41;1;4;5;7mstyle[0m[31m[0m[31m[0m');
    }

    /*
     * Test to string
     */
    function testToString()
    {
        $parser = new Parser();
        $parser->buildTagStyle('warning');
        $this->assertEquals('my text with warning and <undefined>undefined</undefined>', $parser->toString('my <cs color="red">text</cs> with <warning>warning</warning> and <undefined>undefined</undefined>'));
    }
    function testToStringWidth()
    {
        $parser = new Parser();
        $this->assertEquals('my text'.PHP_EOL.' with w'.PHP_EOL.'idth', $parser->toString('my <cs color="red">text</cs> with width', 7));
    }

    /*
     * Test split
     */
    function testSplitWithoutChanges()
    {
        $parser = new Parser();
        $message_1 = 'my <cs color="red" style="bold">message</cs>';
        $message_2 = 'my message';
        $this->assertEquals($message_1, $parser->split($message_1));
        $this->assertEquals($message_2, $parser->split($message_2));
    }

    function testSplitWidth()
    {
        $parser = new Parser();
        $message = 'my <cs color="red" style="bold">message</cs>';
        $expected = 'my <cs color="red" style="bold">mes</cs>' . "\n" . '<cs color="red" style="bold">sage</cs>';

        $this->assertEquals($expected, $parser->split($message, 6));
    }

    function testSplitWithZeroWidth()
    {
        $parser = new Parser();
        $message = 'my <cs color="red" style="bold">message</cs>';
        $this->assertEquals($message, $parser->split($message, 0));
    }

    function testSplitShouldRespectLineBreaks()
    {
        $parser = new Parser();
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

        $this->assertEquals($expected_1, $parser->split($message_1, 3));
        $this->assertEquals($expected_2, $parser->split($message_2, 3));
        $this->assertEquals($expected_3, $parser->split($message_3, 3));
        $this->assertEquals($expected_4, $parser->split($message_4, 3));
        $this->assertEquals($expected_5, $parser->split($message_5, 6));
        $this->assertEquals($expected_6, $parser->split($message_6, 6));
    }

    function testSplitWithNegativeWidthShouldFail()
    {
        $this->expectException(\InvalidArgumentException::class);
        $parser = new Parser();
        $message = 'my <cs color="red" style="bold">message</cs>';
        $this->assertEquals($message, $parser->split($message, -3));
    }
}

class ParserTestFormatter implements FormatterInterface{

    static function format(TextElement $text)
    {
        // TODO: Implement format() method.
    }

    static function unFormat(string $text)
    {
        // TODO: Implement removeFormatting() method.
    }
}
