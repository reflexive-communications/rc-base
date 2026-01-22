<?php

namespace Civi\RcBase\Utils;

use Civi\RcBase\Exception\MissingArgumentException;
use Civi\RcBase\HeadlessTestCase;

/**
 * @group headless
 */
class CLITest extends HeadlessTestCase
{
    /**
     * @return array[]
     */
    public function provideOptions(): array
    {
        return [
            'empty options' => ['', [], []],
            'invalid options' => ['%&-_*  :::()', ['required_option', 'flag:::', 'other%$£&'], []],
            'required' => ['r:', ['req:'], ['r' => 'value', 'req' => 'value2']],
            'flag present' => ['f', ['flag', 'other-flag'], ['f' => true, 'flag' => true, 'other-flag' => true]],
            'flag missing' => ['x', ['not', 'missing-flag'], []],
            'combined' => ['  r::::f%  x', ['req:', 'invalid-req::', 'invalid+flag', 'flag', 'missing-flag'], ['r' => 'value', 'f' => true, 'req' => 'value2', 'flag' => true]],
        ];
    }

    /**
     * @dataProvider provideOptions
     *
     * @param string $short_options
     * @param array $long_options
     * @param array $expected
     *
     * @return void
     * @throws \Civi\RcBase\Exception\MissingArgumentException
     */
    public function testParseOptions(string $short_options, array $long_options, array $expected)
    {
        $args = ['-r', 'value', '-f', '-u', 'unknown value', '--req', 'value2', '--flag', 'value for flag', '--other-flag'];
        $options = CLI::parseArguments($args, $short_options, $long_options);
        self::assertEquals($expected, $options, 'Options parsed incorrectly');
    }

    /**
     * @return void
     * @throws \Civi\RcBase\Exception\MissingArgumentException
     */
    public function testParseOptionsWithMissingRequiredOptionValueThrowsException()
    {
        self::expectException(MissingArgumentException::class);
        self::expectExceptionMessage('option value is not passed');
        CLI::parseArguments(['-r'], 'r:');
    }

    /**
     * @return array
     */
    public function provideColors(): array
    {
        return [
            'normal' => ['normal', `tput sgr0`],
            'bold' => ['bold', `tput bold`],
            'red' => ['red', `tput setaf 1`],
            'green' => ['green', `tput setaf 2`],
            'yellow' => ['yellow', `tput setaf 3`],
            'invalid' => ['invalid', ''],
        ];
    }

    /**
     * @dataProvider provideColors
     */
    public function testColor(string $color, string $expected)
    {
        self::assertSame($expected, CLI::color($color), 'Wrong color escape sequence');
    }

    /**
     * @return void
     */
    public function testColorWithUnknownTerminal()
    {
        // set TERM env var to unknown value
        $old_term = getenv('TERM');
        putenv('TERM=unknown');

        self::assertSame('', CLI::color('bold'), 'Wrong color escape sequence on unknown terminal');

        // restore TERM
        putenv("TERM=$old_term");
    }

    /**
     * @return void
     */
    public function testPrint()
    {
        $msg = "This is a test message with new line\n";
        CLI::print($msg);
        self::expectOutputString($msg);
    }

    /**
     * @return void
     */
    public function testPrintLine()
    {
        $msg = 'This is a test message without new line';
        CLI::printLine($msg);
        self::expectOutputString($msg."\n");
    }

    /**
     * @return void
     */
    public function testPrintError()
    {
        $errorMessage = 'This is an error message';
        CLI::printError($errorMessage);
        // Check stdout is empty
        self::expectOutputString('');
    }

    /**
     * @return void
     */
    public function testPrintHeader()
    {
        $header = 'This is a header';
        CLI::printHeader($header);
        self::expectOutputString(CLI::color('yellow').$header.CLI::color('normal')."\n");
    }

    /**
     * @return void
     */
    public function testPrintHeaderWithoutColor()
    {
        $header = 'This is a header without color';
        CLI::printHeader($header, false);
        self::expectOutputString("{$header}\n");
    }

    /**
     * @return void
     */
    public function testPrintStatus()
    {
        $status = 'This is a status message';
        CLI::printStatus($status);
        self::expectOutputString(CLI::color('yellow').$status.CLI::color('normal'));
    }

    /**
     * @return void
     */
    public function testPrintStatusWithoutColor()
    {
        $status = 'This is a status message without color';
        CLI::printStatus($status, false);
        self::expectOutputString($status);
    }

    /**
     * @return void
     */
    public function testPrintFinish()
    {
        CLI::printFinish();
        self::expectOutputString(CLI::color('green').CLI::color('bold').'Done.'.CLI::color('normal')."\n");
    }

    /**
     * @return void
     */
    public function testPrintFinishWithoutColor()
    {
        $message = 'Ready without color';
        CLI::printFinish($message, false);
        self::expectOutputString("{$message}\n");
    }

    /**
     * @return void
     */
    public function testPrintFinishWithCustomMessage()
    {
        $message = 'This is a custom message';
        CLI::printFinish($message);
        self::expectOutputString(CLI::color('green').CLI::color('bold').$message.CLI::color('normal')."\n");
    }
}
