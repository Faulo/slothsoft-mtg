<?php
declare(strict_types = 1);
namespace Slothsoft\MTG;

use DOMDocument;
use DOMDocumentFragment;
use DOMElement;
use DOMException;
use DOMNode;
use Exception;
use Serializable;
use Slothsoft\Core\Calendar\Seconds;
use Slothsoft\Core\DOMHelper;
use Slothsoft\Core\Storage;

class Oracle implements Serializable {
    
    private ?DOMDocument $doc;
    
    private string $dbName;
    
    private string $idTableName = 'oracle-ids';
    
    private string $xmlTableName = 'oracle-xml';
    
    private string $customTableName = 'oracle-custom';
    
    private ?OracleIdTable $idTable;
    
    private ?OracleXMLTable $xmlTable;
    
    private ?OracleCustomTable $customTable;
    
    private string $_oracleSetURL = 'http://gatherer.wizards.com/Pages/Advanced.aspx';
    
    private string $_oracleSetQuery = '//*[@id="autoCompleteSourceBoxsetAddText0_InnerTextBoxcontainer"]/*';
    
    private ?array $_oracleSetList = null;
    
    private array $_oracleSetBlacklist = [
        'Promo set for Gatherer'
    ];
    
    public function __construct(string $dbName, ?DOMDocument $doc = null) {
        $this->initialize($dbName, $doc);
    }

    private function initialize(string $dbName, ?DOMDocument $doc = null): void {
        $this->dbName = $dbName;
        
        $this->doc = $doc;
        
        $this->idTable = null;
        $this->xmlTable = null;
        $this->customTable = null;
        
        // $this->initTables();
        // my_dump(array_diff(OracleInfo::getSetList(), $this->getOracleSetList()));
    }
    
    public function initTables(): void {
        $table = $this->getIdTable();
        $table->init();
        $table = $this->getXMLTable();
        $table->init();
        $table = $this->getCustomTable();
        $table->init();
    }
    
    public function getIdTable(): ?OracleIdTable {
        if (! $this->idTable) {
            $this->idTable = new OracleIdTable($this->dbName, $this->idTableName);
        }
        return $this->idTable;
    }
    
    public function getXMLTable(): ?OracleXMLTable {
        if (! $this->xmlTable) {
            $this->xmlTable = new OracleXMLTable($this->dbName, $this->xmlTableName);
        }
        return $this->xmlTable;
    }
    
    public function getCustomTable(): ?OracleCustomTable {
        if (! $this->customTable) {
            $this->customTable = new OracleCustomTable($this->dbName, $this->customTableName);
        }
        return $this->customTable;
    }
    
    public function getSetList(): array {
        $ret = [];
        if ($idTable = $this->getIdTable()) {
            $nameList = $idTable->getExpansionList();
            foreach ($nameList as $name) {
                $arr['name'] = $name;
                $arr['count'] = count($idTable->getNameListBySetName($name));
                $ret[$name] = $arr;
            }
        }
        return $ret;
    }
    
    /**
     * @throws Exception
     */
    public function getOracleSetList($downloadFreshCopy = false): ?array {
        if ($this->_oracleSetList === null) {
            if ($xpath = Storage::loadExternalXPath($this->_oracleSetURL, $downloadFreshCopy ? 0 : Seconds::YEAR)) {
                $this->_oracleSetList = [];
                $nodeList = $xpath->evaluate($this->_oracleSetQuery);
                foreach ($nodeList as $node) {
                    $set = $xpath->evaluate('normalize-space(.)', $node);
                    if (strlen($set) and ! in_array($set, $this->_oracleSetBlacklist)) {
                        $this->_oracleSetList[] = $set;
                    }
                }
            }
        }
        return $this->_oracleSetList;
    }
    
