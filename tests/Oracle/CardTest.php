<?php
declare(strict_types = 1);
namespace Slothsoft\MTG\Oracle;

use PHPUnit\Framework\TestCase;

/**
 * CardTest
 *
 * @see Card
 *
 * @todo auto-generated
 */
class CardTest extends TestCase {
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(Card::class), "Failed to load class 'Slothsoft\MTG\Oracle\Card'!");
    }
}