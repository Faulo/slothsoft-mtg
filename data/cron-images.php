<?php
namespace Slothsoft\CMS;

return new HTTPClosure([
    'isThreaded' => true
], function () use ($dataDoc) {
    $oracle = new \Slothsoft\MTG\Oracle('mtg', $dataDoc);
    $idTable = $oracle->getIdTable();
    
    $optionsList = [];
    
    $optionsList[] = [
        'mode' => 'start'
    ];
    
    $optionsList[] = [
        'mode' => 'index_images',
        // 'setList' => $idTable->getCustomSetAbbrList(),
        'imageDir' => realpath(__DIR__ . '/../res/images')
    ];
    
    $optionsList[] = [
        'mode' => 'end'
    ];
    
    $stream = new \Slothsoft\MTG\OracleWorkStream($oracle);
    $stream->initOptionsList($optionsList);
    
    return $stream;
});