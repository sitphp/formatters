<?php

use Doublit\Doublit;
use SitPHP\Styles\Formatters\CliFormatter;
use SitPHP\Styles\Parser;
use SitPHP\Styles\Style;
use SitPHP\Styles\TextElement;

class ParserTest extends \Doublit\TestCase
{

    /*
     * Test style
     */
    function testGetStyleTagShouldReturnInstanceOfStyle(){
        Parser::buildStyleTag('warning')
            ->setColor('yellow')
            ->setBackgroundColor('yellow');
        $style = Parser::getTagStyle('warning');
        $this->assertInstanceOf(Style::class, $style);
        $this->assertEquals('yellow', $style->getColor());
        $this->assertEquals('yellow', $style->getBackgroundColor());
    }
    function testPredefinedStylesShouldBeAvailable(){
        $this->assertInstanceOf(Style::class, Parser::getTagStyle('info'));
    }

    /*
     * Test parse
     */
    function testParseShouldReturnTextElement(){
        $this->assertInstanceOf(TextElement::class, Parser::parse('my text'));
    }
    function testParseShouldUnderstandCsTags(){
        $text = Parser::parse('my <cs>text</cs>');
        $content = $text->getContent();
        $this->assertEquals('my ', $content[0]);
        $this->assertInstanceOf(TextElement::class, $content[1]);
        $this->assertEquals('text', $content[1]->getContent()[0]);
    }
    function testParseShouldUnderstandStyleTags(){
        $text = Parser::parse('my <warning>text</warning>');
        $content = $text->getContent();
        $this->assertEquals('my ', $content[0]);
        $this->assertInstanceOf(TextElement::class, $content[1]);
        $this->assertEquals('text', $content[1]->getContent()[0]);
    }
    function testParseShouldIgnoreUndefinedTags(){
        $text = Parser::parse('my <undefined>text</undefined>');
        $content = $text->getContent();
        $this->assertEquals('my <undefined>text</undefined>', $content[0]);
    }
    function testParseShouldApplyCsStyle(){
        $text = Parser::parse('my <cs color="red" background-color="blue" bold="true" underline="true" blink="true" highlight="true">text</cs>');

        /** @var TextElement $content_1 */
        $content_1 = $text->getContent()[1];

        $this->assertEquals('red',$content_1->getColor());
        $this->assertEquals('blue',$content_1->getBackgroundColor());
        $this->assertTrue($content_1->isBold());
        $this->assertTrue($content_1->isUnderlined());
        $this->assertTrue($content_1->isBlinking());
        $this->assertTrue($content_1->isHighlighted());
    }

    function testParseShouldApplyCsStyleAttribute(){
        $text = Parser::parse('my <cs style="color:red;background-color:blue;bold;underline;blink;highlight">text</cs>');
        /** @var TextElement $content_1 */
        $content_1 = $text->getContent()[1];
        $this->assertEquals('red',$content_1->getColor());
        $this->assertEquals('blue',$content_1->getBackgroundColor());
        $this->assertTrue($content_1->isBold());
        $this->assertTrue($content_1->isUnderlined());
        $this->assertTrue($content_1->isBlinking());
        $this->assertTrue($content_1->isHighlighted());
    }
    function testParseWithUndefinedStyleAttributeShouldFail(){
        $this->expectException(\InvalidArgumentException::class);
        Parser::parse('my <cs undefined="undefined">text</cs>');
    }
    function testParseWithEmptyAttributeShouldBeIgnored(){
        $parsed = Parser::parse('my <cs color="">message</cs>');
        /** @var TextElement $content_1 */
        $content_1 = $parsed->getContent()[1];
        $this->assertNull($content_1->getColor());
    }
    function testEscapedTagsShouldBeIgnored(){
        $parsed = Parser::parse('my <cs color="red">me\<cs>ssage</cs>');
        $this->assertEquals('my me<cs>ssage', $parsed->getText());
    }
    function testRemoveStyleTags(){
        $this->assertEquals('my text with warning and <undefined>undefined</undefined>', Parser::removeStyleTags('my <cs color="red">text</cs> with <warning>warning</warning> and <undefined>undefined</undefined>'));
    }

    /*
     * Test split
     */
    function testSplitWithoutChanges(){
        $message_1 = 'my <cs color="red" style="bold">message</cs>';
        $message_2 = 'my message';
        $this->assertEquals($message_1, Parser::split($message_1));
        $this->assertEquals($message_2, Parser::split($message_2));
    }
    function testSplitWidth(){
        $message = 'my <cs color="red" style="bold">message</cs>';
        $expected = 'my <cs color="red" style="bold">mes</cs>'."\n".'<cs color="red" style="bold">sage</cs>';

        $this->assertEquals($expected, Parser::split($message, 6));
    }
    function testSplitWithZeroWidth(){
        $message = 'my <cs color="red" style="bold">message</cs>';
        $this->assertEquals($message, Parser::split($message, 0));
    }
    function testSplitShouldRespectLineBreaks(){
        $message_1 = "my \n<cs color='red' style='bold'>message</cs>";
        $expected_1 = "my \n<cs color='red' style='bold'>mes</cs>\n<cs color='red' style='bold'>sag</cs>\n<cs color='red' style='bold'>e</cs>";
        $message_2 = "my <cs color='red' style='bold'>mess\nage</cs>";
        $expected_2 = "my \n<cs color='red' style='bold'>mes</cs>\n<cs color='red' style='bold'>s</cs>\n<cs color='red' style='bold'>age</cs>";

        $message_3 = "my <cs color='red' style='bold'>\nmessage</cs>";
        $expected_3 = "my \n<cs color='red' style='bold'>mes</cs>\n<cs color='red' style='bold'>sag</cs>\n<cs color='red' style='bold'>e</cs>";

        $message_4 = "my <cs color='red' style='bold'>\n\nmessage</cs>";
        $expected_4 = "my \n\n<cs color='red' style='bold'>mes</cs>\n<cs color='red' style='bold'>sag</cs>\n<cs color='red' style='bold'>e</cs>";

        $message_5 = 'my <cs color="red" style="bold">mes'."\n".'sage</cs>';
        $expected_5 = 'my <cs color="red" style="bold">mes</cs>'."\n".'<cs color="red" style="bold">sage</cs>';

        $message_6 = "my <cs color='red' style='bold'>\n</cs>";
        $expected_6 = "my \n";

        $this->assertEquals($expected_1, Parser::split($message_1, 3));
        $this->assertEquals($expected_2, Parser::split($message_2, 3));
        $this->assertEquals($expected_3, Parser::split($message_3, 3));
        $this->assertEquals($expected_4, Parser::split($message_4, 3));
        $this->assertEquals($expected_5, Parser::split($message_5, 6));
        $this->assertEquals($expected_6, Parser::split($message_6, 6));
    }
    function testSplitWithNegativeWidthShouldFail(){
        $this->expectException(\InvalidArgumentException::class);
        $message = 'my <cs color="red" style="bold">message</cs>';
        $this->assertEquals($message, Parser::split($message, -3));
    }

    /*
     * Test format
     */
    function testFormat(){
        $format_double = Doublit::dummy(CliFormatter::class)->getClass();
        $format_double::_method('format')
            ->stub('formatted')
            ->count(1);
        Parser::setFormatter('my_cli', $format_double);
        $this->assertEquals('formatted', Parser::format('my <cs color="red">message</cs>', 'my_cli'));
    }
    function testFormatWithUndefinedFormatterShouldFail(){
        $this->expectException(\InvalidArgumentException::class);
        Parser::format('my message', 'undefined');
    }
}
