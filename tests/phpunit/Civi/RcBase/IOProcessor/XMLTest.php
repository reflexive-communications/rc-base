<?php

namespace Civi\RcBase\IOProcessor;

use Civi\RcBase\Exception\InvalidArgumentException;
use CRM_RcBase_HeadlessTestCase;
use SimpleXMLElement;

/**
 * Test XML Processor class
 *
 * @group headless
 */
class XMLTest extends CRM_RcBase_HeadlessTestCase
{
    /**
     * @return void
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     */
    public function testValidXmlToArray()
    {
        $xml_string = <<<XML
<?xml version='1.0' standalone='yes'?>
<movies

>
 <movie\n\n>
  <title>
    PHP: Behind the Parser
  </title>
  <utf-8>öüóőúéáűíÖÜÓŐÚÉÁŰ</utf-8>
  <urlencode>
    %C3%B6%C3%BC%C3%B3%C5%91%C3%BA
  </urlencode>
  <characters>
   <character>
    <name>Ms. <br/>Coder</name>
    <actor>Onlivia Áctőré</actor>
   </character>
   <character>
    <name>"Mr. Coder"</name>
    <actor>'El Act&#211;r'</actor>
   </character>
  </characters>
  <plot>
   So, this language. It's like, a programming language. Or is it a
   scripting language? All is revealed in this thrilling horror spoof
   of a documentary.
  </plot>
  <great-lines>
   <line>PHP solves all my web problems</line>
  </great-lines>
  <rating type="thumbs">7</rating>
  <rating type="stars">5</rating>
 </movie>
</movies>
XML;
        $expected = [
            'movie' => [
                'title' => 'PHP: Behind the Parser',
                'utf-8' => 'öüóőúéáűíÖÜÓŐÚÉÁŰ',
                'urlencode' => '%C3%B6%C3%BC%C3%B3%C5%91%C3%BA',
                'characters' => [
                    'character' => [
                        ['name' => 'Ms. Coder', 'actor' => 'Onlivia Áctőré',],
                        ['name' => '"Mr. Coder"', 'actor' => "'El ActÓr'",],
                    ],
                ],
                'plot' => 'So, this language. It\'s like, a programming language. Or is it a scripting language? All is revealed in this thrilling horror spoof of a documentary.',
                'great-lines' => ['line' => 'PHP solves all my web problems',],
                'rating' => ['7', '5',],
            ],
        ];
        $result = XML::parse($xml_string);
        self::assertSame($expected, $result, 'Invalid XML returned.');
    }

    /**
     * @return void
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     */
    public function testValidXmlToObject()
    {
        $xml_string = '<?xml version="1.0"?>
<movies><movie><title>Star Wars</title><characters><character class="Jedi" role="Good guy">Anakin Skywalker</character><character class="Sith" role="Bad guy">Palpatine</character></characters></movie><movie><title>Harry Potter</title><characters><character class="Wizard" role="Good guy">Harry Potter</character><character class="Dark Wizard" role="Bad guy">Voldemort</character></characters></movie></movies>';

        // Create XML Object manually
        $expected = new SimpleXMLElement("<?xml version='1.0'?><movies></movies>");

        // Add movies
        $movie_1 = $expected->addChild('movie');
        $movie_1->addChild('title', 'Star Wars');
        $movie_2 = $expected->addChild('movie');
        $movie_2->addChild('title', 'Harry Potter');

        // Add characters
        $characters_1 = $movie_1->addChild('characters');
        $characters_2 = $movie_2->addChild('characters');

        $character = $characters_1->addChild('character', 'Anakin Skywalker');
        $character->addAttribute('class', 'Jedi');
        $character->addAttribute('role', 'Good guy');

        $character = $characters_1->addChild('character', 'Palpatine');
        $character->addAttribute('class', 'Sith');
        $character->addAttribute('role', 'Bad guy');

        $character = $characters_2->addChild('character', 'Harry Potter');
        $character->addAttribute('class', 'Wizard');
        $character->addAttribute('role', 'Good guy');

        $character = $characters_2->addChild('character', 'Voldemort');
        $character->addAttribute('class', 'Dark Wizard');
        $character->addAttribute('role', 'Bad guy');

        $result = XML::parse($xml_string, false);

        self::assertInstanceOf(SimpleXMLElement::class, $result, 'Not a SimpleXMLElement returned.');
        self::assertEquals($expected, $result, 'Invalid XML returned.');
        self::assertEquals($expected->asXML(), $result->asXML(), 'Different XML returned.');
    }

