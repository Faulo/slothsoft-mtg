<?php
declare(strict_types = 1);
namespace Slothsoft\MTG;

use Slothsoft\Farah\HTTPRequest;
use DOMDocument;

class OracleDeck {

    protected $data;

    protected $ownerPlayer;

    protected $modeList;

    protected $typeList = [
        'unmanaged',
        'managed',
        'repository',
        'unused',
        'wishlist'
    ];

    protected $stockList;

    protected $sideboard;

    protected $oracle;

    protected $metaChanged = false;

    protected $stockChanged = false;

    public function __construct(array $data, OraclePlayer $player, Oracle $oracle) {
        $this->data = $data;
        $this->ownerPlayer = $player;
        $this->oracle = $oracle;
        switch ($this->data['type']) {
            // case 'managed':
            case 'unused':
                $this->modeList = [
                    'view',
                    'sort-rarity',
                    'sort-color',
                    'sort-expansion',
                    'sort-legality',
                    'filter',
                    'export-txt',
                    'checklist',
                    'pricelist'
                ];
                break;
            default:
                $this->modeList = [
                    'view',
                    'sort-rarity',
                    'sort-color',
                    'sort-expansion',
                    'sort-legality',
                    'edit',
                    'search',
                    'filter',
                    'import-txt',
                    'export-txt',
                    'remove-txt',
                    'checklist',
                    'pricelist'
                ];
                break;
        }
        $this->stockList = $this->data['stockList'];
        if (! isset($this->data['sideboard'])) {
            $this->data['sideboard'] = [];
        }
        $this->sideboard = $this->data['sideboard'];
    }

    public function parseRequest(HTTPRequest $request, DOMDocument $dataDoc) {
        $retNodes = [];
        if ($query = $request->getInputValue('search-query')) {
            $retNodes[] = $this->oracle->createSearchElement($dataDoc, $query);
        }
        if ($query = $request->getInputValue('filter-query')) {
            $searchNode = $this->oracle->createSearchElement($dataDoc, $query);
            $nodeList = [];
            foreach ($searchNode->childNodes as $node) {
                if ($stock = $this->getStock($node->getAttribute('name'))) {
                    $node->setAttribute('stock', $stock);
                } else {
                    $nodeList[] = $node;
                }
            }
            foreach ($nodeList as $node) {
                $node->parentNode->removeChild($node);
            }
            $retNodes[] = $searchNode;
        }
        if ($cardList = $request->getInputValue('card-add-list') and $stockList = $request->getInputValue('card-stock-list')) {
            if ($request->getInputValue('import-verify-list')) {
                $importData = [];
                foreach ($cardList as $i => $card) {
                    $stock = $stockList[$i];
                    $importData[] = sprintf('%d %s', $stock, $card);
                }
                $importData = implode(PHP_EOL, $importData);
                $request->setInputValue('import-data', $importData);
            }
            if ($request->getInputValue('import-submit-list')) {
                $request->setInputValue('card-add', $cardList);
                $request->setInputValue('card-stock', $stockList);
            }
        }
        if ($cardList = $request->getInputValue('card-remove-list') and $stockList = $request->getInputValue('card-stock-list')) {
            if ($request->getInputValue('import-verify-list')) {
                $importData = [];
                foreach ($cardList as $i => $card) {
                    $stock = $stockList[$i];
                    $importData[] = sprintf('%d %s', $stock, $card);
                }
                $importData = implode(PHP_EOL, $importData);
                $request->setInputValue('import-data', $importData);
            }
            if ($request->getInputValue('import-submit-list')) {
                foreach ($stockList as &$stock) {
                    $stock *= - 1;
                }
                unset($stock);
                $request->setInputValue('card-add', $cardList);
                $request->setInputValue('card-stock', $stockList);
            }
        }
        if ($importData = $request->getInputValue('import-data')) {
            $importList = explode(PHP_EOL, $importData);
            foreach ($importList as $line) {
                $match = [];
                if (preg_match('/^(-?\d+)(.+)/', $line, $match)) {
                    $stock = (int) $match[1];
                    $query = trim($match[2]);
                } else {
                    $stock = 1;
                    $query = trim($line);
                }
                if ($stock and strlen($query)) {
                    $retNodes[] = $this->oracle->createSearchElement($dataDoc, $query, $stock);
                }
            }
        }
        if ($cardList = $request->getInputValue('card-add') and $stockList = $request->getInputValue('card-stock', 1)) {
            if (! is_array($cardList)) {
                $cardList = [
                    $cardList
                ];
            }
            if (! is_array($stockList)) {
                $stockList = [
                    $stockList
                ];
            }
            foreach ($cardList as $i => $card) {
                $stock = $stockList[$i];
                if ($card and $stock) {
                    $val = $this->getStock($card) + $stock;
                    if ($val === 0) {
                        if ($this->removeCard($card)) {
                            $msg = sprintf('Removed card "%s"! \\o/', $card);
                        } else {
                            $msg = sprintf('Could not remove card "%s"! /o\\', $card);
                        }
                    } else {
                        if ($this->addCard($card, $this->getStock($card) + $stock)) {
                            $msg = sprintf('Added %d of card "%s"! \\o/', $stock, $card);
                        } else {
                            $msg = sprintf('Could not add card "%s"! /o\\', $card);
                        }
                    }
                    $parentNode = $dataDoc->createElement('result');
                    $parentNode->setAttribute('message', $msg);
                    $retNodes[] = $parentNode;
                }
            }
        }
        if ($card = $request->getInputValue('card-del')) {
            $this->removeCard($card);
        }
        if ($request->getInputValue('deck-name') and $name = $request->getInputValue('name')) {
            $this->setName($name);
        }
        if ($request->getInputValue('deck-type') and $type = $request->getInputValue('type')) {
            $this->setType($type);
        }
        if ($request->getInputValue('deck-stock') and $stock = $request->getInputValue('stock')) {
            if (isset($stock['deck'])) {
                $this->setStockList($stock['deck']);
            }
            if (isset($stock['sideboard'])) {
                $this->setSideboardList($stock['sideboard']);
            }
        }
        if ($request->getInputValue('deck-upgrade')) {
            $this->upgrade();
        }
        switch ($request->getInputValue('mode')) {
            case 'checklist':
                $retNodes[] = $this->oracle->createChecklistElement($dataDoc, $this->stockList + $this->sideboard);
                break;
            case 'pricelist':
                $retNodes[] = $this->oracle->createPricelistElement($dataDoc, $this->stockList + $this->sideboard);
                /*
                 * $listNode = $this->oracle->createPricelistElement($dataDoc, $this->stockList);
                 * $cardList = [];
                 * $uriList = [];
                 * $priceList = [];
                 * $nodeList = $listNode->getElementsByTagName('card');
                 * foreach ($nodeList as $node) {
                 * $id = $node->getAttribute('id');
                 * $uri = $node->getAttribute('href-price');
                 * $cardList[$id] = $node;
                 * $uriList[$id] = $uri;
                 * }
                 * $threadCount = 4;
                 * $chunkList = array_chunk($uriList, ceil(count($uriList) / $threadCount), true);
                 * $workList = [];
                 * foreach ($chunkList as $chunk) {
                 * $options = [];
                 * $options['mode'] = 'oracle_price';
                 * $options['uriList'] = $chunk;
                 * $workList[] = new \MTG\OracleWork($options);
                 * }
                 * $chunkList = \Lambda\Manager::runWorkList($workList);
                 * foreach ($chunkList as $priceList) {
                 * foreach ($priceList as $id => $price) {
                 * if ($node = $cardList[$id]) {
                 * $price = str_replace(',', '.', $price);
                 * $price = (float) $price;
                 * $price = sprintf('%.2f', $price);
                 * $node->setAttribute('price', $price);
                 * }
                 * }
                 * }
                 * $retNodes[] = $listNode;
                 * //
                 */
                break;
        }
        return $retNodes;
    }

