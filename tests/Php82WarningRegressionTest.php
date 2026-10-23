<?php
declare(strict_types = 1);
namespace Slothsoft\MTG;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;

final class Php82WarningRegressionTest extends TestCase {

    public function testEmptyHexCreatesAnEmptyBigInt(): void {
        $number = BigInt::createFromHex('');
        $this->assertSame([], $number->digits);
        $this->assertSame('', $number->toHex());
    }

    public function testHexDigitsKeepTheirLittleEndianOrder(): void {
        $number = BigInt::createFromHex('123456789');
        $this->assertSame([0x6789, 0x2345, 0x1], $number->digits);
        $this->assertSame('000123456789', $number->toHex());
    }

    /**
     * @throws ReflectionException
     */
    public function testLegacyPlayerRepositoryUsesCardNames(): void {
        $repository = $this->getMockBuilder(Deck::class)->disableOriginalConstructor()->onlyMethods(['getCardList', 'getStock'])->getMock();
        $repository->method('getCardList')->willReturn(['Forest' => null]);
        $repository->expects($this->once())->method('getStock')->with('Forest')->willReturn(4);
        $managed = $this->getMockBuilder(Deck::class)->disableOriginalConstructor()->onlyMethods(['getCardList', 'getStock'])->getMock();
        $managed->method('getCardList')->willReturn(['Forest' => null]);
        $managed->expects($this->once())->method('getStock')->with('Forest')->willReturn(2);
        $player = (new ReflectionClass(Player::class))->newInstanceWithoutConstructor();
        $player->repositoryDeck = $repository;
        $player->managedDeckList = [$managed];
        $player->initRepository();
    }

    /**
     * @throws ReflectionException
     */
    public function testOraclePlayerRepositoryUsesStockAndSideboardNames(): void {
        $repository = $this->getMockBuilder(OracleDeck::class)->disableOriginalConstructor()->onlyMethods(['getStockNameList', 'getStock'])->getMock();
        $repository->method('getStockNameList')->willReturn(['Forest']);
        $repository->expects($this->once())->method('getStock')->with('Forest')->willReturn(4);
        $managed = $this->getMockBuilder(OracleDeck::class)->disableOriginalConstructor()->onlyMethods(['getStockNameList', 'getStock', 'getSideboardNameList', 'getSideboard'])->getMock();
        $managed->method('getStockNameList')->willReturn(['Forest']);
        $managed->expects($this->once())->method('getStock')->with('Forest')->willReturn(2);
        $managed->method('getSideboardNameList')->willReturn(['Forest']);
        $managed->expects($this->once())->method('getSideboard')->with('Forest')->willReturn(1);
        $player = (new ReflectionClass(OraclePlayer::class))->newInstanceWithoutConstructor();
        foreach (['repositoryDeck' => $repository, 'managedDeckList' => [$managed]] as $name => $value) {
            (new ReflectionProperty(OraclePlayer::class, $name))->setValue($player, $value);
        }
        $player->initRepository();
    }

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
