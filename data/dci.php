<?php
$dir = realpath(__DIR__ . '/../res/dci');

if ($dir and $html = $this->httpRequest->getInputValue('html')) {
    $doc = new \DOMDocument();
    @$doc->loadHTML($html);
    $xpath = new \DOMXPath($doc);
    $dciNo = $xpath->evaluate('string(//*[@name="DCINumber"]/@value)');
    if ($dciNo) {
        $file = $dir . DIRECTORY_SEPARATOR . $dciNo . '.xml';
        $doc->save($file);
    } else {
        $doc->loadHTML($html);
    }
}

$retFragment = $dataDoc->createDocumentFragment();

$logDocList = $this->getResourceDir('/mtg/dci-log', 'xml');

$formatTranslation = [];
$formatTranslation['Casual - Limited'] = 'Booster Draft';
$formatTranslation['Conspiracy Draft'] = 'Booster Draft';
$formatTranslation['2 HG Sealed'] = '2HG Sealed';
$formatTranslation['2 HG Booster Draft'] = '2HG Booster Draft';
$formatTranslation['Trios - Limited'] = 'Team Sealed';

$formatList = [];
$opponentList = [];
$storeList = [];
$yearList = [];
foreach ($logDocList as $logDoc) {
    $retNode = $dataDoc->createElement('dci');
    $retNode->setAttribute('number', (float) $logDoc->documentElement->getAttribute('path'));
    
    $xpath = $this->loadXPath($logDoc);
    $rowNodeList = $xpath->evaluate('//*[@class="MatchHistoryRow"]');
    foreach ($rowNodeList as $rowNode) {
        $opponents = [];
        /*
         * $nodeList = $xpath->evaluate('.//*[@class="TeamOpponent"]', $rowNode);
         * foreach ($nodeList as $node) {
         * $opponents[] = $xpath->evaluate('normalize-space(.)', $node);
         * }
         * //
         */
        $arr = [];
        $arr['format'] = $xpath->evaluate('normalize-space(preceding::*[@class="EventFormat"][1]/text())', $rowNode);
        $arr['store'] = $xpath->evaluate('normalize-space(preceding::*[@class="HistoryPanelHeaderLabel Location"][1])', $rowNode);
        $arr['date'] = $xpath->evaluate('normalize-space(preceding::*[@class="HistoryPanelHeaderLabel Date"][1])', $rowNode);
        $arr['date-timestamp'] = strtotime($arr['date']);
        $arr['date-year'] = date('Y', $arr['date-timestamp']);
        $arr['date-month'] = date('m', $arr['date-timestamp']);
        $arr['date-day'] = date('d', $arr['date-timestamp']);
        $nodeList = $xpath->evaluate('.//*[@class][not(contains(., "Multiple Opponents"))]', $rowNode);
        foreach ($nodeList as $node) {
            $key = $node->getAttribute('class');
            $key = strtolower(substr($key, strlen('Match')));
            $val = $xpath->evaluate('normalize-space(.)', $node);
            $arr[$key] = $val;
        }
        if ($arr['result'] === 'Bye') {
            $arr['result'] = 'Win';
            $arr['opponent'] = '(Bye)';
        } else {
            $arr['opponent'] = '';
        }
        if (isset($arr['opponent'])) {
            $opponents[] = $arr['opponent'];
        }
        if (isset($formatTranslation[$arr['format']])) {
            $arr['format'] = $formatTranslation[$arr['format']];
        }
        $formatList[$arr['format']] = $arr['format'];
        $storeList[$arr['store']] = $arr['store'];
        $yearList[$arr['date-year']] = $arr['date-year'];
        
        foreach ($opponents as &$opponent) {
            if ($opponent === '') {
                $opponent = 'UNKNOWN, UNKNOWN';
            }
            if ($opponent === 'UNKNOWN, UNKNOWN') {
                $opponent = '(Anonymous)';
            }
            $opponent = implode(' ', array_reverse(explode(', ', $opponent)));
        }
        unset($opponent);
        
        $opponents = array_unique($opponents);
        
        foreach ($opponents as $opponent) {
            if (strlen($opponent)) {
                $arr['opponent'] = $opponent;
                $opponentList[$opponent] = $opponent;
                
                $node = $dataDoc->createElement('match');
                foreach ($arr as $key => $val) {
                    $node->setAttribute($key, $val);
                }
                $retNode->appendChild($node);
            }
        }
    }
    $retFragment->appendChild($retNode);
}

foreach ($formatList as $name) {
    $node = $dataDoc->createElement('format');
    $node->setAttribute('name', $name);
    $retNode->appendChild($node);
}
foreach ($opponentList as $name) {
    $node = $dataDoc->createElement('opponent');
    $node->setAttribute('name', $name);
    $retNode->appendChild($node);
}
foreach ($storeList as $name) {
    $node = $dataDoc->createElement('store');
    $node->setAttribute('name', $name);
    $retNode->appendChild($node);
}

foreach (range(min($yearList), date('Y') + 1) as $name) {
    $node = $dataDoc->createElement('year');
    $node->setAttribute('name', $name);
    $retNode->appendChild($node);
}
foreach (range(1, 12) as $month) {
    $time = mktime(0, 0, 0, $month);
    $node = $dataDoc->createElement('month');
    $node->setAttribute('no', date('m', $time));
    $node->setAttribute('name', date('M', $time));
    $retNode->appendChild($node);
}

return $retFragment;