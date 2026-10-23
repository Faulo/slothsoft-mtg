<?php
declare(strict_types = 1);
namespace Slothsoft\MTG\Oracle;

use PHPUnit\Framework\TestCase;

/**
 * CardTest
 *
 * @see Card
 *
 */
final class CardTest extends TestCase {
    
    public function testClassExists(): void {
        $this->assertTrue(class_exists(Card::class), "Failed to load class 'Slothsoft\MTG\Oracle\Card'!");
    }

    /**
     * @runInSeparateProcess
     */
    public function testTheImportWorkerCanReadTheCompleteCardRecord(): void {
        $data = ['name' => 'Forest', 'type' => 'Basic Land', 'expansion_name' => 'Test', 'oracle_id' => 1];
        $card = new Card($data);
        $this->assertSame('Land', $card->getTypeName());
        $this->assertSame('Test', $card->getSetName());
        $this->assertSame($data, $card->getData());
    }
}
