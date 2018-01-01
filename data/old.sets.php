<?php
$retFragment = $dataDoc->createDocumentFragment();

$wikiHost = 'http://wiki.mtgsalvation.com';
$wikiArticle = '%s/article/%s';

$blockList = [
    'Core sets' => [
        'Magic 2014',
        'Magic 2013'
    ],
    'Return to Ravnica block' => [
        'Dragon\'s Maze',
        'Gatecrash',
        'Return to Ravnica'
    ],
    'Innistrad block' => [
        'Avacyn Restored',
        'Dark Ascension',
        'Innistrad'
    ],
    'Scars of Mirrodin block' => [
        'New Phyrexia',
        'Mirrodin Besieged',
        'Scars of Mirrodin'
    ],
    'Zendikar block' => [
        'Zendikar',
        'Worldwake',
        'Rise of the Eldrazi'
    ],
    'Alara block' => [
        'Alara Reborn',
        'Conflux',
        'Shards of Alara'
    ]
];

foreach ($blockList as $blockName => $setList) {
    $blockNode = $dataDoc->createElement('block');
    
    $arr = [];
    $arr['name'] = $blockName;
    $arr['uri'] = sprintf($wikiArticle, $wikiHost, str_replace(' ', '_', $blockName));
    foreach ($arr as $key => $val) {
        $blockNode->setAttribute($key, $val);
    }
    
    foreach ($setList as $setName) {
        $setNode = $dataDoc->createElement('set');
        
        $arr = [];
        $arr['name'] = $setName;
        $arr['uri'] = sprintf($wikiArticle, $wikiHost, str_replace(' ', '_', $setName));
        
        if ($doc = self::loadExternalDocument($arr['uri'], 'html', TIME_MONTH)) {
            $xpath = self::loadXPath($doc);
            if ($imageLink = $xpath->evaluate('string(.//tr[.//*[normalize-space(.) = "Set symbol"]]//img/@src)')) {
                // $imageLink = str_replace('/thumb/', '/', $imageLink);
                
                $arr['image'] = $wikiHost . $imageLink;
            }
        }
        
        foreach ($arr as $key => $val) {
            $setNode->setAttribute($key, $val);
        }
        $blockNode->appendChild($setNode);
    }
    $retFragment->appendChild($blockNode);
}
return $retFragment;