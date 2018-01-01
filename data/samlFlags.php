<?php
$host = 'https://accounts.wizards.com';
$url = '/SamlWidget/flags.json';

$data = [];
$data[] = \Slothsoft\Core\Storage::loadExternalFile($host . $url, TIME_DAY);

$js = implode(PHP_EOL, $data);

return \Slothsoft\CMS\HTTPFile::createfromString($js, 'samlFlags.js');