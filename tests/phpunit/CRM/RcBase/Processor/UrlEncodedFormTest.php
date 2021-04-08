<?php

use PHPUnit\Framework\TestCase;

/**
 * Test URL encoded form Processor class
 *
 * @group unit
 */
class CRM_RcBase_Processor_UrlEncodedFormTest extends TestCase
{
    public function testParseGet()
    {
        unset($_GET);
        $get = ['token' => '12345', 'route' => 'echo',];
        $_GET = $get;
        self::assertSame($get, CRM_RcBase_Processor_UrlEncodedForm::parseGet(), 'Invalid data returned.');
    }

    public function testParsePost()
    {
        unset($_POST);
        $post = ['string' => 'string', 'integer' => 1, 'bool' => true];
        $_POST = $post;
        self::assertSame($post, CRM_RcBase_Processor_UrlEncodedForm::parsePost(), 'Invalid data returned.');
    }

    public function testParseRequest()
    {
        unset($_GET);
        unset($_POST);
        unset($_REQUEST);

        $get = ['token' => '12345', 'route' => 'echo',];
        $post = ['string' => 'string', 'integer' => 1, 'bool' => true];
        $expected = ['token' => '12345', 'route' => 'echo', 'string' => 'string', 'integer' => 1, 'bool' => true];

        $_REQUEST = array_merge($get, $post);
        self::assertSame($expected, CRM_RcBase_Processor_UrlEncodedForm::parseRequest(), 'Invalid data returned.');
    }

    public function testSanitize()
    {
        unset($_POST);
        $post = ['string  ' => 'string', 'integer<script>hack</script>' => 1, 'bool' => true];
        $post_sanitized = ['string' => 'string', 'integerhack' => 1, 'bool' => true];
        $_POST = $post;
        self::assertSame($post_sanitized, CRM_RcBase_Processor_UrlEncodedForm::parsePost(), 'Invalid data returned.');
    }
}
