<?php
$host = 'https://accounts.wizards.com';
$url = '/SamlWidget/flags.json';

$data = [];
$data[] = \Slothsoft\Core\Storage::loadExternalFile($host . $url, Seconds::DAY);

$js = implode(PHP_EOL, $data);

return \Slothsoft\Farah\HTTPFile::createfromString($js, 'samlFlags.js');