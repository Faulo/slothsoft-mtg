<?php
declare(strict_types = 1);
namespace Slothsoft\MTG;

use Slothsoft\Farah\HTTPRequest;
use Slothsoft\Core\Storage;
use DOMDocument;
use Exception;

class OraclePlayer {

    protected $key;

    protected $name;

    protected $data;

    protected $file;

    protected $deckList;

    protected $repositoryDeck;

    protected $unusedDeck;

    protected $wishlistDeck;

    protected $managedDeckList;

    protected $oracle;

    protected $deckMetaChanged = false;

    protected $deckStockChanged = false;

    public function __construct($playerFile, Oracle $oracle) {
        $this->file = $playerFile;
        $this->oracle = $oracle;
        $this->data = json_decode(file_get_contents($this->file), true);
        if (! $this->data) {
            throw new Exception(sprintf('Empty player file?!%s%s%s%s', PHP_EOL, $this->file, PHP_EOL, file_get_contents($this->file)));
        }
        $this->key = basename($this->file);
        $this->key = substr($this->key, 0, strpos($this->key, '.'));
        $this->name = $this->key;
        /*
         * if (isset($this->data['name'])) {
         * $this->name = $this->data['name'];
         * } else {
         * $this->name = $this->key;
         * }
         * //
         */
        $this->deckList = [];
        $this->managedDeckList = [];
        $i = 0;
        foreach ($this->data['deckList'] as &$deckData) {
            $deck = new OracleDeck($deckData, $this, $this->oracle);
            $deckNo = null;
            if ($deck->getType() === 'repository') {
                if ($this->repositoryDeck) {
                    $deck->setType('unmanaged');
                } else {
                    $this->repositoryDeck = $deck;
                    $deckNo = 'repository';
                }
            }
            if ($deck->getType() === 'unused') {
                if ($this->unusedDeck) {
                    $deck->setType('unmanaged');
                } else {
                    $this->unusedDeck = $deck;
                    $deckNo = 'unused';
                }
            }
            if ($deck->getType() === 'wishlist') {
                if ($this->wishlistDeck) {
                    $deck->setType('unmanaged');
                } else {
                    $this->wishlistDeck = $deck;
                    $deckNo = 'wishlist';
                }
            }
            if ($deck->getType() === 'managed') {
                $this->managedDeckList[] = $deck;
                $deckNo = $i + 1;
                $i ++;
            }
            if ($deck->getType() === 'unmanaged') {
                $deckNo = $i + 1;
                $i ++;
            }
            if ($deckNo) {
                $this->deckList[$deckNo] = $deck;
            }
        }
        unset($deckData);

        $this->initRepository();
        $this->initUnused();
    }

    public function initRepository() {
        if ($this->repositoryDeck) {
            $repositoryStockList = [];
            $cardList = $this->repositoryDeck->getStockNameList();
            foreach ($cardList as $name) {
                $repositoryStockList[$name] = $this->repositoryDeck->getStock($name);
            }
            $managedStockList = [];
            foreach ($this->managedDeckList as $deck) {
                $cardList = $deck->getStockNameList();
                foreach ($cardList as $name => $card) {
                    if (! isset($managedStockList[$name])) {
                        $managedStockList[$name] = 0;
                    }
                    $managedStockList[$name] += $deck->getStock($name);
                }
                $cardList = $deck->getSideboardNameList();
                foreach ($cardList as $name => $card) {
                    if (! isset($managedStockList[$name])) {
                        $managedStockList[$name] = 0;
                    }
                    $managedStockList[$name] += $deck->getSideboard($name);
                }
            }
            foreach ($managedStockList as $name => $managedStock) {
                $add = true;
                if (isset($repositoryStockList[$name])) {
                    if ($repositoryStockList[$name] >= $managedStock) {
                        $add = false;
                    }
                }
                if ($add) {
                    // my_dump($this->file);
                    // my_dump($name);
                    // $this->repositoryDeck->addCard($name, $managedStock);
                }
            }
        }
    }