    /**
     * @throws DOMException
     */
    public function createSearchElement(DOMDocument $dataDoc, $inputQuery, $stock = 1): false|DOMElement {
        if (is_array($inputQuery)) {
            $query = $inputQuery;
        } else {
            $match = null;
            if (preg_match('/(.+)#(.+)/', $inputQuery, $match)) {
                $query = [
                    'expansion_abbr' => $match[1],
                    'expansion_number' => $match[2]
                ];
            } else {
                $query = [
                    'name' => $inputQuery
                ];
            }
        }
        $query = array_merge([
            'name' => '',
            'type' => '',
            'rarity' => '',
            'expansion_name' => '',
            'description' => '',
            'cost' => '',
            'legality' => '',
            'cmc' => '',
            'expansion_abbr' => '',
            'expansion_number' => ''
        ], $query);
        foreach ($query as &$val) {
            if (is_array($val)) {
                $val = implode(' ', $val);
            }
        }
        unset($val);
        // my_dump([$stock, $query]);
        $retNode = $dataDoc->createElement('search');
        $retNode->setAttribute('query', (strlen($query['name']) or is_array($inputQuery)) ? (string) $query['name'] : (string) $inputQuery);
        foreach ($query as $key => $val) {
            $retNode->setAttribute('query-' . $key, (string) $val);
        }
        $retNode->setAttribute('stock', (string) $stock);
        // my_dump($query);
        /*
         * $cardList = implode('', $query) === $query['name']
         * ? $this->getXMLTable()->searchNameByName($query['name'])
         * : $this->getIdTable()->searchNameByCard($query);
         * //
         */
        $cardList = $this->getIdTable()->searchNameByCard($query);
        // my_dump($cardList);
        if ($searchFragment = $this->createCardFragment($dataDoc, $cardList)) {
            $retNode->appendChild($searchFragment);
        }
        return $retNode;
    }
    
    /**
     * @throws DOMException
     */
    public function createSetElement(DOMDocument $dataDoc, $setName, array $stockList = []): false|DOMElement {
        $retNode = $dataDoc->createElement('set');
        $retNode->setAttribute('name', $setName);
        
        $idTable = $this->getIdTable();
        $nameList = $idTable->getNameListBySetName($setName);
        if ($fragment = $this->createCardFragment($dataDoc, $nameList, $stockList)) {
            $retNode->appendChild($fragment);
        }
        /*
         * $cardList = $idTable->getCardListBySetName($setName);
         * if ($fragment = $this->createCardListFragment($dataDoc, $cardList, $stockList)) {
         * $retNode->appendChild($fragment);
         * }
         * //
         */
        return $retNode;
    }
    
    /**
     * @throws DOMException
     * @throws Exception
     */
    public function createCategoriesElement(?DOMDocument $dataDoc = null): false|DOMElement {
        if (! $dataDoc) {
            $dataDoc = $this->doc;
        }
        $categoryNode = $dataDoc->createElement('categories');
        $categoryNode->appendChild(OracleInfo::getTypeFragment($dataDoc));
        $categoryNode->appendChild(OracleInfo::getRarityFragment($dataDoc));
        $categoryNode->appendChild(OracleInfo::getColorFragment($dataDoc));
        $categoryNode->appendChild(OracleInfo::getSetFragment($dataDoc));
        $categoryNode->appendChild(OracleInfo::getLegalityFragment($dataDoc));
        return $categoryNode;
    }
    
    public function createCardFragment(?DOMDocument $dataDoc = null, array $nameList = [], ?array $stockList = null): ?DOMDocumentFragment {
        if (! $dataDoc) {
            $dataDoc = $this->doc;
        }
        foreach ($nameList as &$name) {
            $name = $this->_sanitizeName($name);
        }
        unset($name);
        if (! $dataDoc) {
            $dataDoc = $this->doc;
        }
        $xmlTable = $this->getXMLTable();
        $xml = $xmlTable->getXMLListByNameList($nameList);
        $xml = implode('', $xml);
        $fragment = $this->dom()->parse($xml, $dataDoc);
        if (is_array($stockList)) {
            foreach ($fragment->childNodes as $cardNode) {
                if ($cardNode->nodeType === XML_ELEMENT_NODE) {
                    $name = $cardNode->getAttribute('name');
                    $stock = $stockList[$name] ?? 0;
                    $cardNode->setAttribute('stock', (string) $stock);
                    if ($stock > 0) {
                        $cardNode->setAttribute('owned', '');
                    }
                }
            }
        }
        return $fragment->hasChildNodes() ? $fragment : null;
    }
    
