<?php

namespace Civi\RcBase\Api4;

// Need to include trait manually as autoload does not work (why?)
require_once 'Civi/RcBase/Api4/EntityPagingTrait.php';

use Civi\RcBase\Exception\InvalidArgumentException;
use Civi\RcBase\HeadlessTestCase;

/**
 * @group headless
 */
class EntityPagingTraitTest extends HeadlessTestCase
{
    use EntityPagingTrait;

    /**
     * @return void
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     */
    public function testValidateParamsWithInvalidBatchSizeThrowsException()
    {
        $this->batchSize = 0;
        $this->idOffset = 1;
        $this->maxProcessed = 1;
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('batch size');
        $this->validatePagingParams();
    }

    /**
     * @return void
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     */
    public function testValidateParamsWithInvalidIdOffsetThrowsException()
    {
        $this->batchSize = 1;
        $this->idOffset = 0;
        $this->maxProcessed = 1;
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('ID offset');
        $this->validatePagingParams();
    }

    /**
     * @return void
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     */
    public function testValidateParamsWithInvalidMaxProcessedThrowsException()
    {
        $this->batchSize = 1;
        $this->idOffset = 1;
        $this->maxProcessed = -1;
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('max processed');
        $this->validatePagingParams();
    }
}
