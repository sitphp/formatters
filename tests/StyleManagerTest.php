<?php

use Doublit\TestCase;
use SitPHP\Styles\Formatters\CliFormatter;
use SitPHP\Styles\Formatters\FormatterInterface;
use SitPHP\Styles\Style;
use SitPHP\Styles\StyleManager;
use SitPHP\Styles\TagStyle;
use SitPHP\Styles\TextElement;

class ParserManagerTest extends TestCase
{
    /*
     * Test formatter
     */
    function testSetGetFormatter(){
        $style_manager = new StyleManager();
        $style_manager->setFormatter('formatter', ParserManagerTestFormatter::class);
        $this->assertEquals(ParserManagerTestFormatter::class, $style_manager->getFormatterClass('formatter'));
    }

    function testRemoveFormatter(){
        $style_manager = new StyleManager();
        $style_manager->setFormatter('formatter', ParserManagerTestFormatter::class);
        $style_manager->removeFormatter('formatter');

        $this->assertNull($style_manager->getFormatterClass('formatter'));
    }

    function testSetFormatterWithDifferentShouldFail(){
        $this->expectException(LogicException::class);
        $style_manager = new StyleManager();
        $style_manager->setFormatter('formatter', ParserManagerTestFormatter::class);
        $style_manager->setFormatter('formatter', CliFormatter::class);
    }

    /*
     * Test style
     */
    function testStyle(){
        $style_manager = new StyleManager();
        $style = $style_manager->style(ParserManagerTestFormatter::class);

        $this->assertInstanceOf(Style::class, $style);
        $this->assertEquals(ParserManagerTestFormatter::class, $style->getFormatter());
    }
}


class ParserManagerTestFormatter implements FormatterInterface{

    static function format(TextElement $text)
    {
        // TODO: Implement format() method.
    }

    static function unFormat(string $text)
    {
        // TODO: Implement unFormat() method.
    }
}

