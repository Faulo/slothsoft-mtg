<?php
$doc = $this->getResourceDoc('/mtg/bill', 'xml');

$lineNodeList = $doc->getElementsByTagName('line');
foreach ($lineNodeList as $lineNode) {
    $lineNode->setAttribute('year', date('Y', strtotime($lineNode->getAttribute('date'))));
}

$xpath = \Slothsoft\Core\DOMHelper::loadXPath($doc);

$attrList = [];
$attrList[] = 'type';
$attrList[] = 'shop';

$groupList = [];
foreach ($attrList as $attr) {
    $groupList[$attr] = [];
    $nodeList = $xpath->evaluate(sprintf('//line[@%s]', $attr));
    foreach ($nodeList as $node) {
        $key = $node->getAttribute($attr);
        if (! isset($groupList[$attr][$key])) {
            $groupList[$attr][$key] = [
                0,
                0,
                0
            ];
        }
    }
}

foreach ($groupList as $attr => &$group) {
    foreach ($group as $key => &$record) {
        $nodeList = $xpath->evaluate(sprintf('//line[@%s = "%s"]', $attr, $key));
        foreach ($nodeList as $node) {
            if ($val = $node->getAttribute('record')) {
                $val = explode('-', $val);
                foreach ($val as $i => $v) {
                    $record[$i] += (int) $v;
                }
            }
        }
    }
    unset($record);
}
unset($group);

foreach ($groupList as $attr => $group) {
    foreach ($group as $key => $record) {
        $node = $doc->createElement($attr);
        $node->setAttribute('name', $key);
        if (array_sum($record)) {
            $node->setAttribute('record', implode('-', $record));
        }
        $doc->documentElement->appendChild($node);
    }
}

return $doc;