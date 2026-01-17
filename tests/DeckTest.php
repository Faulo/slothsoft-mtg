<?php
declare(strict_types = 1);
namespace Slothsoft\MTG;

use PHPUnit\Framework\TestCase;

/**
 * DeckTest
 *
 * @see Deck
 *
 * @todo auto-generated
 */
final class DeckTest extends TestCase {
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(Deck::class), "Failed to load class 'Slothsoft\MTG\Deck'!");
    }
}