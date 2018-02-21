<?php
return \Slothsoft\MTG\OracleInfo::getSetFragment($dataDoc);

$oracle = new \Slothsoft\MTG\Oracle('mtg', $dataDoc);

// $idTable = $oracle->getIdTable();
// $xmlTable = $oracle->getXMLTable();

$expansionList = [];
$docList = $this->getResourceDir('/mtg/db', 'xml');
foreach ($docList as $resName => $doc) {
    $nodeList = $doc->getElementsByTagName('line');
    foreach ($nodeList as $node) {
        $key = $node->getAttribute('SET');
        $id = (int) $node->getAttribute('ID');
        if ($id > 0) {
            if (! isset($expansionList[$key])) {
                $expansionList[$key] = $id;
            }
        }
    }
}
asort($expansionList);
$expansionList = array_keys($expansionList);

$setURL = 'http://magiccards.info/sitemap.html';
$exceptionList = [];
$exceptionList['Masters Edition'] = 'MTGO Masters Edition';
$exceptionList['Masters Edition II'] = 'MTGO Masters Edition II';
$exceptionList['Masters Edition III'] = 'MTGO Masters Edition III';
$exceptionList['Masters Edition IV'] = 'MTGO Masters Edition IV';
$exceptionList['Magic: The Gathering-Commander'] = 'Commander';
$exceptionList['Duel Decks: Phyrexia vs. the Coalition'] = 'Duel Decks: Phyrexia vs. The Coalition';
$exceptionList['Promo set for Gatherer'] = 'Media Inserts';
$exceptionList['Modern Event Deck 2014'] = 'Modern Event Deck';
$exceptionList['Magic: The Gathering—Conspiracy'] = 'Conspiracy';
$exceptionList['Magic 2015 Core Set'] = 'Magic 2015';

$fixedList = [];
// $fixedList['Vanguard'] = 'van';
// $fixedList['Magic 2015 Core Set'] = 'm15';
// $fixedList['Magic: The Gathering—Conspiracy'] = 'cns';
// $fixedList['Homelands'] = '_hl';
// $fixedList['Fallen Empires'] = '_fe';
// $fixedList['The Dark'] = '_dk';
// $fixedList['Legends'] = '_lg';
// $fixedList['Antiquities'] = '_aq';
// $fixedList['Arabian Nights'] = '_an';
// $fixedList['Chronicles'] = '_ch';

$skipList = [];
$skipList[] = 'Vanguard';

$abbrList = [];

if ($xpath = \Slothsoft\Core\Storage::loadExternalXPath($setURL, TIME_DAY)) {
    foreach ($expansionList as $expansion) {
        if (in_array($expansion, $skipList)) {
            continue;
        }
        $q = strpos($expansion, '"') === false ? '"' : "'";
        $exp = isset($exceptionList[$expansion]) ? $exceptionList[$expansion] : $expansion;
        $query = sprintf('normalize-space(//li/a[normalize-space(.) = %s%s%s]/following-sibling::small)', $q, $exp, $q);
        
        $abbr = isset($fixedList[$expansion]) ? $fixedList[$expansion] : $xpath->evaluate($query);
        
        if ($abbr) {
            if (isset($abbrList[$abbr])) {
                my_dump([
                    $abbr,
                    $abbrList[$abbr],
                    $expansion
                ]);
            } else {
                $abbrList[$abbr] = $expansion;
            }
        } else {
            // echo $query . PHP_EOL;
            // my_dump($expansion);
        }
    }
}

return \Slothsoft\Farah\HTTPFile::createFromJSON($abbrList);