<?php

namespace Civi\RcBase\IOProcessor;

use Civi;
use Civi\RcBase\HeadlessTestCase;

/**
 * @group headless
 */
class UrlEncodedFormTest extends HeadlessTestCase
{
    /**
     * IOProcessor service
     *
     * @var \Civi\RcBase\IOProcessor\UrlEncodedForm
     */
    protected UrlEncodedForm $service;

    /**
     * @return void
     */
    public function setUpHeadless(): void
    {
        $this->service = Civi::service('IOProcessor.UrlEncodedForm');
    }

    /**
     * @return void
     */
    public function testDecode()
    {
        $data = ['string' => 'string', 'integer' => '1'];
        $encoded = http_build_query($data);
        self::assertSame($data, $this->service->decode($encoded), 'Invalid data returned.');
    }

    /**
     * @return void
     */
    public function testParseGet()
    {
        $get = ['token' => '12345', 'route' => 'echo'];
        $_GET = $get;
        self::assertSame($get, UrlEncodedForm::parseGet(), 'Invalid data returned.');
    }

    /**
     * @return void
     */
    public function testDecodePost()
    {
        $_POST = ['string' => 'string', 'integer' => '1'];
        self::assertSame($_POST, $this->service->decodePost(), 'Invalid data returned.');
    }

    /**
     * @return void
     */
    public function testParseRequest()
    {
        $_GET = ['token' => '12345', 'route' => 'echo'];
        $_POST = ['string' => 'string', 'integer' => 1, 'bool' => true];
        $expected = ['token' => '12345', 'route' => 'echo', 'string' => 'string', 'integer' => 1, 'bool' => true];

        $_REQUEST = array_merge($_GET, $_POST);
        self::assertSame($expected, UrlEncodedForm::parseRequest(), 'Invalid data returned.');
    }

    /**
     * @return void
     */
    public function testSanitize()
    {
        $post = ['string  ' => 'string', 'integer<script>hack</script>' => 1, 'bool' => true];
        $post_sanitized = ['string' => 'string', 'integer' => 1, 'bool' => true];
        $_POST = $post;
        self::assertSame($post_sanitized, UrlEncodedForm::parsePost(), 'Invalid data returned.');
    }
}
