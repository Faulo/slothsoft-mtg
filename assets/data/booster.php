<?php
$oracle = new \Slothsoft\MTG\Oracle('mtg', $dataDoc);

$oracleNode = $dataDoc->createElement('oracle');
$oracleNode->appendChild($oracle->createCategoriesElement($dataDoc));

if ($set = $this->httpRequest->getInputValue('set')) {
    $query = [];
    $query['expansion_name'] = $set;
    
    if ($cards = $this->httpRequest->getInputValue('cards')) {
        $query['name'] = $cards;
        $searchNode = $oracle->createSearchElement($dataDoc, $query);
        $oracleNode->appendChild($searchNode);
    } else {
        $amountList = [];
        $amountList[] = [
            1,
            [
                'land'
            ]
        ];
        $amountList[] = [
            10,
            [
                'common'
            ]
        ];
        $amountList[] = [
            3,
            [
                'uncommon'
            ]
        ];
        $amountList[] = [
            1,
            [
                'rare',
                'rare',
                'mythic'
            ]
        ];
        
        $searchNode = $oracle->createSearchElement($dataDoc, []);
        $cards = [];
        foreach ($amountList as $arr) {
            $amount = $arr[0];
            $rarityList = $arr[1];
            $nodeList = [];
            $searchList = [];
            foreach ($rarityList as $rarity) {
                $query['rarity'] = $rarity;
                $tmpNode = $oracle->createSearchElement($dataDoc, $query);
                foreach ($tmpNode->getElementsByTagName('card') as $node) {
                    $nodeList[] = $node->cloneNode(true);
                }
            }
            $amount = min($amount, count($nodeList));
            while (count($searchList) < $amount) {
                shuffle($nodeList);
                $node = array_pop($nodeList);
                $searchList[$node->getAttribute('name')] = true;
            }
            $cards += $searchList;
        }
        if ($cards) {
            $cards = array_keys($cards);
            $cards = implode('|', $cards);
            
            $href = sprintf('%s?%s', $this->findUri($this->requestedPage, true), http_build_query([
                'set' => $set,
                'cards' => $cards
            ]));
            
            $this->httpResponse->setRedirect($href, false, true);
            $this->progressStatus = self::STATUS_RESPONSE_SET;
        }
    }
}

return $oracleNode;