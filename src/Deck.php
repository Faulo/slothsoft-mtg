<?php
declare(strict_types = 1);
namespace Slothsoft\MTG;

use Slothsoft\Farah\HTTPRequest;
use DOMXPath;
use DOMDocument;
use DOMElement;

class Deck {

    public $ownerPlayer;

    public $node;

    public $superTypes = [
        'Land',
        'Planeswalker',
        // 'Legendary',
        'Enchantment Creature',
        'Creature',
        'Enchantment',
        'Sorcery',
        'Instant',
        'Artifact',
        'Token',
        'Other'
    ];

    public $colorTypes = [
        'White',
        'Blue',
        'Black',
        'Red',
        'Green'
    ];

    public $modeList;

    public $stockList = [];

    protected $oracle;

    public function __construct(Player $player, DOMElement $deckNode, Oracle $oracle) {
        $this->ownerPlayer = $player;
        $this->node = $deckNode;
        $this->oracle = $oracle;
        switch ($this->node->getAttribute('type')) {
            // case 'managed':
            case 'unused':
                $this->modeList = [
                    'view',
                    'sort-rarity',
                    'sort-expansion',
                    'sort-color',
                    'export-txt',
                    'checklist'
                ];
                break;
            default:
                $this->modeList = [
                    'view',
                    'sort-rarity',
                    'sort-expansion',
                    'sort-color',
                    'edit',
                    'import-txt',
                    'export-txt',
                    'checklist'
                ];
                break;
        }
        if ($stock = $this->node->getAttribute('stock') and $stock = json_decode($stock, true)) {
            $this->stockList = $stock;
            $cardList = $this->getCardList();
            $delList = [];
            foreach ($this->stockList as $name => $stock) {
                if (! isset($cardList[$name])) {
                    $delList[] = $name;
                }
            }
            foreach ($delList as $name) {
                unset($this->stockList[$name]);
            }
        }
    }