    public function asObject() {
        $ret = [];

        $ret['name'] = $this->getName();
        $ret['key'] = $this->getKey();
        $ret['type'] = $this->getType();
        $ret['stockList'] = $this->stockList;
        $ret['sideboard'] = $this->sideboard;

        return $ret;
    }

    public function asNode(DOMDocument $dataDoc = null) {
        $returnDocument = $dataDoc === null;

        if ($returnDocument) {
            $dataDoc = new DOMDocument();
        }

        $retNode = $dataDoc->createElement('deck');

        $arr = [];
        $arr['name'] = $this->getName();
        $arr['key'] = $this->getKey();
        $arr['type'] = $this->getType();
        $arr['player'] = $this->ownerPlayer->getName();
        $arr['player-firstname'] = $this->ownerPlayer->getFirstname();
        $arr['player-lastname'] = $this->ownerPlayer->getLastname();
        $arr['player-dci'] = $this->ownerPlayer->getDCI();
        foreach ($arr as $key => $val) {
            $retNode->setAttribute($key, $val);
        }

        if ($stockFragment = $this->getStockFragment($dataDoc)) {
            $retNode->appendChild($stockFragment);
        }

        $sideboardNode = $dataDoc->createElement('sideboard');
        if ($sideboardFragment = $this->getSideboardFragment($dataDoc)) {
            $sideboardNode->appendChild($sideboardFragment);
        }
        $retNode->appendChild($sideboardNode);

        $retNode->appendChild($this->oracle->createCategoriesElement($dataDoc));

        foreach ($this->modeList as $mode) {
            $modeNode = $dataDoc->createElement('mode');
            $modeNode->setAttribute('name', $mode);
            $retNode->appendChild($modeNode);
        }

        foreach ($this->typeList as $type) {
            $typeNode = $dataDoc->createElement('type');
            $typeNode->setAttribute('name', $type);
            $retNode->appendChild($typeNode);
        }

        if ($returnDocument) {
            $dataDoc->appendChild($retNode);
            $retNode = $dataDoc;
        }
        return $retNode;
    }

