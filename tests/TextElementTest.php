<?php

use SitPHP\Doubles\TestCase;
use SitPHP\Formatters\TagStyle;
use SitPHP\Formatters\TextElement;

class TextElementTest extends TestCase
{
    /*
     * Test add content
     */
    function testAddContent(){
        $text_el = new TextElement('message 1');
        $text_el_2 = new TextElement('message 2');
        $text_el->addContent($text_el_2);
        $this->assertEquals(['message 1', $text_el_2], $text_el->getContent());
    }
    function testInvalidAddContentShouldFail(){
        $this->expectException(\InvalidArgumentException::class);
        new TextElement(new \stdClass());
    }

    /*
     *
     */
    function testSetContent()
    {
        $text_el = new TextElement('message 1');
        $text_el_2 = new TextElement('message 2');

        $text_el->setContent(['message 1', $text_el_2]);
        $this->assertEquals(['message 1', $text_el_2] , $text_el->getContent());
    }
    /*
     * Test get text
     */
    function testGetText(){
        $text_el = new TextElement('message 1');
        $text_el_2 = new TextElement('message 2');
        $text_el->addContent($text_el_2);
        $this->assertEquals('message 1message 2', $text_el->getText());
    }

    /*
     * Test style
     */
    function testGetStyleShouldReturnInstanceOfStyle(){
        $text_el = new TextElement('message');
        $this->assertInstanceOf(TagStyle::class, $text_el->getStyle());
    }
    function testStyleShouldBeApplied(){
        $text_el = new TextElement('message');
        $text_el
            ->setColor('red')
            ->setBackgroundColor('blue')
            ->highlight(true)
            ->blink(true)
            ->underline(true)
            ->bold(true);

        $this->assertEquals('red', $text_el->getColor());
        $this->assertEquals('blue', $text_el->getBackgroundColor());
        $this->assertTrue($text_el->isHighlighted());
        $this->assertTrue($text_el->isBlinking());
        $this->assertTrue($text_el->isUnderlined());
        $this->assertTrue($text_el->isBold());
    }
}
