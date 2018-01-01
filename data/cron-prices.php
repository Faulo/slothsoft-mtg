<?php
namespace Slothsoft\CMS;

return new HTTPClosure([
    'isThreaded' => true
], function () use ($dataDoc) {
    $oracle = new \Slothsoft\MTG\Oracle('mtg', $dataDoc);
    
    $optionsList = [];
    
    $optionsList[] = [
        'mode' => 'start'
    ];
    
    $optionsList[] = [
        'mode' => 'index_prices'
    ];
    
    $optionsList[] = [
        'mode' => 'end'
    ];
    
    $stream = new \Slothsoft\MTG\OracleWorkStream($oracle);
    $stream->initOptionsList($optionsList);
    
    return $stream;
});