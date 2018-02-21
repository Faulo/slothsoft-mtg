<?php
$oracle = new \Slothsoft\MTG\Oracle('mtg', $dataDoc);

// $idTable = $oracle->getIdTable();
// $xmlTable = $oracle->getXMLTable();

$retFragment = $dataDoc->createDocumentFragment();

$resDir = $this->getResourceDir('/mtg/players');

$deckMode = 'view';
$deckKey = $this->httpRequest->getInputValue('deck');

// my_dump($resDir);

foreach ($resDir as $playerDoc) {
    $playerFile = $playerDoc->documentElement->getAttribute('realpath');
    $player = $oracle->getPlayer($playerFile);
    
    if ($deck = $player->getDeckByKey($deckKey)) {
        $deckNode = $deck->asNode($dataDoc);
        $deckNode->setAttribute('mode', $deckMode);
        $retFragment->appendChild($deckNode);
    }
}

return $retFragment;