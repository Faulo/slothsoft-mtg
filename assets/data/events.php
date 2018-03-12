<?php
$eventList = [];

$cacheTime = Seconds::WEEK;

// GPTs und PPTQs
$eventTypeCodeNames = [
    'GT' => 'GPT',
    'PPTQ' => 'PPTQ'
];
$formatCodeNames = [
    'SEALED' => 'Limited',
    'STANDARD' => 'Standard',
    'MODERN' => 'Modern',
    'LEGACY' => 'Legacy'
];
$cutoffTime = mktime(0, 0, 0, date('n') - 1, 1, date('Y'));
$EventTypeCodes = array_keys($eventTypeCodeNames);
$LocalTime = sprintf('/Date(%d)/', $cutoffTime);

$uri = 'http://locator.wizards.com/Service/LocationService.svc/GetLocations';
$req = [
    "language" => "en-us",
    "request" => [
        /*
         * "North" => 51.27393381538981,
         * "East" => 14.092830556689137,
         * "South" => 50.8268837846102,
         * "West" => 13.381693643310768,
         * //
         */
        "North" => 52,
        "East" => 15,
        "South" => 50,
        "West" => 9,
        "LocalTime" => $LocalTime,
        "ProductLineCodes" => [],
        "EventTypeCodes" => $EventTypeCodes,
        "PlayFormatCodes" => [],
        "SalesBrandCodes" => [],
        "MarketingProgramCodes" => [],
        "EarliestEventStartDate" => null,
        "LatestEventStartDate" => null
    ],
    "page" => 1,
    "count" => 1000,
    "filter_mass_markets" => true
];
// {"language":"en-us","request":{"North":51.369186315389804,"East":10.440611227198588,"South":50.9221362846102,"West":9.728007772801448,"LocalTime":"/Date(1475625600000)/","ProductLineCodes":["MG"],"EventTypeCodes":[],"PlayFormatCodes":[],"SalesBrandCodes":["MG"],"MarketingProgramCodes":[],"EarliestEventStartDate":null,"LatestEventStartDate":null},"page":1,"count":30,"filter_mass_markets":true}
$header = [
    'method' => 'POST',
    'header' => [
        'content-type' => 'application/json'
    ]
];
if ($res = \Slothsoft\Core\Storage::loadExternalJSON($uri, $cacheTime, json_encode($req), $header)) {
    $businessList = $res['d']['Results'];
    
    $uri = 'http://locator.wizards.com/Service/LocationService.svc/GetLocationDetails';
    
    foreach ($businessList as $business) {
        $req = [
            "language" => "en-us",
            "request" => [
                "BusinessAddressId" => $business['Id'],
                "OrganizationId" => $business['Organization']['Id'],
                "EventTypeCodes" => $EventTypeCodes,
                "PlayFormatCodes" => [],
                "ProductLineCodes" => [],
                "LocalTime" => $LocalTime,
                "EarliestEventStartDate" => null,
                "LatestEventStartDate" => null
            ]
        ];
        
        if ($res = \Slothsoft\Core\Storage::loadExternalJSON($uri, $cacheTime, json_encode($req), $header)) {
            $eveList = $res['d']['Result']['EventsAtVenue'];
            foreach ($eveList as $eve) {
                if (in_array($eve['EventTypeCode'], $EventTypeCodes)) {
                    // my_dump($eve);
                    
                    $startDate = preg_match('/\d+/', $eve['StartDate'], $match) ? (int) substr($match[0], 0, - 3) : 0;
                    
                    $event = [];
                    $event['type'] = $eventTypeCodeNames[$eve['EventTypeCode']];
                    $event['format'] = $formatCodeNames[$eve['PlayFormatCode']];
                    $event['date'] = $startDate;
                    $event['date-start'] = $startDate;
                    $event['date-end'] = $startDate;
                    $event['name'] = $eve['Name'];
                    // $event['name-href'] = sprintf('mailto:%s', $eve['Email']);
                    $event['name-title'] = $eve['AdditionalDetails'];
                    $event['venue'] = $eve['Address']['Name'];
                    $event['city'] = $eve['Address']['City']; // sprintf('%s (%s)', $eve['Address']['City'], $eve['Address']['Name']);
                    $event['venue-href'] = sprintf('http://maps.google.com/?q=%s', rawurlencode(implode(' ', [
                        $eve['Address']['Line1'],
                        $eve['Address']['Line2'],
                        $eve['Address']['Line3'],
                        // $eve['Address']['Line4'],
                        $eve['Address']['PostalCode'],
                        $eve['Address']['City'],
                        // $eve['Address']['Region'],
                        $eve['Address']['CountryName']
                    ])));
                    $event['country'] = $eve['Address']['CountryName'];
                    
                    $eventList[] = $event;
                }
            }
        }
    }
}

