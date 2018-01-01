<?php
$oracle = new \Slothsoft\MTG\Oracle('mtg', $dataDoc);

// $idTable = $oracle->getIdTable();
// $xmlTable = $oracle->getXMLTable();

$retFragment = $dataDoc->createDocumentFragment();

$resDir = $this->getResourceDir('/mtg/players');
$templateDoc = $this->getTemplateDoc('/mtg/sites');
$dom = new \Slothsoft\Core\DOMHelper();

foreach ($resDir as $key => $doc) {
    $playerFile = $doc->documentElement->getAttribute('realpath');
    $player = $oracle->getPlayer($playerFile);
    $playerDoc = new \DOMDocument();
    
    $playerNode = $player->asNode($playerDoc, false);
    $playerDoc->appendChild($playerNode);
    
    // return \Slothsoft\CMS\HTTPFile::createFromDocument($playerDoc);
    
    $sitesNode = $dom->transformToFragment($playerDoc, $templateDoc, $dataDoc);
    $retFragment->appendChild($sitesNode);
}

return $retFragment;