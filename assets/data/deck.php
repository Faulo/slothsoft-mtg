<?php
$oracle = new \Slothsoft\MTG\Oracle('mtg', $dataDoc);

// $idTable = $oracle->getIdTable();
// $xmlTable = $oracle->getXMLTable();

$retFragment = $dataDoc->createDocumentFragment();

$resDir = $this->getResourceDir('/mtg/players');

$deckMode = $this->sitesPath->evaluate('string(page[1]/@name)', $this->requestElement);
$deckNo = $this->sitesPath->evaluate('string(page[2]/@name)', $this->requestElement);
$playerName = $this->sitesPath->evaluate('string(page[3]/@name)', $this->requestElement);

// $playerName .= '.json';

$this->httpRequest->setInputValue('mode', $deckMode);

if (isset($resDir[$playerName])) {
    $playerDoc = $resDir[$playerName];
    $playerFile = $playerDoc->documentElement->getAttribute('realpath');
    // $player = new \Slothsoft\MTG\Player($playerFile, $oracle);
    $player = $oracle->getPlayer($playerFile);
    $deck = $player->getDeck($deckNo);
    
    $nodeList = $deck->parseRequest($this->httpRequest, $dataDoc);
    $player->save();
    foreach ($nodeList as $node) {
        $retFragment->appendChild($node);
    }
    
    $deckNode = $deck->asNode($dataDoc);
    $deckNode->setAttribute('mode', $deckMode);
    $retFragment->appendChild($deckNode);
}
return $retFragment;