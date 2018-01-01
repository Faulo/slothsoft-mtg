<?php
$dataRoot = $dataDoc->createElement('print');

$uriList = [];

if ($uri = $this->httpRequest->getInputValue('uri')) {
    $uriList = array_fill(0, 20, $uri);
    // $dataRoot->setAttribute('uri', $uri);
}

if ($deckKey = $this->httpRequest->getInputValue('deck')) {
    $oracle = new \Slothsoft\MTG\Oracle('mtg', $dataDoc);
    $resDir = $this->getResourceDir('/mtg/players');
    
    foreach ($resDir as $playerDoc) {
        $playerFile = $playerDoc->documentElement->getAttribute('realpath');
        $player = $oracle->getPlayer($playerFile);
        
        if ($deck = $player->getDeckByKey($deckKey)) {
            $deckNode = $deck->asNode($dataDoc);
            
            $arrList = [];
            $nodeList = $deckNode->getElementsByTagName('card');
            foreach ($nodeList as $node) {
                $sort = $node->getAttribute('sort') . $node->getAttribute('name');
                $uri = $node->getAttribute('href-image');
                $stock = (int) $node->getAttribute('stock');
                if ($stock > 0) {
                    $arrList[$sort] = array_fill(0, $stock, $uri);
                }
            }
            ksort($arrList);
            
            $uriList = [];
            foreach ($arrList as $arr) {
                $uriList = array_merge($uriList, $arr);
            }
        }
    }
}

$arrList = array_chunk($uriList, 9);

foreach ($arrList as $arr) {
    $pageNode = $dataDoc->createElement('page');
    foreach ($arr as $uri) {
        $node = $dataDoc->createElement('card');
        $node->setAttribute('uri', $uri);
        $pageNode->appendChild($node);
    }
    $dataRoot->appendChild($pageNode);
}

return $dataRoot;