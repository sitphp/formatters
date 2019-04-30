<?php

use Doublit\Doublit;
use Doublit\TestCase;
use SitPHP\Formatters\Formatters\CliFormatter;
use SitPHP\Formatters\Formatters\FormatterInterface;
use SitPHP\Formatters\Formatter;
use SitPHP\Formatters\FormatterManager;
use SitPHP\Formatters\TagStyle;
use SitPHP\Formatters\TextElement;

class ParserTest extends TestCase
{
    /*
     * Test get/set formatter
     */

    function testGetSetFormatter(){
        $formatter_manager = new FormatterManager();
        $formatter = new Formatter($formatter_manager, ParserTestFormatter::class);
        $this->assertEquals(ParserTestFormatter::class, $formatter->getFormatterClass());
    }

    function testSetUndefinedFormatterShouldFail()
    {
        $this->expectException(InvalidArgumentException::class);
        $formatter_manager = new FormatterManager();
        $formatter = new Formatter($formatter_manager, 'undefined');
    }

    function testSetInvalidFormatterShouldFail()
    {
        $this->expectException(InvalidArgumentException::class);
        $formatter_manager = new FormatterManager();
        $formatter = new Formatter($formatter_manager, __CLASS__);
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
        $formatter_manager = new FormatterManager();
        $formatter_manager->setFormatter('my_formatter', $format_double);
        $formatter = $formatter_manager->formatter('my_formatter');
        $this->assertEquals('formatted', $formatter->format('my <cs color="red">message</cs>'));
    }
    function testFormatWithFormatter()
    {
        $format_double = Doublit::dummy(CliFormatter::class)->getClass();
        $format_double::_method('format')
            ->stub('formatted')
            ->count(1);
        $formatter_manager = new FormatterManager();
        $formatter_manager->setFormatter('cli', $format_double);
        $formatter = $formatter_manager->formatter('cli');
        $this->assertEquals('formatted', $formatter->format('my <cs color="red">message</cs>', null, $format_double));
    }
    function testFormatWithUndefinedFormatterShouldFail()
    {
        $this->expectException(LogicException::class);
        $formatter_manager = new FormatterManager();
        $formatter =$formatter_manager->formatter('undefined');
        $formatter->format('message');
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
        $formatter_manager = new FormatterManager();
        $formatter_manager->setFormatter('my_formatter', $format_double);
        $formatter = $formatter_manager->formatter('my_formatter');
        $this->assertEquals('unformatted', $formatter->unFormat('my <cs color="red">message</cs>'));
    }
    function testUnFormatWithFormatter()
    {
        $format_double = Doublit::dummy(CliFormatter::class)->getClass();
        $format_double::_method('unFormat')
            ->stub('unformatted')
            ->count(1);
        $formatter_manager = new FormatterManager();
        $formatter_manager->setFormatter('cli', $format_double);
        $formatter = $formatter_manager->formatter('cli');
        $this->assertEquals('unformatted', $formatter->unFormat('my <cs color="red">message</cs>', $format_double));
    }

    function testUnFormatWithUndefinedFormatterShouldFail()
    {
        $this->expectException(LogicException::class);
        $formatter_manager = new FormatterManager();
        $formatter = new Formatter($formatter_manager, 'undefined');
        $formatter->unFormat('message');
    }


    /*
     * Test style
     */
    function testGetTagStyle()
    {
        $formatter_manager = new FormatterManager();
        $formatter = new Formatter($formatter_manager, 'cli');
        $formatter->buildTagStyle('info')->setColor('blue');
        $this->assertInstanceOf(TagStyle::class, $formatter->getTagStyle('info'));
        $this->assertEquals('blue', $formatter->getTagStyle('info')->getColor());
    }
    function testRemoveTagStyle()
    {
        $formatter_manager = new FormatterManager();
        $formatter = new Formatter($formatter_manager, 'cli');
        $formatter->buildTagStyle('info')->setColor('blue');
        $formatter->removeTagStyle('info');
        $this->assertNull($formatter->getTagStyle('info'));
    }

    /*
     * Test parse
     */
    function testParseShouldReturnTextElement()
    {
        $formatter_manager = new FormatterManager();
        $formatter = new Formatter($formatter_manager, 'cli');
        $this->assertInstanceOf(TextElement::class, $formatter->parse('my text'));
    }

    function testParseShouldUnderstandCsTags()
    {
        $formatter_manager = new FormatterManager();
        $formatter = new Formatter($formatter_manager, 'cli');
        $text = $formatter->parse('my <cs>text</cs>');
        $content = $text->getContent();
        $this->assertEquals('my ', $content[0]);
        $this->assertInstanceOf(TextElement::class, $content[1]);
        $this->assertEquals('text', $content[1]->getContent()[0]);
    }

    function testParseShouldUnderstandStyleTags()
    {
        $formatter_manager = new FormatterManager();
        $formatter = new Formatter($formatter_manager, 'cli');
        $formatter->buildTagStyle('warning');
        $text = $formatter->parse('my <warning>text</warning>');
        $content = $text->getContent();
        $this->assertEquals('my ', $content[0]);
        $this->assertInstanceOf(TextElement::class, $content[1]);
        $this->assertEquals('text', $content[1]->getContent()[0]);
    }

