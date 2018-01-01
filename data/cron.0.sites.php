<?php
namespace Slothsoft\CMS;

return new HTTPClosure([
    'isThreaded' => true
], function () use ($dataDoc) {
    $oracle = new \Slothsoft\MTG\Oracle('mtg', $dataDoc);
    
    // $idTable = $oracle->getIdTable();
    // $xmlTable = $oracle->getXMLTable();
    
    $resourcePath = '/mtg/sites.players';
    
    $retFragment = $dataDoc->createDocumentFragment();
    
    $resDir = $this->getResourceDir('/mtg/players');
    $templateDoc = $this->getTemplateDoc('/mtg/sites');
    $dom = new \Slothsoft\Core\DOMHelper();
    
    foreach ($resDir as $key => $doc) {
        $playerFile = $doc->documentElement->getAttribute('realpath');
        $player = $oracle->getPlayer($playerFile);
        $playerDoc = $player->asNode();
        
        // return \Slothsoft\CMS\HTTPFile::createFromDocument($playerDoc);
        
        $sitesNode = $dom->transformToFragment($playerDoc, $templateDoc, $dataDoc);
        $retFragment->appendChild($sitesNode);
    }
    
    $dataDoc->documentElement->appendChild($retFragment);
    
    $res = $this->setResourceDoc($resourcePath, $dataDoc->documentElement);
    
    $ret = sprintf('Saved %d bytes to %s!', $res, $this->getResourcePath($resourcePath));
    
    return \Slothsoft\CMS\HTTPFile::createFromString($ret);
    
    // ;
    
    // return $retFragment;
});