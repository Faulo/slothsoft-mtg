<?php
declare(strict_types = 1);
namespace Slothsoft\MTG;

use PHPUnit\Framework\TestCase;

/**
 * OracleDeckTest
 *
 * @see OracleDeck
 *
 * @todo auto-generated
 */
final class OracleDeckTest extends TestCase {
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(OracleDeck::class), "Failed to load class 'Slothsoft\MTG\OracleDeck'!");
    }
}