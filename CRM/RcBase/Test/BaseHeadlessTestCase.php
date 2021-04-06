<?php

use Civi\Test;
use Civi\Test\Api3DocTrait;
use Civi\Test\ContactTestTrait;
use Civi\Test\DbTestTrait;
use Civi\Test\GenericAssertionsTrait;
use Civi\Test\HeadlessInterface;
use Civi\Test\MailingTestTrait;
use PHPUnit\Framework\TestCase;

/**
 * Base test class for headless tests
 * Contains helper functions for testing
 */
class CRM_RcBase_Test_BaseHeadlessTestCase extends TestCase implements HeadlessInterface
{
    use Api3DocTrait;
    use GenericAssertionsTrait;
    use DbTestTrait;
    use ContactTestTrait;
    use MailingTestTrait;

    /**
     * External ID counter
     *
     * @var int
     */
    protected static $externalID = 0;

    /**
     * Contact sequence counter
     *
     * @var int
     */
    protected static $contactSequence = 0;

    /**
     * Apply a forced rebuild of DB, thus
     * create a clean DB before running tests
     *
     * @throws CRM_Extension_Exception_ParseException
     */
    public static function setUpBeforeClass(): void
    {
        // Resets DB
        Test::headless()
            ->installMe(__DIR__)
            ->apply(true);
    }

    /**
     * The setupHeadless function runs at the start of each test case, right before
     * the headless environment reboots.
     *
     * It should perform any necessary steps required for putting the database
     * in a consistent baseline -- such as loading schema and extensions.
     *
     * The utility `\Civi\Test::headless()` provides a number of helper functions
     * for managing this setup, and it includes optimizations to avoid redundant
     * setup work.
     *
     * @see \Civi\Test
     */
    public function setUpHeadless()
    {
        return Test::headless()
            ->installMe(__DIR__)
            ->apply();
    }

    /**
     * Setup method run before each test
     */
    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Get next ID in sequence (auto-increment)
     *
     * @return string Next ID
     */
    protected static function getNextExternalID(): string
    {
        self::$externalID++;

        return (string)self::$externalID;
    }

    /**
     * Get next contact sequence number (auto-increment)
     *
     * @return int Next ID
     */
    protected static function getNextContactSequence(): int
    {
        self::$contactSequence++;

        return self::$contactSequence;
    }

    /**
     * Sample contact parameters, next in the sequence
     *
     * @return array Contact parameters
     */
    protected function nextSampleIndividual(): array
    {
        // Assemble Contact data
        $contact = $this->sampleContact('Individual', self::getNextContactSequence());
        $contact['external_identifier'] = self::getNextExternalID();
        // Remove unnecessary fields
        unset($contact['prefix_id']);
        unset($contact['suffix_id']);
        // Remove email
        unset($contact['email']);

        return $contact;
    }

    /**
     * Executes a raw SQL query on the DB
     *
     * @param string $query SQL query
     *
     * @return array Query results indexed by column name
     */
    protected function rawSqlQuery(string $query): array
    {
        if (empty($query)) {
            $this->fail('Missing SQL query');
        }

        $pdo = Test::pdo();

        return $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get next auto-increment value for an SQL table
     *
     * @param string $table_name Name of table
     *
     * @return int Next auto-increment value
     */
    protected function getNextAutoIncrementValue(string $table_name): int
    {
        if (empty($table_name)) {
            $this->fail('Missing table name');
        }

        $results = $this->rawSqlQuery("SHOW TABLE STATUS WHERE name='${table_name}'");

        if (count($results) < 1) {
            $this->fail("Table ${table_name} not found in DB");
        }

        return (int)$results[0]['Auto_increment'];
    }

    /**
     * Call cv api4 with get action
     *
     * @param string $entity Entity to work on (Contact, Email, etc.)
     * @param array $select Fields to return
     *   Example:
     *   $select = ['contact_type', 'first_name', 'external_identifier']
     * @param array $where Where conditions to filter results (if more given they are joined by AND)
     *   Example:
     *   $where = [
     *   'contact_type=Individual',
     *   'first_name like "Adams%",
     *   ]
     *
     * @return array Results
     */
    protected function cvApi4Get(string $entity, array $select = [], array $where = []): array
    {
        if (empty($entity)) {
            $this->fail('Missing entity name');
        }

        // Parse parameters and assemble command
        $select_string = '';
        if (!empty($select)) {
            $select_string = implode(',', $select);
            $select_string = "+s '${select_string}'";
        }

        $where_string = '';
        foreach ($where as $item) {
            $where_string .= "+w '${item}' ";
        }

        $command = "api4 ${entity}.get ${select_string} ${where_string}";

        // Run command
        $result = cv($command);

        // Check results
        if (!is_array($result)) {
            $this->fail("Not an array returned from '${command}'");
        }

        // Check each record
        foreach ($result as $record) {
            if (!is_array($result)) {
                $this->fail("Not an array returned from '${command}'");
            }

            // Check if selected fields are present
            foreach ($select as $item) {
                if (!array_key_exists($item, $record)) {
                    $this->fail("${item} not returned");
                }
            }
        }

        return $result;
    }

    /**
     * Call cv api4 with create action
     *
     * @param string $entity Entity to work on (Contact, Email, etc.)
     * @param array $params Params of entity
     *
     * @return int Created entity ID
     */
    protected function cvApi4Create(string $entity, array $params = []): int
    {
        if (empty($entity)) {
            $this->fail('Missing entity name');
        }

        // Parse parameters and assemble command
        $values = [
            'values' => $params,
        ];
        $values_json = json_encode($values);

        $command = "api4 ${entity}.create '${values_json}'";

        // Run command
        $result = cv($command);

        // Check results
        if (!is_array($result)) {
            $this->fail("Not an array returned from '${command}'");
        }
        if (count($result) != 1) {
            $this->fail("Not one result returned from '${command}'");
        }
        if (!is_array($result[0])) {
            $this->fail("Not an array returned from '${command}'");
        }
        if (!array_key_exists('id', $result[0])) {
            $this->fail('ID not found.');
        }

        return (int)$result[0]['id'];
    }
}
