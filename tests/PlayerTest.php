<?php
declare(strict_types = 1);
namespace Slothsoft\MTG;

use PHPUnit\Framework\TestCase;

/**
 * PlayerTest
 *
 * @see Player
 *
 * @todo auto-generated
 */
final class PlayerTest extends TestCase {
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(Player::class), "Failed to load class 'Slothsoft\MTG\Player'!");
    }
}