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
        'mode' => 'index_custom',
        'setDir' => realpath(__DIR__ . '/../res/custom-sets'),
        'imageDir' => realpath(__DIR__ . '/../res/custom-cards')
    ];
    
    /*
     * $optionsList[] = [
     * 'mode' => 'index_tokens',
     * 'urlList' => [
     * //'http://magiccards.info/extras.html',
     * 'http://mtg.onl/token-list/data/ProxyTokens.json', //http://mtg.onl/token-list/tokens/Angel_B_3_3.jpg
     * ],
     * ];
     * //
     */
    
    $optionsList[] = [
        'mode' => 'index_spoiler',
        'setList' => [            /*
         * [
         * 'name' => 'Eldritch Moon',
         * 'url' => 'http://mythicspoiler.com/emn/',
         * ],
         * [
         * 'name' => 'Shadows over Innistrad',
         * 'url' => 'http://mythicspoiler.com/soi/',
         * ],
         * //
         */
            /*
         * [
         * 'name' => 'Oath of the Gatewatch',
         * 'url' => 'http://mythicspoiler.com/ogw/',
         * ],
         * //
         */
            /*
         * [
         * 'name' => 'Commander 2015',
         * 'url' => 'http://mythicspoiler.com/c15/',
         * ],
         * //
         */
        ]
    ];
    
    $optionsList[] = [
        'mode' => 'index_gatherer'
    ];
    
    $optionsList[] = [
        'mode' => 'custom_import'
    ];
    
    $optionsList[] = [
        'mode' => 'index_prices'
    ];
    
    $optionsList[] = [
        'mode' => 'index_convert'
    ];
    
    $optionsList[] = [
        'mode' => 'index_images',
        'imageDir' => realpath(__DIR__ . '/../res/images')
    ];
    
    $optionsList[] = [
        'mode' => 'end'
    ];
    
    $stream = new \Slothsoft\MTG\OracleWorkStream($oracle);
    $stream->initOptionsList($optionsList);
    
    return $stream;
});