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
}