$uriList = [];
/*
 * //$uriList[] = 'http://magic.wizards.com/en/protour/pptq1st16/locations';
 * //$uriList[] = 'http://magic.wizards.com/en/protour/pptq2nd16/locations';
 *
 *
 * foreach ($uriList as $uri) {
 * if ($xpath = \Slothsoft\Core\Storage::loadExternalXPath($uri, $cacheTime)) {
 * $tableNodeList = $xpath->evaluate('//table[.//tr[* = "Country"]/*]');
 * if (!$tableNodeList->length) {
 * output($xpath->document);die();
 * }
 * foreach ($tableNodeList as $tableNode) {
 * $keyList = [];
 * $nodeList = $xpath->evaluate('.//tr[* = "Country"]/*', $tableNode);
 * foreach ($nodeList as $node) {
 * $key = $xpath->evaluate('normalize-space(.)', $node);
 * $key = str_replace(['Start', 'Name'], '', $key);
 * $key = strtolower($key);
 * $key = preg_replace('/[^\w]+/', '', $key);
 * $keyList[] = $key;
 * }
 * if (!$keyList) {
 * output($xpath->document);die();
 * continue;
 * }
 * //output($xpath->document);die();
 * $nodeList = $xpath->evaluate('.//tr[not(* = "Country")]', $tableNode);
 * foreach ($nodeList as $node) {
 * $event = [];
 * $event['type'] = 'PPTQ';
 * $event['name-href'] = $xpath->evaluate('normalize-space(.//a[1]/@href)', $node);
 * foreach ($keyList as $i => $key) {
 * $val = $xpath->evaluate(sprintf('normalize-space(td[%d])', $i+1), $node);
 * $event[$key] = $val;
 * }
 * if (isset($event['date'])) {
 * $eventList[] = $event;
 * } else {
 * my_dump($event);
 * }
 * }
 * //$retFragment->appendChild($dataDoc->importNode($node, true));
 * }
 * }
 * }
 * //
 */

