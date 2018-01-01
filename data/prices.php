<?php
namespace Slothsoft\MTG;

$oracle = new \Slothsoft\MTG\Oracle('mtg', $dataDoc);
$mkm = new MKM($oracle);

return $mkm->createShoppingElement($dataDoc, (array) $this->httpRequest->getInputValue('shopping'));

$oracle = new \Slothsoft\MTG\Oracle('mtg', $dataDoc);

$oracleNode = $dataDoc->createElement('oracle');
$oracleNode->appendChild($oracle->createCategoriesElement($dataDoc));

$host = 'https://www.magiccardmarket.eu';
$storageTime = TIME_DAY;
$languageList = [
    'German',
    'English'
];
$standardList = \Slothsoft\MTG\OracleInfo::getStandardLegalList();
$modernList = array_diff(\Slothsoft\MTG\OracleInfo::getModernLegalList(), $standardList);

$boosterList = [];
for ($i = 0; $i < 10; $i ++) {
    $uri = sprintf('/Products/Boosters?onlyAvailable=yes&sortBy=releaseDate&sortDir=desc&view=list&resultsPage=%d', $i);
    if ($xpath = \Slothsoft\Core\Storage::loadExternalXPath($host . $uri, $storageTime)) {
        $rowNodeList = $xpath->evaluate('//table[@class="MKMTable fullWidth"]/tbody/tr/td/a');
        $success = false;
        foreach ($rowNodeList as $rowNode) {
            $success = true;
            $name = $xpath->evaluate('normalize-space(.)', $rowNode);
            $uri = $rowNode->getAttribute('href');
            $boosterList[$uri] = $name;
        }
        if (! $success) {
            break;
        }
    }
}

$shopList = [];

foreach ($boosterList as $uri => $name) {
    if ($xpath = \Slothsoft\Core\Storage::loadExternalXPath($host . $uri, $storageTime)) {
        $rowNodeList = $xpath->evaluate('//table[@class="MKMTable specimenTable MKMSortable fullWidth"]/tbody/tr[.//*[@onmouseover="showMsgBox(\'Item location: Germany\')"]]');
        foreach ($rowNodeList as $rowNode) {
            $shop = $xpath->evaluate('normalize-space(td[1]//a)', $rowNode);
            $language = $xpath->evaluate('normalize-space(td[2]//@onmouseover)', $rowNode);
            $language = preg_replace('/^.+\'(.+)\'.+$/', '$1', $language);
            $price = $xpath->evaluate('normalize-space(td[5])', $rowNode);
            $price = str_replace(',', '.', $price);
            $price = (float) $price;
            
            if (in_array($language, $languageList)) {
                if (! isset($shopList[$shop])) {
                    $shopList[$shop] = [];
                }
                $shopList[$shop][] = [
                    'name' => $name,
                    'language' => $language,
                    'price' => $price
                ];
            }
        }
    }
}

/*
 * $sortList = [];
 * foreach ($shopList as $key => $val) {
 * $sortList[$key] = count($val);
 * }
 *
 * array_multisort($sortList, $shopList);
 * $shopList = array_reverse($shopList);
 * //
 */

foreach ($shopList as $shopName => $list) {
    $shopNode = $dataDoc->createElement('shop');
    $shopNode->setAttribute('name', $shopName);
    foreach ($list as $arr) {
        $node = $dataDoc->createElement('price');
        $node->setAttribute('booster', $arr['name']);
        $node->setAttribute('language', $arr['language']);
        $node->textContent = sprintf('%.2f', $arr['price']);
        $shopNode->appendChild($node);
    }
    $oracleNode->appendChild($shopNode);
}

foreach ($boosterList as $boosterURI => $boosterName) {
    $boosterNode = $dataDoc->createElement('booster');
    $format = 'Vintage';
    foreach ($standardList as $set) {
        if (strpos($boosterName, $set) === 0) {
            $format = 'Standard';
        }
    }
    foreach ($modernList as $set) {
        if (strpos($boosterName, $set) === 0) {
            $format = 'Modern';
        }
    }
    $boosterNode->setAttribute('href', $host . $boosterURI);
    $boosterNode->setAttribute('format', $format);
    $boosterNode->textContent = $boosterName;
    $oracleNode->appendChild($boosterNode);
}

return $oracleNode;