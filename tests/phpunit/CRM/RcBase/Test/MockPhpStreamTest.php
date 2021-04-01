<?php

use PHPUnit\Framework\TestCase;

/**
 * Test MockPhpStream Processor class
 *
 * @group unit
 */
class CRM_RcBase_Test_MockPhpStreamTest extends TestCase
{

    /**
     * Provide streams for the php:// stream wrapper
     *
     * @return \string[][]
     */
    public function provideStreams()
    {
        return [
            'std in'    => ['stdin'],
            'std out'   => ['stdout'],
            'std error' => ['stderr'],
            'input'     => ['input'],
        ];
    }

    /**
     * @dataProvider provideStreams
     *
     * @param $stream
     */
    public function testCheckOperation($stream)
    {
        // Register Mock wrapper
        stream_wrapper_unregister("php");
        stream_wrapper_register("php", "CRM_RcBase_Test_MockPhpStream");

        // Write & read data
        $data = "original";
        file_put_contents("php://${stream}", $data);
        $result = file_get_contents("php://${stream}");
        $this->assertSame($data, $result, 'Invalid data returned.');

        // Update data
        $data = "changed";
        file_put_contents('php://input', $data);
        $result_changed = file_get_contents('php://input');
        $this->assertSame($data, $result_changed, 'Invalid data returned.');

        $this->assertNotSame($result, $result_changed, 'Result not changed.');
    }

}
