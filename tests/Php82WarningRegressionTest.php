<?php
declare(strict_types = 1);
namespace Slothsoft\MTG;

use PHPUnit\Framework\TestCase;

final class Php82WarningRegressionTest extends TestCase {

    /**
     * @runInSeparateProcess
     */
    public function testCardClassificationKeepsItsFallbacks(): void {
        $card = ['type' => 'Unrecognized type'];
        $this->assertSame('Other', OracleInfo::getCardTypeName($card));
        $this->assertSame(14, OracleInfo::getCardTypeIndex($card));
        $card['type'] = 'Token Creature';
        $this->assertSame('Token', OracleInfo::getCardTypeName($card));
        $this->assertSame(7, OracleInfo::getCardTypeIndex($card));
        $this->assertSame(5, OracleInfo::getColorIndex('unknown'));
    }
}
