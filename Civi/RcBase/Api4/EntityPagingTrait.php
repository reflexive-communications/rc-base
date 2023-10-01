<?php

namespace Civi\RcBase\Api4;

use Civi\RcBase\Exception\InvalidArgumentException;
use Civi\RcBase\Exception\MissingArgumentException;
use Civi\RcBase\Utils\DB;

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
     * Dry-run
     *
     * If true, no changes will be made, just report what would be done
     *
     * @var bool
     */
    protected $dryRun = false;

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

    /**
     * Fetch next page of entities. Use cursor method for paging
     * Note: it's expected that from, select and where are already sanitized & escaped
     *
     * @param string $from Table name optionally with joins
     * @param array $select Columns to select
     * @param string $where Where clause
     * @param int $limit Number of entities to return
     * @param int $id Last retrieved entity ID
     * @param string $id_prefix If ID is ambiguous, specify table alias
     *
     * @return array
     * @throws \Civi\RcBase\Exception\DataBaseException
     * @throws \Civi\RcBase\Exception\MissingArgumentException
     */
    public function fetchNextPage(string $from, array $select, string $where, int $limit, int $id, string $id_prefix = ''): array
    {
        if (empty($select)) {
            throw new MissingArgumentException('select');
        }

        $select_clause = implode(',', $select);
        $where = !empty($where) ? "({$where}) AND" : '';
        $id_prefix = !empty($id_prefix) ? "{$id_prefix}." : '';

        $sql = "SELECT {$select_clause}
                FROM {$from}
                WHERE {$where} {$id_prefix}id > %1
                ORDER BY {$id_prefix}id
                LIMIT %2";
        $params = [
            1 => [$id, 'Positive'],
            2 => [$limit, 'Positive'],
        ];

        return DB::query($sql, $params);
    }
}