$calendarList = [];
$calendarList[] = 'http://magic.wizards.com/en/events/premier-calendar';
foreach ($calendarList as $uri) {
    // *
    if ($xpath = \Slothsoft\Core\Storage::loadExternalXPath($uri, $cacheTime)) {
        $script = $xpath->evaluate('normalize-space(//script[contains(., \'"calendar"\')])');
        /*
         * /
         * if (@$doc = \Slothsoft\Core\DOMHelper::loadDocument($uri, true)) {
         * $xpath = \Slothsoft\Core\DOMHelper::loadXPath($doc);
         * $script = $xpath->evaluate('normalize-space(//script[contains(., \'"calendar"\')])');
         * //
         */
        if (preg_match('/(\{.+\})/', $script, $match)) {
            $script = $match[1];
            if ($data = json_decode($script, true)) {
                // my_dump($data);
                $data = $data['wiz_cal_initial']['calendar'];
                foreach ($data as $events) {
                    $month = $events['name'];
                    $year = preg_match('/\d{4}/', $month, $match) ? $match[0] : date('Y');
                    
                    foreach ($events['events'] as $arr) {
                        // my_dump($arr);
                        switch ($arr['type']) {
                            case 'event':
                                // my_dump($arr);
                                
                                $event = [];
                                if (preg_match('/([1-3]?\d)-([1-3]?\d)/', $arr['dates'], $match)) {
                                    $event['date'] = sprintf('%d %s', $match[1], $month);
                                    $event['date-start'] = sprintf('%d %s', $match[1], $month);
                                    $event['date-end'] = sprintf('%d %s', $match[2], $month);
                                } elseif (preg_match('/(\d+)\/(\d+).+?(\d+)\/(\d+)/', $arr['dates'], $match)) {
                                    $event['date'] = sprintf('%d-%02d-%02d', $year, $match[1], $match[2]);
                                    $event['date-start'] = sprintf('%d-%02d-%02d', $year, $match[1], $match[2]);
                                    $event['date-end'] = sprintf('%d-%02d-%02d', $year, $match[3], $match[4]);
                                } else {
                                    // my_dump($arr);
                                }
                                $event['name'] = trim($arr['title']);
                                $event['name-href'] = $arr['link'];
                                $event['format'] = $arr['format'];
                                
                                $type = '???';
                                $city = '';
                                switch (true) {
                                    case strpos($arr['title'], 'Grand Prix') === 0:
                                        $type = 'Grand Prix';
                                        $city = substr($arr['title'], strlen('Grand Prix '));
                                        break;
                                    case strpos($arr['title'], 'Pro Tour') === 0:
                                        $type = 'Pro Tour';
                                        if (preg_match('/\((\w+)\)/', $arr['title'], $match)) {
                                            $city = $match[1];
                                        }
                                        break;
                                    case strpos($arr['title'], 'RPTQ') === 0:
                                        $type = 'RPTQ';
                                        break;
                                    case strpos($arr['title'], 'World Championship') !== false:
                                        $type = 'World Championship';
                                        break;
                                    case strpos($arr['title'], 'World Magic Cup') !== false:
                                        $type = 'World Magic Cup';
                                        break;
                                    case strpos($arr['title'], 'Magic Online Championship') !== false:
                                        $type = 'Magic Online Championship';
                                        break;
                                    default:
                                        // my_dump($arr['title']);
                                        break;
                                }
                                $event['type'] = $type;
                                $event['city'] = $city;
                                
                                /*
                                 * if ($xpath = \Slothsoft\Core\Storage::loadExternalXPath($arr['link'], $cacheTime)) {
                                 *
                                 * if ($formatNode = $xpath->evaluate('//strong[contains(., "Format:")]')->item(0)) {
                                 * $nodeList = $xpath->evaluate('following-sibling::node()', $formatNode);
                                 * $format = [];
                                 * foreach ($nodeList as $node) {
                                 * if ($node instanceof \DOMElement and $node->tagName === 'br') {
                                 * break;
                                 * }
                                 * $val = $xpath->evaluate('normalize-space(.)', $node);
                                 * if (strlen($val)) {
                                 * $format[] = $val;
                                 * }
                                 * }
                                 * $format = implode(' ', $format);
                                 * $format = str_replace('Constructed', '', $format);
                                 * $event['format'] = $format;
                                 * } else {
                                 * //continue 2;
                                 * }
                                 * if ($descriptionNode = $xpath->evaluate('//div[@class="description"]')->item(0)) {
                                 * $date = $xpath->evaluate('string(p[strong])', $descriptionNode);
                                 * $date = preg_replace('/-.+,/', '', $date);
                                 * $event['date'] = $date;
                                 *
                                 * $address = $xpath->evaluate('string(p[a][contains(., ",")])', $descriptionNode);
                                 * $address = trim($address);
                                 * $address = preg_replace('/(\([^\)]*\))/', '', $address);
                                 * $address = explode("\n", $address);
                                 * $country = explode(',', array_pop($address));
                                 * $city = explode(',', array_pop($address));
                                 * $address = array_merge($address, [$city[0]], $country);
                                 * $event['country'] = array_pop($address);
                                 * $event['city'] = array_pop($address);
                                 * $event['city'] = preg_replace('/\d+/', '', $event['city']);
                                 * }
                                 * } else {
                                 * die($uri);
                                 * }
                                 * //
                                 */
                                
                                $eventList[] = $event;
                                break;
                            default:
                                my_dump($arr);
                                break;
                        }
                    }
                }
            }
        }
    }
}

