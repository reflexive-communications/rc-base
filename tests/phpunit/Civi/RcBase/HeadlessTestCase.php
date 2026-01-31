<?php

namespace Civi\RcBase;

use Civi\Test;
use Civi\Test\HeadlessInterface;
use PHPUnit\Framework\TestCase;

/**
 * @group headless
 */
class HeadlessTestCase extends TestCase implements HeadlessInterface
{
    /**
     * Apply a forced rebuild of DB, thus
     * create a clean DB before running tests
     */
    public static function setUpBeforeClass(): void
    {
        // Resets DB
        Test::headless()
            ->install(['rc-base'])
            ->apply(true);
    }

    /**
     * @return void
     */
    public function setUpHeadless(): void
    {
    }
}
