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
