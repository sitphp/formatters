<?php

namespace SitPHP\Formatters\Tests;


use LogicException;
use SitPHP\Doubles\TestCase;
use SitPHP\Formatters\Formatter;
use SitPHP\Formatters\FormatterManager;
use SitPHP\Formatters\Formatters\CliFormatter;
use SitPHP\Formatters\Formatters\FormatterInterface;
use SitPHP\Formatters\TextElement;

class FormatterManagerTest extends TestCase
{
    /*
     * Test formatter
     */
    function testSetGetFormatter()
    {
        $formatter_manager = new FormatterManager();
        $formatter_manager->setFormatter('formatter', ParserManagerTestFormatter::class);
        $this->assertEquals(ParserManagerTestFormatter::class, $formatter_manager->getFormatterClass('formatter'));
    }

    function testRemoveFormatter()
    {
        $formatter_manager = new FormatterManager();
        $formatter_manager->setFormatter('formatter', ParserManagerTestFormatter::class);
        $formatter_manager->removeFormatter('formatter');

        $this->assertNull($formatter_manager->getFormatterClass('formatter'));
    }

    function testSetFormatterWithDifferentShouldFail()
    {
        $this->expectException(LogicException::class);
        $formatter_manager = new FormatterManager();
        $formatter_manager->setFormatter('formatter', ParserManagerTestFormatter::class);
        $formatter_manager->setFormatter('formatter', CliFormatter::class);
    }

    /*
     * Test formatter
     */
    function testFormatter()
    {
        $formatter_manager = new FormatterManager();
        $formatter = $formatter_manager->getFormatter(ParserManagerTestFormatter::class);

        $this->assertInstanceOf(Formatter::class, $formatter);
        $this->assertEquals(ParserManagerTestFormatter::class, $formatter->getFormatterClass());
    }
}


class ParserManagerTestFormatter implements FormatterInterface
{

    static function format(TextElement $text)
    {
        // TODO: Implement format() method.
    }

    static function unFormat(string $text)
    {
        // TODO: Implement unFormat() method.
    }
}

