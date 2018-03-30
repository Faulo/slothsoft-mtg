<?php
$url = 'https://accounts.wizards.com/Widget/ShowLogin?returnUrl=http%3A%2F%2Fwww.wizards.com%2FMagic%2FPlaneswalkerPoints%2F&requiredFieldSets=playlocation+name';

if ($xpath = \Slothsoft\Core\Storage::loadExternalXPath($url, 0)) {
    $frameNodeList = $xpath->evaluate('//*[@id="samlWidget"]');
    foreach ($frameNodeList as $frameNode) {
        $frameNode->setAttribute('src', 'https://accounts.wizards.com/Widget/SamlWidget');
        return $frameNode;
    }
    
    $formNodeList = $xpath->evaluate('//*[@id="loginForm"]');
    foreach ($formNodeList as $formNode) {
        return $formNode;
        
        $data = [];
        $inputNodeList = $xpath->evaluate('.//*[@name]', $formNode);
        foreach ($inputNodeList as $inputNode) {
            $key = $inputNode->getAttribute('name');
            $val = $inputNode->getAttribute('value');
            $data[$key] = $val;
        }
        my_dump($data);
    }
}

die();

$host = 'http://www.wizards.com';

$planeswalkerURI = '/Magic/PlaneswalkerPoints/';

$pointsURI = '/Magic/PlaneswalkerPoints/JavaScript/GetPointsSummary/';
$pointsURI = '/Magic/PlaneswalkerPoints/JavaScript/GetPointsHistory/';
$eventURI = '/Magic/PlaneswalkerPoints/JavaScript/GetEventSummary/';

$playerList = [];
$playerList['2112587694'] = 'Daniel Schulz';
$playerList['2112587697'] = 'Steffi Schulz';

$retFragment = $dataDoc->createDocumentFragment();

