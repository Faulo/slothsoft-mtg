<?php
declare(strict_types = 1);
namespace Slothsoft\MTG;

use Exception;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use ReflectionProperty;
use XSLTProcessor;

/**
 * @runTestsInSeparateProcesses
 */
final class NativeTypeRegressionTest extends TestCase {

    /**
     * @throws Exception
     */
    public function testXmlPlayerWithoutSpecialDecksKeepsNullableLookups(): void {
        $file = temp_file(self::class);
        file_put_contents($file, '<player name="Test"/>');
        $player = new Player($file, new Oracle('test'));
        $this->assertSame('Test', $player->name);
        $this->assertSame([], $player->deckList);
        $this->assertNull($player->repositoryDeck);
        $this->assertNull($player->getDeck('missing'));
        $this->assertNull($player->getDeckByKey('missing'));
    }

    /**
     * @throws Exception
     */
    public function testJsonPlayerAndDeckKeepEmptyStockAndNullableLookups(): void {
        $file = temp_file(self::class);
        file_put_contents($file, json_encode(['deckList' => []]));
        $oracle = new Oracle('test');
        $player = new OraclePlayer($file, $oracle);
        $this->assertNull($player->getDeck('missing'));
        $this->assertNull($player->getDeckByKey('missing'));
        $deck = new OracleDeck(['name' => 'Test', 'type' => 'managed', 'stockList' => []], $player, $oracle);
        $this->assertSame(0, $deck->getStock('Forest'));
        $this->assertSame(0, $deck->getSideboard('Forest'));
        $this->assertFalse($deck->hasMetaChanged());
        $this->assertFalse($deck->hasStockChanged());
    }

    /**
     * @throws Exception
     */
    public function testReviewWithoutAFileStillStartsEmpty(): void {
        $review = new OracleReview(temp_file(self::class), new Oracle('test'));
        $this->assertSame([], $review->getSetList());
    }

    /**
     * @throws Exception
     */
    public function testImageFactoriesStillReturnNullForInvalidDirectories(): void {
        $oracle = new Oracle('test');
        $missing = temp_file(self::class);
        $this->assertNull($oracle->getCardImage($missing, '1', 'tst', '1'));
        $this->assertNull($oracle->getRarityImage($missing, 'tst', 'Common'));
        $this->assertNull($oracle->getSetImage($missing, 'tst'));
        $this->assertNull($oracle->getColorImage($missing, 'W'));
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     */
    public function testCardDataWithoutAnOracleUrlStillReturnsNull(): void {
        (new ReflectionProperty(OracleInfo::class, '_oracleXSLT'))->setValue(null, new XSLTProcessor());
        $this->assertNull(OracleInfo::getOracleCardData(['oracle_id' => 0]));
    }

    public function testReadOnlyClassificationAcceptsArrayExpressions(): void {
        $this->assertSame('Land', OracleInfo::getCardTypeName(['type' => 'Basic Land']));
        $this->assertSame('Common', OracleInfo::getCardRarityName(['rarity' => 'Common']));
    }
}
