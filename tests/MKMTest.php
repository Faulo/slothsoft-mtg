<?php
declare(strict_types = 1);
namespace Slothsoft\MTG;

use PHPUnit\Framework\TestCase;

/**
 * MKMTest
 *
 * @see MKM
 *
 * @todo auto-generated
 */
final class MKMTest extends TestCase {
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(MKM::class), "Failed to load class 'Slothsoft\MTG\MKM'!");
    }
}