    /**
     * @return void
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     */
    public function testInvalidXmlThrowsException()
    {
        $invalid_xml = '<?xml version="1.0"?><movies><movie><title>Star Wars</title>';
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('Invalid XML received');
        XML::parse($invalid_xml);
    }

    /**
     * @return void
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     * @throws \Civi\RcBase\Exception\RunTimeException
     */
    public function testParseFileStream()
    {
        $expected = [
            'movie' => [
                [
                    'title' => 'Star Wars',
                    'characters' => ['character' => ['Anakin Skywalker', 'Palpatine']],
                ],
                [
                    'title' => 'Harry Potter',
                    'characters' => ['character' => ['Harry Potter', 'Voldemort']],
                ],
            ],
        ];
        $result = XML::parseStream('file://'.__DIR__.'/test.xml');
        self::assertSame($expected, $result, 'Invalid XML returned.');
    }

    /**
     * @return void
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     * @throws \Civi\RcBase\Exception\RunTimeException
     */
    public function testParsePost()
    {
        $xml = <<<XML
<?xml version='1.0' standalone='yes'?>
<movies

>
 <movie\n\n>
  <title>
    PHP: Behind the Parser
  </title>
  <utf-8>öüóőúéáűíÖÜÓŐÚÉÁŰ</utf-8>
  <urlencode>
    %C3%B6%C3%BC%C3%B3%C5%91%C3%BA
  </urlencode>
  <characters>
   <character>
    <name>Ms. <br/>Coder</name>
    <actor>Onlivia Áctőré</actor>
   </character>
   <character>
    <name>"Mr. Coder"</name>
    <actor>'El Act&#211;r'</actor>
   </character>
  </characters>
  <plot>
   So, this language. It's like, a programming language. Or is it a
   scripting language? All is revealed in this thrilling horror spoof
   of a documentary.
  </plot>
  <great-lines>
   <line>PHP solves all my web problems</line>
  </great-lines>
  <rating type="thumbs">7</rating>
  <rating type="stars">5</rating>
 </movie>
</movies>
XML;
        $expected = [
            'movie' => [
                'title' => 'PHP: Behind the Parser',
                'utf-8' => 'öüóőúéáűíÖÜÓŐÚÉÁŰ',
                'urlencode' => '%C3%B6%C3%BC%C3%B3%C5%91%C3%BA',
                'characters' => [
                    'character' => [
                        ['name' => 'Ms. Coder', 'actor' => 'Onlivia Áctőré',],
                        ['name' => '"Mr. Coder"', 'actor' => "'El ActÓr'",],
                    ],
                ],
                'plot' => 'So, this language. It\'s like, a programming language. Or is it a scripting language? All is revealed in this thrilling horror spoof of a documentary.',
                'great-lines' => ['line' => 'PHP solves all my web problems',],
                'rating' => ['7', '5',],
            ],
        ];

        // Register Mock wrapper
        stream_wrapper_unregister('php');
        stream_wrapper_register('php', '\Civi\RcBase\IOProcessor\MockPHPStream');

        // Feed JSON to stream
        file_put_contents('php://input', $xml);

        // Parse raw data from the request body
        $result = XML::parsePost();

        // Restore original wrapper
        stream_wrapper_restore('php');

        self::assertSame($expected, $result, 'Invalid XML returned.');
    }

    /**
     * @return void
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     */
    public function testSkipXxeContent()
    {
        $xxe_xml = <<<XML
<?xml version="1.0" encoding="ISO-8859-1"?>
<!DOCTYPE foo [
<!ELEMENT foo ANY >
<!ENTITY xxe SYSTEM "file:///etc/passwd" >]>
<foo>&xxe;<title>Star Wars</title></foo>
XML;
        $expected = [
            'xxe' => ['xxe' => null,],
            'title' => 'Star Wars',
        ];

        $result = XML::parse($xxe_xml);

        self::assertSame($expected, $result, 'Invalid XML returned.');
    }
}
