<?php
$card = [];
$card['expansion_name'] = $this->httpRequest->getInputValue('expansion_name');
$card['expansion_abbr'] = $this->httpRequest->getInputValue('expansion_abbr');
$card['rarity'] = $this->httpRequest->getInputValue('rarity', 'R');

if ($card['expansion_name']) {
    $oracle = new \Slothsoft\MTG\Oracle('mtg', $dataDoc);
    $idTable = $oracle->getIdTable();
    $setList = $idTable->getSetList();
    $card['expansion_abbr'] = array_search($card['expansion_name'], $setList);
    
    if (! $card['expansion_abbr']) {
        my_dump($setList);
        my_dump($card['expansion_name']);
    }
}

$path = \Slothsoft\MTG\OracleInfo::getRarityPath($card);
if ($path = realpath($path)) {
    return \Slothsoft\CMS\HTTPFile::createFromPath($path);
}