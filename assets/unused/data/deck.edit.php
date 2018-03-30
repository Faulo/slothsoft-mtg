<?php
$oracle = new \Slothsoft\MTG\Oracle('mtg', $dataDoc);

// $idTable = $oracle->getIdTable();
// $xmlTable = $oracle->getXMLTable();

$ret = null;

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
    
    $uri = null;
    switch ($deckMode) {
        case 'new':
            $deckNo = $player->createDeck($deckNo);
            $player->save();
            $uri = $this->findUri($this->requestedPage, true) . '../view/';
            break;
        case 'delete':
            $player->removeDeck($deckNo);
            $player->save();
            $uri = $this->findUri($this->requestedPage, true) . '../../';
            break;
        case 'move-up':
            $deckNo = $player->moveDeck($deckNo, - 1);
            $player->save();
            $uri = $this->findUri($this->requestedPage, true) . '../../' . $deckNo . '/view/';
            break;
        case 'move-down':
            $deckNo = $player->moveDeck($deckNo, 1);
            $player->save();
            $uri = $this->findUri($this->requestedPage, true) . '../../' . $deckNo . '/view/';
            // my_dump($uri);die();
            break;
    }
    
    // $uri = sprintf('%s%s/', $this->findUri($this->requestedPage->parentNode, true), $deckNo);
    
    // my_dump($uri);
    
    if ($uri) {
        $this->httpResponse->setRedirect($uri, false, true);
        $this->progressStatus = self::STATUS_RESPONSE_SET;
    }
    // my_dump($uri);
    
    /*
     * $nodeList = $deck->parseRequest($this->httpRequest, $dataDoc);
     * $player->save(true);
     * foreach ($nodeList as $node) {
     * $retFragment->appendChild($node);
     * }
     *
     * $deckNode = $deck->asNode($dataDoc);
     * $deckNode->setAttribute('mode', $deckMode);
     * $retFragment->appendChild($deckNode);
     * //
     */
}
return $ret;