    public function getKey() {
        return $this->ownerPlayer->getDeckKey($this);
    }

    public function getType() {
        return $this->data['type'];
    }

    public function setType($type) {
        if (in_array($type, $this->typeList) and $this->data['type'] !== $type) {
            $this->data['type'] = $type;
            $this->metaChanged = true;
        }
    }

    public function getTitle() {
        $name = $this->getName();
        $stock = array_sum($this->stockList);
        $count = count($this->stockList);
        return sprintf('%s (%d/%d cards)', $name, $count, $stock);
    }

    public function getName() {
        $name = $this->data['name'];
        // $name = str_replace(' ', ' ', $name);
        if (! $name) {
            $name = 'Deck Without A Name ._.';
        }
        return $name;
    }

    public function setName($name) {
        if ($name !== $this->data['name']) {
            $this->data['name'] = $name;
            $this->metaChanged = true;
        }
    }

    public function getStockNameList() {
        return array_keys($this->stockList);
    }

    public function getStockFragment(DOMDocument $dataDoc) {
        $retFragment = null;
        if ($nodeList = $this->oracle->createCardElementList($dataDoc, $this->getStockNameList())) {
            $retFragment = $dataDoc->createDocumentFragment();
            $sortList = [];
            foreach ($nodeList as $name => $node) {
                $sortList[$name] = $node->getAttribute('sort');
            }
            asort($sortList);
            foreach (arra_keys($sortList) as $name) {
                $node = $nodeList[$name];
                $node->setAttribute('stock', $this->getStock($name));
                $retFragment->appendChild($node);
            }
        }
        return $retFragment;
    }

    public function getSideboardNameList() {
        return array_keys($this->sideboard);
    }

    public function getSideboardFragment(DOMDocument $dataDoc) {
        $retFragment = null;
        if ($nodeList = $this->oracle->createCardElementList($dataDoc, $this->getSideboardNameList())) {
            $retFragment = $dataDoc->createDocumentFragment();
            $sortList = [];
            foreach ($nodeList as $name => $node) {
                $sortList[$name] = $node->getAttribute('sort');
            }
            asort($sortList);
            foreach (arra_keys($sortList) as $name) {
                $node = $nodeList[$name];
                $node->setAttribute('stock', $this->getSideboard($name));
                $retFragment->appendChild($node);
            }
        }
        return $retFragment;
    }

    public function setStockList(array $stockList) {
        foreach ($stockList as $name => $stock) {
            $this->setStock($name, $stock);
        }
    }

    public function setStock($name, $count) {
        $count = (int) $count;
        if (! isset($this->stockList[$name]) or $this->stockList[$name] !== $count) {
            $this->stockList[$name] = $count;
            $this->stockChanged = true;
        }
    }

    public function getStock($name) {
        return isset($this->stockList[$name]) ? $this->stockList[$name] : 0;
    }

    public function setSideboardList(array $stockList) {
        foreach ($stockList as $name => $stock) {
            $this->setSideboard($name, $stock);
        }
    }

    public function setSideboard($name, $count) {
        $count = (int) $count;
        if (! isset($this->sideboard[$name]) or $this->sideboard[$name] !== $count) {
            $this->sideboard[$name] = $count;
            $this->stockChanged = true;
        }
    }

    public function getSideboard($name) {
        return isset($this->sideboard[$name]) ? $this->sideboard[$name] : 0;
    }

    public function removeSideboard($name) {
        $ret = false;
        if (isset($this->sideboard[$name])) {
            unset($this->sideboard[$name]);
            $this->stockChanged = true;
            $ret = true;
        }
        return $ret;
    }

    public function hasCard($name) {
        return isset($this->stockList[$name]);
    }

    public function addCard($name, $stock = 1) {
        $ret = false;
        if ($cardNode = $this->oracle->createCardElement(null, $name)) {
            $name = $cardNode->getAttribute('name');
            // my_dump([$this->getName(), $name]);die();
            // $this->removeCard($name);
            $this->setStock($name, $stock);
            $ret = true;
        } else {
            // $this->removeCard($name);
            // my_dump($name);
        }
        return $ret;
    }

    public function removeCard($name) {
        $ret = false;
        if (isset($this->stockList[$name])) {
            unset($this->stockList[$name]);
            $this->stockChanged = true;
            $ret = true;
        }
        return $ret;
    }

    public function hasMetaChanged() {
        return $this->metaChanged;
    }

    public function hasStockChanged() {
        return $this->stockChanged;
    }

    public function save() {
        $this->ownerPlayer->save();
    }

    public function upgrade() {}
}