    public function initUnused() {
        if ($this->repositoryDeck and $this->unusedDeck) {
            $repositoryStockList = [];
            $cardList = $this->unusedDeck->getStockNameList();
            foreach ($cardList as $name) {
                $repositoryStockList[$name] = 0;
            }
            $cardList = $this->repositoryDeck->getStockNameList();
            // my_dump(count($cardList));
            foreach ($cardList as $name) {
                $repositoryStockList[$name] = $this->repositoryDeck->getStock($name);
            }
            foreach ($this->managedDeckList as $deck) {
                $cardList = $deck->getStockNameList();
                // echo $deck->getStock('Swamp') . PHP_EOL;
                foreach ($cardList as $name) {
                    if (! isset($repositoryStockList[$name])) {
                        $repositoryStockList[$name] = 0;
                    }
                    $repositoryStockList[$name] -= $deck->getStock($name);
                }
            }
            foreach ($repositoryStockList as $name => $managedStock) {
                if ($managedStock) {
                    $this->unusedDeck->setStock($name, $managedStock);
                } else {
                    $this->unusedDeck->removeCard($name);
                }
            }
        }
    }

    public function createDeck($deckNo) {
        // {"name":"Deck #4","key":"c68c9d99f3131a1f25ef4dfe830456e7","type":"managed","stockList":[]}
        $deckData = [];
        $deckData['name'] = 'New Deck';
        $deckData['key'] = '';
        $deckData['type'] = 'managed';
        $deckData['stockList'] = [];
        $deck = new OracleDeck($deckData, $this, $this->oracle);
        $this->managedDeckList[] = $deck;
        // $deckNo = count($this->managedDeckList);
        $this->deckList[$deckNo] = $deck;
        $this->deckMetaChanged = true;
        return $deckNo;
    }

    public function removeDeck($deckNo) {
        if (isset($this->deckList[$deckNo])) {
            $this->deckMetaChanged = true;
            unset($this->deckList[$deckNo]);
        }
        return $deckNo;
    }

    public function moveDeck($deckNo, $offset) {
        $retNo = $deckNo;
        if (isset($this->deckList[$deckNo])) {
            $this->deckMetaChanged = true;

            $deck = $this->deckList[$deckNo];
            $oldPos = 0;
            foreach ($this->deckList as $tmpNo => $tmpDeck) {
                if ($tmpDeck === $deck) {
                    break;
                }
                $oldPos ++;
            }
            $newPos = $oldPos + $offset;
            $newPos = max(0, min($newPos, count($this->deckList)));
            $newList = [];
            $pos = 0;
            foreach ($this->deckList as $tmpNo => $tmpDeck) {
                if ($pos === $newPos) {
                    $newList[$deckNo] = $deck;
                    if (is_numeric($deckNo) and is_numeric($tmpNo)) {
                        $retNo = $newPos - $oldPos + (int) $deckNo;
                    }
                }
                if ($tmpDeck !== $deck) {
                    $newList[$tmpNo] = $tmpDeck;
                    $pos ++;
                }
            }
            // my_dump(array_keys($newList));
            if (! isset($newList[$deckNo])) {
                $newList[$deckNo] = $deck;
            }
            $this->deckList = $newList;
            if (is_numeric($deckNo)) {
                // my_dump($deckNo);
                // $deckNo = $newPos - $oldPos + (int) $deckNo;
                // my_dump($deckNo);
            }
        }
        return $retNo;
    }

    public function getDeck($deckNo) {
        return isset($this->deckList[$deckNo]) ? $this->deckList[$deckNo] : null;
    }

    public function getDeckByKey($deckKey) {
        foreach ($this->deckList as $deck) {
            if ($deck->getKey() === $deckKey) {
                return $deck;
            }
        }
        return null;
    }

    public function save() {
        if (! $this->deckMetaChanged) {
            foreach ($this->deckList as $deck) {
                if ($deck->hasMetaChanged()) {
                    $this->deckMetaChanged = true;
                }
                if ($deck->hasStockChanged()) {
                    $this->deckStockChanged = true;
                }
            }
        }
        if ($this->deckMetaChanged or $this->deckStockChanged) {
            if (headers_sent()) {
                throw new Exception('will not save "' . $this->file . '", an error occured?! oAO');
            }

            $data = $this->asObject();
            $data = json_encode($data);
            file_put_contents($this->file, $data);

            if ($this->deckMetaChanged) {
                Storage::loadExternalDocument('http://slothsoft.net/getData.php/mtg/cron.0.sites');
            }
        }
    }

