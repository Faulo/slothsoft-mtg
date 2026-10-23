<?php
declare(strict_types = 1);
namespace Slothsoft\MTG;

use DOMDocument;
use Exception;
use PHPUnit\Framework\TestCase;

/**
 * @runTestsInSeparateProcesses
 */
final class SerializationTest extends TestCase {

    public function testOracleRoundTripStoresOnlyItsDatabaseName(): void {
        $oracle = new Oracle('serialization-test', new DOMDocument());
        $serialized = serialize($oracle);
        $this->assertStringStartsWith('O:', $serialized);
        $restored = unserialize($serialized);
        $this->assertInstanceOf(Oracle::class, $restored);
        $this->assertSame(['db' => 'serialization-test'], $restored->__serialize());
        $this->assertStringNotContainsString('DOMDocument', $serialized);
    }

    /**
     * @throws Exception
     */
    public function testOracleCanStillReadItsLegacyPayload(): void {
        $oracle = new Oracle('old');
        $oracle->unserialize('restored');
        $this->assertSame('restored', $oracle->serialize());
    }

    public function testTableRoundTripReinitializesTheTable(): void {
        $table = new SerializationTableStub('test-db', 'test-table');
        $serialized = serialize($table);
        $this->assertStringStartsWith('O:', $serialized);
        $restored = unserialize($serialized);
        $this->assertInstanceOf(SerializationTableStub::class, $restored);
        $this->assertSame('test-db', $restored->getDBName());
        $this->assertSame('test-table', $restored->getTableName());
        $this->assertSame(1, $restored->initializations);
    }

    public function testTableCanStillReadItsLegacyPayload(): void {
        $table = new SerializationTableStub('old-db', 'old-table');
        $table->unserialize(serialize(['db' => 'new-db', 'table' => 'new-table']));
        $this->assertSame('new-db', $table->getDBName());
        $this->assertSame('new-table', $table->getTableName());
    }
}

class SerializationTableStub extends OracleTable {

    public int $initializations = 0;

    public function init(): void {
        $this->initializations ++;
    }

    protected function install(): void {
    }
}
