<?php
if ($name = $this->httpRequest->getInputValue('name')) {
    $oracle = new \Slothsoft\MTG\Oracle('mtg', $dataDoc);
    $idTable = $oracle->getIdTable();
    $card = $idTable->getCardByName($name);
    if (! $card) {
        throw new Exception(sprintf('Card with name "%s" not found!', $name));
    }
} else {
    $card = [];
    $card['expansion_abbr'] = $this->httpRequest->getInputValue('expansion_abbr');
    $card['expansion_index'] = $this->httpRequest->getInputValue('expansion_index');
}

$path = \Slothsoft\MTG\OracleInfo::getImagePath($card);
if ($path = realpath($path)) {
    return \Slothsoft\Farah\HTTPFile::createFromPath($path);
}