    public function parseRequest(HTTPRequest $request, DOMDocument $dataDoc) {
        $retNodes = [];
        // unused ???
        if ($request->getInputValue('deck-add') and $data = $request->getInputJSON()) {
            $cardName = $data['cardName'];
            $deck = $this->getDeck($data['deckNo']);
            $stock = (int) $data['stock'];
            if ($cardName and $deck) {
                if ($deck->hasCard($cardName)) {
                    $deck->setStock($cardName, $deck->getStock($cardName) + $stock);
                } else {
                    $deck->addCard($cardName);
                }
                if (! $deck->getStock($cardName)) {
                    $deck->removeCard($cardName);
                }
                // $this->save();
            }
        }
        // manager !!!
        if ($request->getInputValue('deck-switch') and $dataList = $request->getInputJSON()) {
            foreach ($dataList as $data) {
                $cardName = $data['cardName'];
                $isSideboard = false;
                $deck = $this->getDeck($data['deckNo']);
                if (! $deck and $data['deckNo'][0] === '-') {
                    $deck = $this->getDeck(substr($data['deckNo'], 1));
                    $isSideboard = true;
                }
                if (! $deck) {
                    throw new Exception('DECK NOT FOUND: ' . $data['deckNo']);
                }
                $stock = (int) $data['stock'];
                if ($cardName and $deck) {
                    /*
                     * if ($deck->hasCard($cardName)) {
                     * $deck->setStock($cardName, $deck->getStock($cardName) + $stock);
                     * } else {
                     * $deck->addCard($cardName);
                     * }
                     * //
                     */
                    if ($isSideboard) {
                        $deck->setSideboard($cardName, $deck->getSideboard($cardName) + $stock);
                        if (! $deck->getSideboard($cardName)) {
                            $deck->removeSideboard($cardName);
                        }
                    } else {
                        $deck->setStock($cardName, $deck->getStock($cardName) + $stock);
                        if (! $deck->getStock($cardName)) {
                            $deck->removeCard($cardName);
                        }
                    }
                }
            }
            $this->initUnused();
            // $this->save();
        }
        return $retNodes;
    }

    public function getName() {
        return $this->name;
    }

    public function getFirstname() {
        return isset($this->data['firstname']) ? $this->data['firstname'] : '';
    }

    public function getLastname() {
        return isset($this->data['lastname']) ? $this->data['lastname'] : '';
    }

    public function getDCI() {
        return isset($this->data['dci']) ? $this->data['dci'] : '';
    }

    public function getKey() {
        return $this->key;
    }

    public function getDeckKey(OracleDeck $deck) {
        $ret = null;
        foreach ($this->deckList as $no => $tmpDeck) {
            if ($tmpDeck === $deck) {
                $ret = md5($this->getKey() . ':' . $no);
                break;
            }
        }
        return $ret;
    }

    public function asObject() {
        $ret = [];

        // $ret['name'] = $this->getName();
        $ret['firstname'] = $this->getFirstname();
        $ret['lastname'] = $this->getLastname();
        $ret['dci'] = $this->getDCI();
        // $ret['key'] = $this->getKey();
        $ret['deckList'] = [];
        foreach ($this->deckList as $no => $deck) {
            $ret['deckList'][$no] = $deck->asObject();
        }

        return $ret;
    }

    public function asNode(DOMDocument $dataDoc = null) {
        $returnDocument = $dataDoc === null;

        if ($returnDocument) {
            $dataDoc = new DOMDocument();
        }

        $retNode = $dataDoc->createElement('player');

        $arr = [];
        $arr['name'] = $this->name;
        $arr['key'] = $this->key;

        foreach ($arr as $key => $val) {
            $retNode->setAttribute($key, $val);
        }
        foreach ($this->deckList as $no => $deck) {
            $node = $deck->asNode($dataDoc);
            $node->setAttribute('no', $no);
            $retNode->appendChild($node);
        }

        if ($returnDocument) {
            $dataDoc->appendChild($retNode);
            $retNode = $dataDoc;
        }
        return $retNode;
    }
}