    public function createCardElement(?DOMDocument $dataDoc = null, $name = ''): DOMNode|false|null {
        if (! $dataDoc) {
            $dataDoc = $this->doc;
        }
        $name = $this->_sanitizeName($name);
        $ret = null;
        $xmlTable = $this->getXMLTable();
        if ($xml = $xmlTable->getXMLListByNameList([
            $name
        ])) {
            $xml = implode('', $xml);
            if ($fragment = $this->dom()->parse($xml, $dataDoc)) {
                $ret = $fragment->removeChild($fragment->firstChild);
            }
        }
        return $ret;
    }
    
    public function createCardElementList(?DOMDocument $dataDoc = null, array $nameList = []): array {
        foreach ($nameList as &$name) {
            $name = $this->_sanitizeName($name);
        }
        unset($name);
        if (! $dataDoc) {
            $dataDoc = $this->doc;
        }
        $ret = [];
        $xmlTable = $this->getXMLTable();
        $xml = $xmlTable->getXMLListByNameList($nameList);
        $xml = implode('', $xml);
        $fragment = $this->dom()->parse($xml, $dataDoc);
        while ($fragment->hasChildNodes()) {
            $node = $fragment->removeChild($fragment->firstChild);
            $ret[$node->getAttribute('name')] = $node;
        }
        return $ret;
    }
    
    /**
     * @throws DOMException
     * @throws Exception
     */
    public function createChecklistElement(DOMDocument $dataDoc, array $stockList): false|DOMElement {
        $checklistNode = $dataDoc->createElement('checklist');
        $setNameList = OracleInfo::getVintageLegalList();
        foreach ($setNameList as $setName) {
            if ($setNode = $this->createSetElement($dataDoc, $setName, $stockList)) {
                $checklistNode->appendChild($setNode);
            }
        }
        return $checklistNode;
    }
    
    /**
     * @throws DOMException
     */
    public function createPricelistElement(DOMDocument $dataDoc, array $stockList): false|DOMElement {
        $retNode = $dataDoc->createElement('pricelist');
        
        // $idTable = $this->getIdTable();
        $setNode = $dataDoc->createElement('set');
        $setNode->setAttribute('name', 'Magiccardmarket');
        $setNode->setAttribute('href', 'https://www.magiccardmarket.eu/');
        
        $nameList = array_keys($stockList);
        if ($fragment = $this->createCardFragment($dataDoc, $nameList, $stockList)) {
            $setNode->appendChild($fragment);
        }
        /*
         * $cardList = [];
         * $tmpList = $idTable->getCardListByNameList($nameList);
         * foreach ($tmpList as $card) {
         * $cardList[$card['name']] = $card;
         * }
         * if ($fragment = $this->createCardListFragment($dataDoc, $cardList, $stockList)) {
         * $setNode->appendChild($fragment);
         * }
         * //
         */
        $retNode->appendChild($setNode);
        return $retNode;
    }
    
    protected function _sanitizeName(string $name): string {
        // $name = str_replace(' & ', ' // ', $name);
        return $name;
    }
    
    private function dom(): DOMHelper {
        return new DOMHelper();
    }
    
    public function __serialize(): array {
        return ['db' => $this->dbName];
    }

    public function __unserialize(array $data): void {
        $this->initialize($data['db']);
    }

    public function serialize(): string {
        return $this->dbName;
    }
    
    public function unserialize(string $data): void {
        $this->initialize($data);
    }
}
