<?php

use Doublit\Doublit;
use Doublit\TestCase;
use SitPHP\Styles\Formatters\CliFormatter;
use SitPHP\Styles\Formatters\FormatterInterface;
use SitPHP\Styles\Style;
use SitPHP\Styles\StyleManager;
use SitPHP\Styles\TagStyle;
use SitPHP\Styles\TextElement;

class ParserTest extends TestCase
{
    /*
     * Test get/set formatter
     */

    function testGetSetFormatter(){
        $style_manager = new StyleManager();
        $style = new Style($style_manager, ParserTestFormatter::class);
        $this->assertEquals(ParserTestFormatter::class, $style->getFormatterClass());
    }

    function testSetUndefinedFormatterShouldFail()
    {
        $this->expectException(InvalidArgumentException::class);
        $style_manager = new StyleManager();
        $style = new Style($style_manager, 'undefined');
    }

    function testSetInvalidFormatterShouldFail()
    {
        $this->expectException(InvalidArgumentException::class);
        $style_manager = new StyleManager();
        $style = new Style($style_manager, __CLASS__);
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
        $style_manager = new StyleManager();
        $style_manager->setFormatter('my_formatter', $format_double);
        $style = $style_manager->style('my_formatter');
        $this->assertEquals('formatted', $style->format('my <cs color="red">message</cs>'));
    }
    function testFormatWithFormatter()
    {
        $format_double = Doublit::dummy(CliFormatter::class)->getClass();
        $format_double::_method('format')
            ->stub('formatted')
            ->count(1);
        $style_manager = new StyleManager();
        $style = $style_manager->style('cli');
        $this->assertEquals('formatted', $style->format('my <cs color="red">message</cs>', null, $format_double));
    }
    function testFormatWithUndefinedFormatterShouldFail()
    {
        $this->expectException(LogicException::class);
        $style_manager = new StyleManager();
        $style =$style_manager->style('undefined');
        $style->format('message');
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
        $style_manager = new StyleManager();
        $style_manager->setFormatter('my_formatter', $format_double);
        $style = $style_manager->style('my_formatter');
        $this->assertEquals('unformatted', $style->unFormat('my <cs color="red">message</cs>'));
    }
    function testUnFormatWithFormatter()
    {
        $format_double = Doublit::dummy(CliFormatter::class)->getClass();
        $format_double::_method('unFormat')
            ->stub('unformatted')
            ->count(1);
        $style_manager = new StyleManager();
        $style = $style_manager->style('cli');
        $this->assertEquals('unformatted', $style->unFormat('my <cs color="red">message</cs>', $format_double));
    }

    function testUnFormatWithUndefinedFormatterShouldFail()
    {
        $this->expectException(LogicException::class);
        $style_manager = new StyleManager();
        $style = new Style($style_manager, 'undefined');
        $style->unFormat('message');
    }


    /*
     * Test style
     */
    function testGetTagStyle()
    {
        $style_manager = new StyleManager();
        $style = new Style($style_manager, 'cli');
        $style->buildTagStyle('info')->setColor('blue');
        $this->assertInstanceOf(TagStyle::class, $style->getTagStyle('info'));
        $this->assertEquals('blue', $style->getTagStyle('info')->getColor());
    }
    function testRemoveTagStyle()
    {
        $style_manager = new StyleManager();
        $style = new Style($style_manager, 'cli');
        $style->buildTagStyle('info')->setColor('blue');
        $style->removeTagStyle('info');
        $this->assertNull($style->getTagStyle('info'));
    }

    /*
     * Test parse
     */
    function testParseShouldReturnTextElement()
    {
        $style_manager = new StyleManager();
        $style = new Style($style_manager, 'cli');
        $this->assertInstanceOf(TextElement::class, $style->parse('my text'));
    }

    function testParseShouldUnderstandCsTags()
    {
        $style_manager = new StyleManager();
        $style = new Style($style_manager, 'cli');
        $text = $style->parse('my <cs>text</cs>');
        $content = $text->getContent();
        $this->assertEquals('my ', $content[0]);
        $this->assertInstanceOf(TextElement::class, $content[1]);
        $this->assertEquals('text', $content[1]->getContent()[0]);
    }

    function testParseShouldUnderstandStyleTags()
    {
        $style_manager = new StyleManager();
        $style = new Style($style_manager, 'cli');
        $style->buildTagStyle('warning');
        $text = $style->parse('my <warning>text</warning>');
        $content = $text->getContent();
        $this->assertEquals('my ', $content[0]);
        $this->assertInstanceOf(TextElement::class, $content[1]);
        $this->assertEquals('text', $content[1]->getContent()[0]);
    }

