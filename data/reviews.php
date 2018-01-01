<?php
$oracle = new \Slothsoft\MTG\Oracle('mtg', $dataDoc);

$ret = $dataDoc->createDocumentFragment();

$resDir = $this->getResourceDir('/mtg/reviews');

// mtg.slothsoft.net/Reviews/Kaladesh/
$mode = $this->sitesPath->evaluate('string(page[1]/@name)', $this->requestElement);

// $this->httpRequest->setInputValue('mode', $deckMode);

// $setList = [];

$req = (array) $this->httpRequest->getInputValue('review', []);

foreach ($resDir as $reviewDoc) {
    $reviewFile = $reviewDoc->documentElement->getAttribute('realpath');
    
    $review = $oracle->getReview($reviewFile);
    
    // $setList = array_merge($setList, $review->getSetList());
    
    $node = $review->asNode($dataDoc);
    
    switch ($mode) {
        case 'edit':
            if (isset($req[$review->getName()])) {
                $review->updateData($req[$review->getName()]);
                $review->save();
            }
            break;
        default:
            $mode = 'view';
            break;
    }
    
    $node->setAttribute('mode', $mode);
    $ret->appendChild($node);
}

// $setList = array_unique($setList);

return $ret;