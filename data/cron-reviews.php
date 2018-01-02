<?php
namespace Slothsoft\Farah;

return new HTTPClosure([
    'isThreaded' => true
], function () use ($dataDoc) {
    $oracle = new \Slothsoft\MTG\Oracle('mtg', $dataDoc);
    
    $optionsList = [];
    
    $optionsList[] = [
        'mode' => 'start'
    ];
    
    $optionsList[] = [
        'mode' => 'review_cfb',
        'reviewDir' => realpath(__DIR__ . '/../res/reviews'),
        'reviewName' => 'ChannelFireball',
        'setList' => [
            'Kaladesh' => 'http://www.channelfireball.com/tag/kaladesh-set-review/'
        ]
    ];
    
    $optionsList[] = [
        'mode' => 'end'
    ];
    
    $stream = new \Slothsoft\MTG\OracleWorkStream($oracle);
    $stream->initOptionsList($optionsList);
    
    return $stream;
});