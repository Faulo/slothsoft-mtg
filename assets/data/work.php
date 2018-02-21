<?php
$oracle = new \Slothsoft\MTG\Oracle('mtg', $dataDoc);

$optionsList = [];
$optionsList[] = [
    'mode' => 'index_custom',
    'setDir' => realpath(__DIR__ . '/../res/custom-sets'),
    'imageDir' => realpath(__DIR__ . '/../res/custom-cards')
];
/*
 * $optionsList[] = [
 * 'mode' => 'oracle_set',
 * 'setName' => 'Shadows over Innistrad',
 * ];
 * //
 */
$optionsList[] = [
    'mode' => 'index_images',
    'imageDir' => realpath(__DIR__ . '/../res/images')
];

$stream = new \Slothsoft\MTG\OracleWorkStream($oracle);
$stream->initOptionsList($optionsList);

return $stream;