<?php
$keyword = $this->httpRequest->getInputValue('search');

if (! $keyword) {
    return;
}

$oracle = new \Slothsoft\MTG\Oracle('mtg', $dataDoc);

$setList = $oracle->getSetList();
foreach ($setList as $set) {
    // echo $set['name'] . PHP_EOL;
}
$aliasList = [];
$aliasList['Conspiracy'] = 'Magic: The Gathering—Conspiracy';
$aliasList['Magic 2014'] = 'Magic 2014 Core Set';
$aliasList['Magic 2015'] = 'Magic 2015 Core Set';
$aliasList['Sixth Edition'] = 'Classic Sixth Edition';
$aliasList['Unlimited'] = 'Unlimited Edition';
$aliasList['Alpha'] = 'Limited Edition Alpha';
$aliasList['Beta'] = 'Limited Edition Beta';

$baseURI = 'https://www.magiccardmarket.eu/?mainPage=showSearchResult&searchFor=%s&resultsPage=%d';

$resList = [];
$attrList = [];
$attrList['name'] = 'normalize-space(.//a)';
$attrList['href'] = 'concat("https://www.magiccardmarket.eu", .//a/@href)';
$attrList['category'] = 'normalize-space(td[6])';
$attrList['price'] = 'normalize-space(td[last()])';
$attrList['set'] = 'normalize-space(.//span/@onmouseover)';

$categoryList = [];

for ($i = 0; $i < 10; $i ++) {
    $found = false;
    $uri = sprintf($baseURI, urlencode($keyword), $i);
    if ($xpath = \Slothsoft\Core\Storage::loadExternalXPath($uri, Seconds::DAY)) {
        // output($xpath->document);die();
        $tableNodeList = $xpath->evaluate('//*[@class="MKMTable SearchTable"]');
        foreach ($tableNodeList as $tableNode) {
            $rowNodeList = $xpath->evaluate('tbody/tr', $tableNode);
            foreach ($rowNodeList as $rowNode) {
                $res = [];
                foreach ($attrList as $attr => $query) {
                    $res[$attr] = $xpath->evaluate($query, $rowNode);
                }
                $res['set'] = preg_match('/\'(.+)\'/', $res['set'], $match) ? stripslashes($match[1]) : '';
                if (isset($aliasList[$res['set']])) {
                    $res['set'] = $aliasList[$res['set']];
                }
                $res['set-size'] = isset($setList[$res['set']]) ? $setList[$res['set']]['count'] : '-';
                $res['price-float'] = str_replace(',', '.', $res['price']);
                $res['price-float'] = (float) $res['price-float'];
                if (! $res['price-float']) {
                    $res['price-float'] = 99999;
                }
                $categoryList[$res['category']] = true;
                // $res['price'] = sprintf('%6.2f', $res['price']);
                if ($res['name']) {
                    $resList[] = $res;
                    $found = true;
                }
            }
        }
    }
    if (! $found) {
        break;
    }
}

$retNode = $dataDoc->createElement('search');

foreach ($resList as $res) {
    $node = $dataDoc->createElement('article');
    foreach ($res as $key => $val) {
        $node->setAttribute($key, $val);
        $retNode->appendChild($node);
    }
}
foreach ($categoryList as $category => $tmp) {
    $node = $dataDoc->createElement('category');
    $node->setAttribute('name', $category);
    $retNode->appendChild($node);
}

return $retNode;