<?php
namespace Slothsoft\CMS;

use Slothsoft\MTG\Oracle;
use DOMDocument;
$retDoc = new DOMDocument();
$oracle = new Oracle('mtg', $retDoc);

$retNode = $retDoc->createElement('oracle');
$retNode->appendChild($oracle->createCategoriesElement());
$retDoc->appendChild($retNode);

$this->setResourceDoc('mtg/oracle', $retNode);

return HTTPFile::createFromDocument($retDoc, 'oracle.xml');