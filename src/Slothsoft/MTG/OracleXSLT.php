<?php
declare(strict_types = 1);
namespace Slothsoft\MTG;

use DOMDocument;

class OracleXSLT {

    public static function cardsByExpansion($expansion) {
        $query = [
            'expansion_name' => $expansion
        ];

        $dataDoc = new DOMDocument();
        $oracle = new Oracle('mtg', $dataDoc);

        $retNode = $dataDoc->createElement('oracle');

        $retNode->appendChild($oracle->createCategoriesElement($dataDoc));
        $retNode->appendChild($oracle->createSearchElement($dataDoc, $query));

        $dataDoc->appendChild($retNode);

        return $dataDoc;
    }
}