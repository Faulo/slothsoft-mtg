<?php
$host = 'https://accounts.wizards.com';
$url = '/Widget/SamlWidget';

$data = [];
if ($xpath = \Slothsoft\Core\Storage::loadExternalXPath($host . $url, TIME_DAY)) {
    $url = $xpath->evaluate('normalize-space(//script/@src)');
    $data[] = \Slothsoft\Core\Storage::loadExternalFile($host . $url, TIME_DAY);
}

$js = implode(PHP_EOL, $data);

return \Slothsoft\Farah\HTTPFile::createfromString($js, 'samlScript.js');