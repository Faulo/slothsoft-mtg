<?php
$url = 'http://www.wizards.com/Magic/PlaneswalkerPoints/';
$url = 'https://accounts.wizards.com/Orchestration/Esso/Saml';
$data = [];
$data['username'] = '2112587694';
$data['password'] = '';
$data['submit'] = 'C002';

$data = new DOMDocument();
$data->load('<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/"><s:Body><GetIdentityVersion xmlns="http://www.wizards.com/Service/2013-03"><requestMessage xmlns:i="http://www.w3.org/2001/XMLSchema-instance">&lt;samlp:AttributeQuery xmlns:samlp = "urn:oasis:names:tc:SAML:2.0:protocol" ID="_fee74ff9-608b-1291-0833-cda2425cb63e" IssueInstant="2015-01-07T16:32:05Z" Version="2.0"&gt;&lt;samlp:Issuer xmlns:samlp ="urn:oasis:names:tc:SAML:2.0:assertion"/&gt;&lt;saml:Subject xmlns:saml = "urn:oasis:names:tc:SAML:2.0:assertion"&gt;&lt;saml:NameID&gt; 2112587694 &lt;/saml:NameID&gt;&lt;/saml:Subject&gt; &lt;saml:Attribute xmlns:saml = "urn:oasis:names:tc:SAML:2.0:assertion" Name = "UserName"/&gt;&lt;/samlp:AttributeQuery&gt;</requestMessage></GetIdentityVersion></s:Body></s:Envelope>');

if ($xpath = \Slothsoft\Core\Storage::loadExternalXPath($url, 0, $data)) {
    return ($xpath->document);
}