$wikiList = [];
$wikiList[] = 'https://en.wikipedia.org/wiki/List_of_Magic:_The_Gathering_Grand_Prix_events';
foreach ($wikiList as $uri) {
    if ($xpath = \Slothsoft\Core\Storage::loadExternalXPath($uri, $cacheTime)) {
        $dataList = [];
        $queryList = [];
        $queryList['city'] = 'td[2]';
        $queryList['city-href'] = 'td[2]//@href';
        $queryList['format'] = 'td[3]';
        $queryList['date'] = 'td[4]/*[@class="sorttext"] | td[4]/text()';
        $tableNodeList = $xpath->evaluate('//table[@class="wikitable sortable"][1]');
        foreach ($tableNodeList as $tableNode) {
            $rowNodeList = $xpath->evaluate('tr[td]', $tableNode);
            foreach ($rowNodeList as $rowNode) {
                $data = [];
                foreach ($queryList as $key => $query) {
                    $data[$key] = $xpath->evaluate(sprintf('normalize-space(%s)', $query), $rowNode);
                }
                $dataList[] = $data;
            }
        }
        
        foreach ($dataList as &$data) {
            if (preg_match('/(\d+)\s?[-–]\s?(\d+)/u', $data['date'], $match)) {
                $data['date-start'] = str_replace($match[0], $match[1], $data['date']);
                $data['date-end'] = str_replace($match[0], $match[2], $data['date']);
                $data['date'] = $data['date-start'];
            } elseif (preg_match('/(.+)[-–](.+)/u', $data['date'], $match)) {
                $data['date-start'] = $match[1] . substr($match[2], - 5);
                $data['date-end'] = $match[2];
                $data['date'] = $data['date-start'];
            }
            $data['type'] = 'Grand Prix';
            $data['name'] = $data['type'] . ' ' . $data['city'];
            $data['city-href'] = 'https://en.wikipedia.org' . $data['city-href'];
        }
        unset($data);
        
        foreach ($dataList as $data) {
            $eventList[] = $data;
        }
    }
}

$translationTable = [
    'Sealed' => 'Limited',
    'Booster Draft' => 'Limited',
    'Mixed' => 'Standard' . PHP_EOL . 'Limited',
    '/' => PHP_EOL
];
foreach ($eventList as &$event) {
    $event['format'] = trim(strtr($event['format'], $translationTable));
}
unset($event);

// my_dump($eventList);
$sortList = [];

foreach ($eventList as $i => &$event) {
    $include = true;
    
    $sort = null;
    foreach ([
        'date',
        'date-start',
        'date-end'
    ] as $key) {
        // $val = $event[$key];
        if (! isset($event[$key])) {
            $event[$key] = 0;
        }
        if (is_string($event[$key])) {
            $event[$key] = strtotime($event[$key]);
        }
        
        if ($event[$key] > 0) {
            if ($event[$key] < $cutoffTime) {
                $include = false;
            }
            if ($sort === null) {
                $sort = $event[$key];
            }
            $event[$key] = date(DateTimeFormatter::FORMAT_DATE, $event[$key]);
        } else {
            /*
             * my_dump($val);
             * my_dump($event);
             * //
             */
            if ($sort === null) {
                $sort = PHP_INT_MAX;
            }
            $event[$key] = '???';
        }
    }
    
    foreach ($event as $key => $val) {
        if ($this->httpRequest->getInputValue($key, $val) !== $val) {
            $include = false;
            break;
        }
    }
    if ($include) {
        $sortList[$i] = $sort;
    }
}
unset($event);

foreach ($eventList as $i => &$eventA) {
    if ($eventA and $eventA['type'] === 'Grand Prix') {
        foreach ($eventList as $j => &$eventB) {
            if ($eventA['date-end'] === '11.09.16' and $eventB['date-end'] === '11.09.16') {
                // my_dump($eventA);
                // my_dump($eventB);
                // my_dump([$i !== $j, $eventA['name'] === $eventB['name'], $eventA['date-end'] === $eventB['date-end']]);
            }
            if ($i !== $j and $eventA['name'] === $eventB['name'] and $eventA['date-end'] === $eventB['date-end']) {
                $eventA += $eventB;
                $eventB = null;
            }
        }
        unset($eventB);
    }
}
unset($eventA);

asort($sortList);

$retFragment = $dataDoc->createDocumentFragment();
foreach (array_merge([
    'http://locator.wizards.com'
], $uriList, $calendarList, $wikiList) as $uri) {
    $node = $dataDoc->createElement('reference');
    $node->setAttribute('href', $uri);
    $retFragment->appendChild($node);
}
foreach ($sortList as $i => $tmp) {
    if ($event = $eventList[$i]) {
        $node = $dataDoc->createElement('event');
        foreach ($event as $key => $val) {
            $node->setAttribute($key, $val);
        }
        $retFragment->appendChild($node);
    }
}

return $retFragment;