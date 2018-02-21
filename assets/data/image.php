<?php
$card = [];
$card['color'] = $this->httpRequest->getInputValue('color');

$path = \Slothsoft\MTG\OracleInfo::getColorPath($card);
if ($path = realpath($path)) {
    return \Slothsoft\Farah\HTTPFile::createFromPath($path);
}