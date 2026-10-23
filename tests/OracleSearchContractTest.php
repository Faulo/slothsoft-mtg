<?php
declare(strict_types = 1);
namespace Slothsoft\MTG;

use DOMDocument;
use DOMException;
use DOMXPath;
use PHPUnit\Framework\TestCase;

final class OracleSearchContractTest extends TestCase {

    /**
     * @dataProvider queryProvider
     * @throws DOMException
     */
    public function testServerSearchKeepsItsQueryAttributesAndCards(array|string $input, array $expected): void {
        $doc = new DOMDocument();
        $ids = $this->getMockBuilder(OracleIdTable::class)->disableOriginalConstructor()->onlyMethods(['searchNameByCard'])->getMock();
        $ids->expects($this->once())->method('searchNameByCard')->with($this->callback(function (array $query) use ($expected): bool {
            foreach ($expected as $key => $value) {
                $this->assertSame($value, $query[$key]);
            }
            return true;
        }))->willReturn(['Forest']);
        $xml = $this->getMockBuilder(OracleXMLTable::class)->disableOriginalConstructor()->onlyMethods(['getXMLListByNameList'])->getMock();
        //language=XML
        $cardXml = '<card name="Forest" rarity="Common"/>';
        $xml->expects($this->once())->method('getXMLListByNameList')->with(['Forest'])->willReturn([$cardXml]);
        $oracle = $this->getMockBuilder(Oracle::class)->setConstructorArgs(['mtg', $doc])->onlyMethods(['getIdTable', 'getXMLTable'])->getMock();
        $oracle->method('getIdTable')->willReturn($ids);
        $oracle->method('getXMLTable')->willReturn($xml);
        $result = $oracle->createSearchElement($doc, $input);
        $doc->appendChild($result);
        $this->assertSame('search', $result->tagName);
        $this->assertSame('1', $result->getAttribute('stock'));
        foreach ($expected as $key => $value) {
            $this->assertSame($value, $result->getAttribute('query-' . $key));
        }
        $this->assertSame('Forest', (new DOMXPath($doc))->evaluate('string(/search/card/@name)'));
    }

    public function queryProvider(): iterable {
        yield 'server array query' => [['name' => 'Forest', 'rarity' => ['Common', 'Uncommon']], ['name' => 'Forest', 'rarity' => 'Common Uncommon']];
        yield 'plain name' => ['Forest', ['name' => 'Forest']];
        yield 'set and number' => ['tst#42', ['name' => '', 'expansion_abbr' => 'tst', 'expansion_number' => '42']];
    }
}