    public function parseRequest(HTTPRequest $request, DOMDocument $dataDoc) {
        $retNodes = [];
        if ($query = $request->getInputValue('search-query')) {
            $retNodes[] = $this->oracle->createSearchElement($dataDoc, $query);
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
        if ($importData = $request->getInputValue('import-data')) {
            $importList = explode(PHP_EOL, $importData);
            foreach ($importList as $line) {
                $match = [];
                if (preg_match('/(\d+)(.+)/', $line, $match)) {
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
                    if ($this->addCard($card, $this->getStock($card) + $stock)) {
                        $msg = sprintf('Added %d of card "%s"! :D', $stock, $card);
                    } else {
                        $msg = sprintf('Could not add card "%s"! D:', $card);
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
        if ($request->getInputValue('deck-stock') and $stock = $request->getInputValue('stock')) {
            $this->setStockList($stock);
        }
        if ($request->getInputValue('deck-upgrade')) {
            $this->upgrade();
        }
        switch ($request->getInputValue('mode')) {
            case 'checklist':
                $retNodes[] = $this->oracle->createChecklistElement($dataDoc, $this->stockList);
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

        return $ret;
    }

    public function asNode(DOMDocument $dataDoc = null, $loadDeck = true) {
        if ($dataDoc === null) {
            $dataDoc = $this->ownerPlayer->doc;
            $xpath = $this->ownerPlayer->xpath;
            $retNode = $this->node->cloneNode($loadDeck);
        } else {
            $xpath = new DOMXPath($dataDoc);
            $retNode = $dataDoc->importNode($this->node, $loadDeck);
        }
        $retNode->setAttribute('player', $this->ownerPlayer->name);
        $retNode->setAttribute('key', $this->getKey());

        if (! $retNode->getAttribute('name')) {
            $retNode->setAttribute('name', $this->getName());
        }
        $cardList = $xpath->evaluate('card', $retNode);
        foreach ($cardList as $cardNode) {
            $name = $cardNode->getAttribute('name');
            $cardNode->setAttribute('stock', isset($this->stockList[$name]) ? $this->stockList[$name] : 1);
        }
        foreach ($this->superTypes as $type) {
            $typeNode = $dataDoc->createElement('type');
            $typeNode->setAttribute('name', $type);
            $nodeList = [];
            $tmpList = $xpath->evaluate(sprintf('card[contains(@type-sup, "%s")]', $type), $retNode);
            foreach ($tmpList as $node) {
                $nodeList[] = $node;
            }
            foreach ($nodeList as $node) {
                $typeNode->appendChild($node);
            }
            $retNode->appendChild($typeNode);
            $lastNode = $typeNode;
        }
        foreach ($this->oracle->rarityTypes as $key => $rarity) {
            $rarityNode = $dataDoc->createElement('rarity');
            $rarityNode->setAttribute('name', $rarity);
            $rarityNode->setAttribute('key', $key);
            $retNode->appendChild($rarityNode);
        }
        foreach ($this->colorTypes as $color) {
            $colorNode = $dataDoc->createElement('color');
            $colorNode->setAttribute('name', $color);
            $retNode->appendChild($colorNode);
        }
        $expansionList = [];
        $nodeList = $xpath->evaluate('.//card/@set', $retNode);
        foreach ($nodeList as $node) {
            $set = $xpath->evaluate('string(.)', $node);
            $expansionList[$set] = $set;
        }
        sort($expansionList);
        foreach ($expansionList as $expansion) {
            $expansionNode = $dataDoc->createElement('expansion');
            $expansionNode->setAttribute('name', $expansion);
            $retNode->appendChild($expansionNode);
        }
        foreach ($this->modeList as $mode) {
            $modeNode = $dataDoc->createElement('mode');
            $modeNode->setAttribute('name', $mode);
            $retNode->appendChild($modeNode);
        }

        $nodeList = [];
        $tmpList = $xpath->evaluate('card', $retNode);
        foreach ($tmpList as $node) {
            $nodeList[] = $node;
        }
        foreach ($nodeList as $node) {
            $lastNode->appendChild($node);
        }
        return $retNode;
    }

    public function getKey() {
        return md5(md5($this->ownerPlayer->name) . md5($this->getName()));
    }

    public function getType() {
        return $this->node->getAttribute('type');
    }

    public function getTitle() {
        $name = $this->getName();
        $deckNode = $this->asNode();
        $stock = $this->ownerPlayer->xpath->evaluate('sum(.//card/@stock)', $deckNode);
        $count = $this->ownerPlayer->xpath->evaluate('count(.//card[@stock > 0])', $deckNode);
        return sprintf('%s (%d/%d cards)', $name, $count, $stock);
    }

    public function getName() {
        $name = $this->node->getAttribute('name');
        // $name = str_replace(' ', ' ', $name);
        if (! $name) {
            $name = 'Deck Without A Name ._.';
        }
        return $name;
    }

    public function setName($name) {
        if ($name !== $this->node->getAttribute('name')) {
            $this->node->setAttribute('name', $name);
            $this->save();
        }
    }

    public function getCardList() {
        $ret = [];
        $xpath = $this->ownerPlayer->xpath;
        $nodeList = $xpath->evaluate('card', $this->node);
        foreach ($nodeList as $node) {
            $ret[$node->getAttribute('name')] = $node;
        }
        return $ret;
    }

    public function setStockList(array $stockList) {
        foreach ($stockList as $name => $stock) {
            $this->setStock($name, $stock);
        }
        $this->save();
    }

    public function setStock($name, $count) {
        $this->stockList[$name] = (int) $count;
        $this->node->setAttribute('stock', json_encode($this->stockList));
    }

    public function getStock($name) {
        return isset($this->stockList[$name]) ? $this->stockList[$name] : 0;
    }

    public function hasCard($name) {
        $xpath = $this->ownerPlayer->xpath;
        return $xpath->evaluate(sprintf('boolean(card[@name = "%s"])', $name), $this->node);
    }

    public function addCard($name, $stock = 1) {
        $ret = false;
        if ($card = $this->oracle->searchCardByName($name)) {
            // my_dump([$this->getName(), $name]);die();
            $this->removeCard($name);
            $this->node->appendChild($this->oracle->createCardElement($card, $this->ownerPlayer->doc));
            $this->setStock($name, $stock);
            $this->save();
            $ret = true;
        } else {
            // $this->removeCard($name);
            // my_dump($name);
        }
        return $ret;
    }

    public function removeCard($name) {
        $xpath = $this->ownerPlayer->xpath;
        $nodeList = [];
        $tmpList = $xpath->evaluate(sprintf('card[@name = "%s"]', $name), $this->node);
        foreach ($tmpList as $node) {
            $nodeList[] = $node;
        }
        if ($nodeList) {
            foreach ($nodeList as $node) {
                $node->parentNode->removeChild($node);
            }
            $this->save();
        }
    }

    public function save() {
        $this->ownerPlayer->save();
    }

    public function upgrade() {
        $cardList = $this->getCardList();
        foreach (array_keys($cardList) as $name) {
            $this->addCard($name, $this->getStock($name));
        }
    }
}