    function testParseShouldIgnoreUndefinedTags()
    {
        $formatter_manager = new FormatterManager();
        $formatter = new Formatter($formatter_manager, 'cli');
        $text = $formatter->parse('my <undefined>text</undefined>');
        $content = $text->getContent();
        $this->assertEquals('my <undefined>text</undefined>', $content[0]);
    }

    function testParseShouldApplyCsStyle()
    {
        $formatter_manager = new FormatterManager();
        $formatter = new Formatter($formatter_manager, 'cli');
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
        $formatter_manager = new FormatterManager();
        $formatter = new Formatter($formatter_manager, 'cli');
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
        $formatter_manager = new FormatterManager();
        $formatter = new Formatter($formatter_manager, 'cli');
        $formatter->parse('my <cs undefined="undefined">text</cs>');
    }

    function testParseWithEmptyAttributeShouldBeIgnored()
    {
        $formatter_manager = new FormatterManager();
        $formatter = new Formatter($formatter_manager, 'cli');
        $parsed = $formatter->parse('my <cs color="">message</cs>');
        /** @var TextElement $content_1 */
        $content_1 = $parsed->getContent()[1];
        $this->assertNull($content_1->getColor());
    }

    function testEscapedTagsShouldBeIgnored()
    {
        $formatter_manager = new FormatterManager();
        $formatter = new Formatter($formatter_manager, 'cli');
        $parsed = $formatter->parse('my <cs color="red">me\<cs>ssage</cs>');
        $this->assertEquals('my me<cs>ssage', $parsed->getText());
    }

    function testParseInvalidMessageShouldFail(){
        $this->expectException(InvalidArgumentException::class);
        $formatter_manager = new FormatterManager();
        $formatter = new Formatter($formatter_manager, 'cli');
        $formatter->parse('my [31mmessage [0m[31m[34;41;1;4;5;7mstyle[0m[31m[0m[31m[0m');
    }

    /*
     * Test plain
     */
    function testPlain()
    {
        $formatter_manager = new FormatterManager();
        $formatter = new Formatter($formatter_manager, 'cli');
        $formatter->buildTagStyle('warning');
        $this->assertEquals('my text with warning and <undefined>undefined</undefined>', $formatter->plain('my <cs color="red">text</cs> with <warning>warning</warning> and <undefined>undefined</undefined>'));
    }
    function testPlainTagsWidth()
    {
        $formatter_manager = new FormatterManager();
        $formatter = new Formatter($formatter_manager, 'cli');
        $this->assertEquals('my text'.PHP_EOL.' with w'.PHP_EOL.'idth', $formatter->plain('my <cs color="red">text</cs> with width', 7));
    }

    /*
     * Test raw
     */
    function testRaw(){
        $formatter_manager = new FormatterManager();
        $formatter = new Formatter($formatter_manager, 'cli');
        $text = '<cs color="blue"></cs>';
        $this->assertEquals($text, $formatter->raw($text));
    }

    function testRawWidth(){
        $formatter_manager = new FormatterManager();
        $formatter = new Formatter($formatter_manager, 'cli');
        $this->assertEquals('<cs color='.PHP_EOL.'"blue"></c'.PHP_EOL.'s>', $formatter->raw('<cs color="blue">hello</cs>', 10));
    }

    /*
     * Test split
     */
    function testSplitWithoutChanges()
    {
        $formatter_manager = new FormatterManager();
        $formatter = new Formatter($formatter_manager, 'cli');
        $message_1 = 'my <cs color="red" style="bold">message</cs>';
        $message_2 = 'my message';
        $this->assertEquals($message_1, $formatter->split($message_1));
        $this->assertEquals($message_2, $formatter->split($message_2));
    }

    function testSplitWidth()
    {
        $formatter_manager = new FormatterManager();
        $formatter = new Formatter($formatter_manager, 'cli');
        $message = 'my <cs color="red" style="bold">message</cs>';
        $expected = 'my <cs color="red" style="bold">mes</cs>' . "\n" . '<cs color="red" style="bold">sage</cs>';

        $this->assertEquals($expected, $formatter->split($message, 6));
    }

    function testSplitWithZeroWidth()
    {
        $formatter_manager = new FormatterManager();
        $formatter = new Formatter($formatter_manager, 'cli');
        $message = 'my <cs color="red" style="bold">message</cs>';
        $this->assertEquals($message, $formatter->split($message, 0));
    }

    function testSplitShouldRespectLineBreaks()
    {
        $formatter_manager = new FormatterManager();
        $formatter = new Formatter($formatter_manager, 'cli');
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

    function testSplitWithNegativeWidthShouldFail()
    {
        $this->expectException(InvalidArgumentException::class);
        $formatter_manager = new FormatterManager();
        $formatter = new Formatter($formatter_manager, 'cli');
        $message = 'my <cs color="red" style="bold">message</cs>';
        $this->assertEquals($message, $formatter->split($message, -3));
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
