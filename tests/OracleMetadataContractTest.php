<?php
declare(strict_types = 1);
namespace Slothsoft\MTG;

use DOMDocument;
use DOMException;
use Exception;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

/**
 * @runTestsInSeparateProcesses
 */
final class OracleMetadataContractTest extends TestCase {

    /**
     * @throws DOMException
     * @throws Exception
     */
    public function testSetMetadataKeepsAllAttributesAndEscapesText(): void {
        (new ReflectionProperty(OracleInfo::class, '_setList'))->setValue(null, [[
            'name' => 'Duel & Decks',
            'href' => 'https://fixtures.example.test/?a=1&b=2',
            'date' => '2020-01-02',
            'type' => 'expansion set'
        ]]);
        $doc = new DOMDocument();
        $root = $doc->createElement('sets');
        $doc->appendChild($root);
        $root->appendChild(OracleInfo::getSetFragment($doc));
        $set = $doc->getElementsByTagName('set')->item(0);
        $this->assertSame('Duel & Decks', $set->textContent);
        $this->assertSame('https://fixtures.example.test/?a=1&b=2', $set->getAttribute('href'));
        $this->assertSame('2020-01-02', $set->getAttribute('date'));
        $this->assertSame('2020', $set->getAttribute('date-year'));
        $this->assertSame('01', $set->getAttribute('date-month'));
        $this->assertSame('02', $set->getAttribute('date-day'));
        $this->assertSame('expansion set', $set->getAttribute('type'));
        $this->assertStringContainsString('Duel &amp; Decks', $doc->saveXML());
    }

    public function testColorMappingsKeepBothDirectionsAndUnknownValues(): void {
        foreach (['C' => 'Colorless', 'W' => 'White', 'U' => 'Blue', 'B' => 'Black', 'R' => 'Red', 'G' => 'Green'] as $key => $name) {
            $this->assertSame($name, OracleInfo::getColorName($key));
            $this->assertSame($key, OracleInfo::getColorKey($name));
        }
        $this->assertNull(OracleInfo::getColorName('unknown'));
        $this->assertNull(OracleInfo::getColorKey('unknown'));
    }
}
