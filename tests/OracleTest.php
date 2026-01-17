<?php
declare(strict_types = 1);
namespace Slothsoft\MTG;

use PHPUnit\Framework\TestCase;

/**
 * OracleTest
 *
 * @see Oracle
 *
 * @todo auto-generated
 */
final class OracleTest extends TestCase {
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(Oracle::class), "Failed to load class 'Slothsoft\MTG\Oracle'!");
    }
}