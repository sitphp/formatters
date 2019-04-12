<?php

use Doublit\TestCase;
use SitPHP\Styles\Formatters\CliFormatter;
use SitPHP\Styles\Formatters\FormatterInterface;
use SitPHP\Styles\Parser;
use SitPHP\Styles\ParserManager;
use SitPHP\Styles\Style;
use SitPHP\Styles\TextElement;

class ParserManagerTest extends TestCase
{
    /*
     * Test formatter alias
     */
    function testSetGetFormatterAlias(){
        $parser_manager = new ParserManager();
        $parser_manager->setFormatterAlias('formatter', ParserManagerTestFormatter::class);
        $this->assertEquals(ParserManagerTestFormatter::class, $parser_manager->getFormatterAlias('formatter'));
    }

    function testRemoveFormatterAlias(){
        $parser_manager = new ParserManager();
        $parser_manager->setFormatterAlias('formatter', ParserManagerTestFormatter::class);
        $parser_manager->removeFormatterAlias('formatter');

        $this->assertNull($parser_manager->getFormatterAlias('formatter'));
    }

    function testSetFormatterWithDifferentAliasShouldFail(){
        $this->expectException(LogicException::class);
        $parser_manager = new ParserManager();
        $parser_manager->setFormatterAlias('formatter', ParserManagerTestFormatter::class);
        $parser_manager->setFormatterAlias('formatter', CliFormatter::class);
    }

    /*
     * Test style
     */
    function testGetTagStyle()
    {
        $parser_manager = new ParserManager();
        $parser_manager->buildTagStyle('info')->setColor('blue');
        $this->assertInstanceOf(Style::class, $parser_manager->getTagStyle('info'));
        $this->assertEquals('blue', $parser_manager->getTagStyle('info')->getColor());
    }
    function testRemoveTagStyle()
    {
        $parser_manager = new ParserManager();
        $parser_manager->buildTagStyle('info')->setColor('blue');
        $parser_manager->removeTagStyle('info');
        $this->assertNull($parser_manager->getTagStyle('info'));
    }

    /*
     * Test parser
     */
    function testParser(){
        $parser_manager = new ParserManager();
        $parser_manager->buildTagStyle('tag_style')->setColor('blue');
        $parser = $parser_manager->parser(ParserManagerTestFormatter::class);

        $this->assertInstanceOf(Parser::class, $parser);
        $this->assertInstanceOf(Style::class, $parser->getTagStyle('tag_style'));
        $this->assertEquals(ParserManagerTestFormatter::class, $parser->getFormatter());
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

