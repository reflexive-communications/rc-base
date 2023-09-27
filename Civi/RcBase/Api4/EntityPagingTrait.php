<?php

namespace Civi\RcBase\Api4;

use Civi\RcBase\Exception\InvalidArgumentException;

/**
 * Properties and methods for APIv4 actions that page through entities
 */
trait EntityPagingTrait
{
    /**
     * Batch size
     *
     * Entities will be processed in this size of batches
     *
     * @var int
     * @required
     */
    protected $batchSize = 1000;

    /**
     * Start processing with this ID
     *
     * With this config you can continue a previous (manual) operation and useful for other debugging purposes.
     * Combined with maxProcessed=1 you can process only one entity.
     * Set idOffset=1 to include all entities.
     *
     * @var int
     * @required
     */
    protected $idOffset = 1;

    /**
     * Action will stop after this number of entities processed
     *
     * Useful for debugging and manual operations. Zero means all entities will be processed.
     * Only the remaining entities (higher IDs than idOffset) will be considered even if maxProcessed=0.
     *
     * @var int
     * @required
     */
    protected $maxProcessed = 0;

    /**
     * Validate paging parameters
     *
     * @return void
     * @throws \Civi\RcBase\Exception\InvalidArgumentException
     */
    protected function validatePagingParams(): void
    {
        if ($this->batchSize < 1) {
            throw new InvalidArgumentException('batch size', 'must be positive');
        }

        if ($this->idOffset < 1) {
            throw new InvalidArgumentException('ID offset', 'must be positive');
        }

        if ($this->maxProcessed < 0) {
            throw new InvalidArgumentException('max processed', 'must be non-negative');
        }
    }
}