    function testParseShouldIgnoreUndefinedTags()
    {
        $style_manager = new StyleManager();
        $style = new Style($style_manager, 'cli');
        $text = $style->parse('my <undefined>text</undefined>');
        $content = $text->getContent();
        $this->assertEquals('my <undefined>text</undefined>', $content[0]);
    }

    function testParseShouldApplyCsStyle()
    {
        $style_manager = new StyleManager();
        $style = new Style($style_manager, 'cli');
        $text = $style->parse('my <cs color="red" background-color="blue" bold="true" underline="true" blink="true" highlight="true">text</cs>');

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
        $style_manager = new StyleManager();
        $style = new Style($style_manager, 'cli');
        $text = $style->parse('my <cs style="color:red;background-color:blue;bold;underline;blink;highlight">text</cs>');
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
        $style_manager = new StyleManager();
        $style = new Style($style_manager, 'cli');
        $style->parse('my <cs undefined="undefined">text</cs>');
    }

    function testParseWithEmptyAttributeShouldBeIgnored()
    {
        $style_manager = new StyleManager();
        $style = new Style($style_manager, 'cli');
        $parsed = $style->parse('my <cs color="">message</cs>');
        /** @var TextElement $content_1 */
        $content_1 = $parsed->getContent()[1];
        $this->assertNull($content_1->getColor());
    }

    function testEscapedTagsShouldBeIgnored()
    {
        $style_manager = new StyleManager();
        $style = new Style($style_manager, 'cli');
        $parsed = $style->parse('my <cs color="red">me\<cs>ssage</cs>');
        $this->assertEquals('my me<cs>ssage', $parsed->getText());
    }

    function testParseInvalidMessageShouldFail(){
        $this->expectException(InvalidArgumentException::class);
        $style_manager = new StyleManager();
        $style = new Style($style_manager, 'cli');
        $style->parse('my [31mmessage [0m[31m[34;41;1;4;5;7mstyle[0m[31m[0m[31m[0m');
    }

    /*
     * Test remove tags
     */
    function testRaw()
    {
        $style_manager = new StyleManager();
        $style = new Style($style_manager, 'cli');
        $style->buildTagStyle('warning');
        $this->assertEquals('my text with warning and <undefined>undefined</undefined>', $style->raw('my <cs color="red">text</cs> with <warning>warning</warning> and <undefined>undefined</undefined>'));
    }
    function testRawTagsWidth()
    {
        $style_manager = new StyleManager();
        $style = new Style($style_manager, 'cli');
        $this->assertEquals('my text'.PHP_EOL.' with w'.PHP_EOL.'idth', $style->raw('my <cs color="red">text</cs> with width', 7));
    }

    /*
     * Test split
     */
    function testSplitWithoutChanges()
    {
        $style_manager = new StyleManager();
        $style = new Style($style_manager, 'cli');
        $message_1 = 'my <cs color="red" style="bold">message</cs>';
        $message_2 = 'my message';
        $this->assertEquals($message_1, $style->split($message_1));
        $this->assertEquals($message_2, $style->split($message_2));
    }

    function testSplitWidth()
    {
        $style_manager = new StyleManager();
        $style = new Style($style_manager, 'cli');
        $message = 'my <cs color="red" style="bold">message</cs>';
        $expected = 'my <cs color="red" style="bold">mes</cs>' . "\n" . '<cs color="red" style="bold">sage</cs>';

        $this->assertEquals($expected, $style->split($message, 6));
    }

    function testSplitWithZeroWidth()
    {
        $style_manager = new StyleManager();
        $style = new Style($style_manager, 'cli');
        $message = 'my <cs color="red" style="bold">message</cs>';
        $this->assertEquals($message, $style->split($message, 0));
    }

    function testSplitShouldRespectLineBreaks()
    {
        $style_manager = new StyleManager();
        $style = new Style($style_manager, 'cli');
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

        $this->assertEquals($expected_1, $style->split($message_1, 3));
        $this->assertEquals($expected_2, $style->split($message_2, 3));
        $this->assertEquals($expected_3, $style->split($message_3, 3));
        $this->assertEquals($expected_4, $style->split($message_4, 3));
        $this->assertEquals($expected_5, $style->split($message_5, 6));
        $this->assertEquals($expected_6, $style->split($message_6, 6));
    }

    function testSplitWithNegativeWidthShouldFail()
    {
        $this->expectException(InvalidArgumentException::class);
        $style_manager = new StyleManager();
        $style = new Style($style_manager, 'cli');
        $message = 'my <cs color="red" style="bold">message</cs>';
        $this->assertEquals($message, $style->split($message, -3));
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
