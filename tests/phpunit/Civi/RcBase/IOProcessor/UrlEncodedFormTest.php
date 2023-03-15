<?php

namespace Civi\RcBase\IOProcessor;

use Civi\RcBase\HeadlessTestCase;

/**
 * Test URL encoded form Processor class
 *
 * @group headless
 */
class UrlEncodedFormTest extends HeadlessTestCase
{
    /**
     * @return void
     */
    public function testParseGet()
    {
        unset($_GET);
        $get = ['token' => '12345', 'route' => 'echo'];
        $_GET = $get;
        self::assertSame($get, UrlEncodedForm::parseGet(), 'Invalid data returned.');
    }

    /**
     * @return void
     */
    public function testParsePost()
    {
        unset($_POST);
        $post = ['string' => 'string', 'integer' => 1, 'bool' => true];
        $_POST = $post;
        self::assertSame($post, UrlEncodedForm::parsePost(), 'Invalid data returned.');
    }

    /**
     * @return void
     */
    public function testParseRequest()
    {
        unset($_GET);
        unset($_POST);
        unset($_REQUEST);

        $get = ['token' => '12345', 'route' => 'echo'];
        $post = ['string' => 'string', 'integer' => 1, 'bool' => true];
        $expected = ['token' => '12345', 'route' => 'echo', 'string' => 'string', 'integer' => 1, 'bool' => true];

        $_REQUEST = array_merge($get, $post);
        self::assertSame($expected, UrlEncodedForm::parseRequest(), 'Invalid data returned.');
    }

    /**
     * @return void
     */
    public function testSanitize()
    {
        unset($_POST);
        $post = ['string  ' => 'string', 'integer<script>hack</script>' => 1, 'bool' => true];
        $post_sanitized = ['string' => 'string', 'integer' => 1, 'bool' => true];
        $_POST = $post;
        self::assertSame($post_sanitized, UrlEncodedForm::parsePost(), 'Invalid data returned.');
    }
}
