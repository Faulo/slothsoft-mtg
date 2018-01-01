<?php
namespace Slothsoft\MTG;

use Slothsoft\Core\DOMHelper;
$dataDoc = DOMHelper::loadDocument($this->getResourcePath('mtg/oracle'));
$retNode = $dataDoc->documentElement;

$query = $this->httpRequest->getInputValue('search-query');

if ($query !== null) {
    $oracle = new Oracle('mtg', $dataDoc);
    $retNode->appendChild($oracle->createSearchElement($dataDoc, $query));
}

return $retNode;