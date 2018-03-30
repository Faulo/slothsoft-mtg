<?php
$xml = file_get_contents(__FILE__ . '.xml');

$doc = new \DOMDocument();
$doc->loadXML($xml);

$url = 'https://accounts.wizards.com/Orchestration/Esso/Saml';

$options = [];
$options['method'] = 'POST';
$options['header'] = [];
$options['header']['content-type'] = 'text/xml; charset=UTF-8';
$options['header']['Referer'] = 'https://accounts.wizards.com/Widget/SamlWidget';
$options['header']['SOAPAction'] = 'http://www.wizards.com/Service/2013-03/ISamlEndpoint/ExecuteAuthnRequest';

$ret = null;

$dom = new \Slothsoft\Core\DOMHelper();

if ($xpath = \Slothsoft\Core\Storage::loadExternalXPath($url, 0, $doc, $options)) {
    $xml = $xpath->evaluate('string(//*[name()="ExecuteAuthnRequestResult"])');
    // die($ret);
    $doc = new \DOMDocument();
    $doc->loadXML($xml);
    
    $xpath = new \DOMXPath($doc);
    if ($token = $xpath->evaluate('string(//*[@Name="SamlToken"])')) {
        $token = str_replace('+', ' ', $token);
        $param = json_decode('{"Parameters":{"token":"6tFbq4NmcX7vvh7IPDgNcbQZHvipXqPo0mENVrI bl4PCcrOWu9fZE9OBmDv7KobVcCHGtVCvSIYI6clhkoe77jUAdTiEwlrTb6eoXrvhho7ght365TTd2PzfP5lKinxGyw 6oHNcQYaTuntgUVs Sd5bTvUzkE/qXVXj6aW/a2ll/WcA8VyY4fqKz1ym4eTHvWQ9Orh3DeZU33/s0MKH2HZUsyLTtRXY2M0je7ZlKcBKcWSBoWg3sMck9yFn9LxM3WZXF610SEk6JwxIZBDIpfYpFgC2d PvbKJfWaLNoxF7pvlFQMXP dvanhxjWKMq1AIjXNS ctXmQkgpPuraGFHeWCzLTxTMRqRHXBdOKzA9 Jp/LJtEEJyMUuDNuw2hVeLt21xMusmC1NH3mcjklXxkEARk3x0j8zrEvJxR L71fbMFryXm6vhkxr0TqRKlN6Vrz8O /VWC8pRVBi8Z8sQWtxUXkgQuJy bXDckKZhfk3UkNwe4t2R3w8tcuCpTMELJlGfnuUOBc83rklkJLRoY32uNTQEOURwV9it3YWGFdLbbkS8PIynsKG15vHoJ/uylTW8 /llJ6DhZ0ciwGX2ajm98Va0eAIUYxVvS5/CUb17MjQ Y KzNxRFncoN","redirectUrl":null,"preReqCheck":false}}', true);
        // my_dump($param);
        $param['Parameters']['token'] = $token;
        // my_dump($param);
        
        $url = 'http://www.wizards.com/Magic/PlaneswalkerPoints/Login/Login';
        
        $options = [];
        $options['method'] = 'POST';
        $options['header'] = [];
        $options['header']['content-type'] = 'application/json';
        $options['header']['Referer'] = 'http://www.wizards.com/Magic/PlaneswalkerPoints/';
        
        $param = json_encode($param);
        if ($data = \Slothsoft\Core\Storage::loadExternalFile($url, 0, $param, $options)) {}
        my_dump($data);
    }
}

return $ret;