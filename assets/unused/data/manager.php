<?php
$oracle = new \Slothsoft\MTG\Oracle('mtg', $dataDoc);

// $idTable = $oracle->getIdTable();
// $xmlTable = $oracle->getXMLTable();

$ret = null;

$resDir = $this->getResourceDir('/mtg/players');

$playerName = $this->httpRequest->getInputValue('player');

// $playerName .= '.json';

if (isset($resDir[$playerName])) {
    $playerDoc = $resDir[$playerName];
    $playerFile = $playerDoc->documentElement->getAttribute('realpath');
    // $player = new \Slothsoft\MTG\Player($playerFile, $oracle);
    $player = $oracle->getPlayer($playerFile);
    $player->parseRequest($this->httpRequest, $dataDoc);
    $player->save();
    
    $ret = \Slothsoft\Farah\HTTPFile::createFromDocument($player->asNode());
    
    /*
     * $nodeList = $player->parseRequest($this->httpRequest, $dataDoc);
     * $player->save();
     *
     * $ret = $dataDoc->createDocumentFragment();
     * foreach ($nodeList as $node) {
     * $ret->appendChild($node);
     * }
     * $playerNode = $player->asNode($dataDoc);
     * $ret->appendChild($playerNode);
     * //
     */
}
return $ret;