foreach ($playerList as $no => $name) {
    $url = $host . $pointsURI . $no;
    
    $header = [];
    $header['X-Requested-With'] = 'XMLHttpRequest';
    $header['referer'] = $host . $planeswalkerURI . $no;
    $header['cookie'] = 'f5_cspm=1234; __utma=75931667.574082148.1422476871.1422476871.1422476871.1; __utmz=75931667.1422476871.1.1.utmcsr=(direct)|utmccn=(direct)|utmcmd=(none); JudgeCenter.CulturePref=de; PlaneswalkerPointsSettings=0=0&readnotifications=2841,2840,2839,2837,2836,2838,2835,2834,2783,2784,2781&lastviewed=2112587694; WizardsWebsiteSettings=language=en-us; BIGipServerWWWPWPPOOL01=370346250.20480.0000; __utmb=75931667.3.10.1422476871; __utmc=75931667; BIGipServerWWWPool1=3826256138.20480.0000; PWP.ASPXAUTH=CC31FB1EC22F749DB3EAF8FD37D5917FDE38AD329074A62C568512230267D326D290FB1962B8555A0A9CF10895448EF61D0C3CE666F03DA44E217A3B26547D264595AB6CE086C8795C779AE25E6F1616C4FEA06236611D84B9A3F1AF8FE145732BF064CA5915BF8D594FD8338FEC844435F49359C0EBCC48DED44D0D3DBC1A85676914385D3AFA1063A54153E592BA5D3442660A13A1106D5F96A9E95E45C2A5DEC97BB1AAD9A2917E5128831C52A1B78373261518DAAB4372830921DF8E1C4AD96E2C64985C910E1DD9F3E77C02F07CC35CADA2DDBBC6CBBB46D79E1516793076C1857F10B87B636D7A1D4AD195690B9B78B51964DE3EFBE04F32FFF5C9598FD79A4B9D7EBDB5C7AEF14A5C11DD7DC8C186DC07F4C1A6AD4EEB5A97EE330A015EF3728A3A009F93C99EAB73A1FE939991262B3AA5FA813D3558307FE13A0867934C94A2B48493F4A3C78B163A7DB59BED17FCBF4F9D407A33431ECA407EE7FB9478FFBC6DA7EBAFFC95DDBDCDB4413BCDABAC24F3CB1ADDB6238773D740BFE8643128F95ACC8A77851D9CEE5C3A783E39C199AEA485A9F0D3B4EE9B74927E0F696FCF9AE6A634DAA9D9BDD9529BB4428E538EA137DB92DF3A55ACD41E910AB453E4BD34; __utmt=1';
    
    $header['cookie'] = 'PWP.ASPXAUTH=A4E51BB6EE48EA2C2533F61B4CAC3EC29C08042E142357366BB56786D789037FD940D2334CE04DEFCF9317ECE370EC8DD1879073B507AEE4DB63B18A5185471140322D587F81BF648E38936B61AF6B2890927071A4DB454B9206DFAAD3FB99865123E8EC570CE582D88229034563040F1FC3C33FC4C282041B5ABAC35992C1635ED2C2A9516BF8ECE9A8F72ECB4C646212CFA8DC6A4A240D7843C959D6F1D6AF78D036F036244DFE3A6607B13619BA6A017287C4A117ABA3B258AE1DD4FDE54B6F9A467A17F8E3587333117C1833D81F321E8A715EAFD9B14C6A99415E7796D68ACA2F738F87BD55B5B342981AF05264C3FE83F2743D10C850BB08C95EEB9E73846E46626388E5D03867687ADE923C779CADBE9238F1AF8F2FC0CEDEF63D1A3E705C642519EDD9FBC3AF22AC6A16EFDC539486519C47B8869233BAA87F38B24ADC0885EA469A1BBF96A50A7A679169315AAD7AE3AFB4826F35246E51C2AD7688052A5B46104D2AB6FFF30FFB25AA30FDC52FC38A791445C0880F77E4EE4566B91D9C93E91A624DFBA286DA2746012FA859A6B0B0A5F3069ECEDD12E540AEA7739A0C7A04D2079FF6F45A623DA925C8F6F1967A1C23C6013BFDE326D03AF5ED7CC2D9A89D';
    $dom = new \Slothsoft\Core\DOMHelper();
    $dataXPath = new \DOMXPath($dataDoc);
    
    $options = [];
    $options['method'] = 'POST';
    $options['header'] = $header;
    
    if ($data = \Slothsoft\Core\Storage::loadExternalJSON($url, Seconds::HOUR, '', $options)) {
        $parentNode = $dataDoc->createElement('player');
        $parentNode->setAttribute('number', $no);
        $parentNode->setAttribute('name', $name);
        $retFragment->appendChild($parentNode);
        // my_dump($data);
        if ($data = $data['Data']) {
            $xml = '';
            foreach ($data as $row) {
                $xml .= sprintf('<object id="%s">%s</object>', $row['Key'], $row['Value']);
                /*
                 * $node = $dataDoc->createElement($row['Key']);
                 * $xml = $row['Value'];
                 * $xml = utf8_encode($xml);
                 * //$xml = str_replace('&nbsp;', ' ', $xml);
                 * $xml = sprintf('<div xmlns="%s">%s</div>', \Slothsoft\Core\DOMHelper::NS_HTML, $xml);
                 * $node->appendChild($dom->parse($xml, $dataDoc, true));
                 * $retFragment->appendChild($node);
                 * //
                 */
            }
            
            // echo $xml . PHP_EOL;
            // my_dump($dom->parse($xml, $dataDoc, true));
            $dataFragment = $dom->parse($xml, $dataDoc, true);
            
            $queryList = [];
            $queryList['id'] = 'string(.//@data-summarykey)';
            $queryList['date'] = 'string(.//*[@class="HistoryPanelHeaderLabel Date"])';
            $queryList['description'] = 'string(.//*[@class="HistoryPanelHeaderLabel Description"])';
            $queryList['location'] = 'string(.//*[@class="HistoryPanelHeaderLabel Location"])';
            $queryList['points'] = 'string(.//*[@class="HistoryPanelHeaderLabel LifetimePoints"])';
            
            $nodeList = $dataXPath->evaluate('.//*[@class="HistoryPanelRow"]', $dataFragment);
            foreach ($nodeList as $node) {
                $event = [];
                foreach ($queryList as $key => $query) {
                    $event[$key] = $dataXPath->evaluate($query, $node);
                }
                
                if ($event['id'] = (int) $event['id']) {
                    $url = $host . $eventURI . $event['id'];
                    // *
                    if ($data = \Slothsoft\Core\Storage::loadExternalJSON($url, Seconds::HOUR, '', $options)) {
                        if (! $data['Result']) {
                            \Slothsoft\Core\Storage::clearExternalDocument($url, Seconds::HOUR, '', $options);
                            $data = \Slothsoft\Core\Storage::loadExternalJSON($url, Seconds::HOUR, '', $options);
                        }
                    }
                    my_dump($data);
                    // */
                    $node = $dataDoc->createElement('event');
                    foreach ($event as $key => $val) {
                        $node->setAttribute($key, $val);
                    }
                    $parentNode->appendChild($node);
                }
            }
        }
    } else {
        my_dump(\Slothsoft\Core\Storage::loadExternalFile($pointsURI, 0, '', $options));
    }
}

return $retFragment;