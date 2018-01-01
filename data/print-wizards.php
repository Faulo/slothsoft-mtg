<?php
$dataRoot = $dataDoc->createElement('print');

$uriList = [];

// 'http://magic.wizards.com/en/articles/archive/card-image-gallery/kaladesh';
$uri = $this->httpRequest->getInputValue('uri');

if ($xpath = $this->loadExternalXPath($uri)) {
    $nodeList = $xpath->evaluate('//p[@class="rtecenter"]/img');
    foreach ($nodeList as $node) {
        $uriList[] = $node->getAttribute('src');
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