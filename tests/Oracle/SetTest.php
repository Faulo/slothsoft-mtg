<?php
declare(strict_types = 1);
namespace Slothsoft\MTG\Oracle;

use PHPUnit\Framework\TestCase;

/**
 * SetTest
 *
 * @see Set
 *
 * @todo auto-generated
 */
class SetTest extends TestCase {
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(Set::class), "Failed to load class 'Slothsoft\MTG\Oracle\Set'!");
    }
}