<?php

use PHPUnit\Framework\TestCase;

class ReflectionTestClass
{
    private $restricted;

    public function __construct()
    {
        $this->restricted = 'private';
    }

    protected function privateMethod(string $param = '')
    {
        return "restricted_${param}";
    }
}

/**
 * Test Utils Basic class
 *
 * @group unit
 */
class CRM_RcBase_Test_UtilsBasicTest extends TestCase
{
    public function testCallingRestrictedMethodThrowsException()
    {
        $test = new ReflectionTestClass();
        self::expectException(Error::class);
        $test->privateMethod();
    }

    public function testAccessingRestrictedPropertyThrowsException()
    {
        $test = new ReflectionTestClass();
        self::expectException(Error::class);
        $test->restricted = 'public';
    }

    /**
     * @throws \ReflectionException
     */
    public function testGetAndSetProtectedProperty()
    {
        $test = new ReflectionTestClass();
        CRM_RcBase_Test_UtilsBasic::setProtectedProperty($test, 'restricted', 'public');
        self::assertSame('public', CRM_RcBase_Test_UtilsBasic::getProtectedProperty($test, 'restricted'), 'Bad value returned');
    }

    /**
     * @throws \ReflectionException
     */
    public function testInvokeProtectedMethod()
    {
        $test = new ReflectionTestClass();

        // Invoke with no parameters
        self::assertSame('restricted_', CRM_RcBase_Test_UtilsBasic::invokeProtectedMethod($test, 'privateMethod'), 'Bad value returned');

        // Invoke with
        self::assertSame(
            'restricted_test',
            CRM_RcBase_Test_UtilsBasic::invokeProtectedMethod($test, 'privateMethod', ['test']),
            'Bad value returned'
        );
    }
}
