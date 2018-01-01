<?php
namespace Slothsoft\MTG;

$oracle = new Oracle('mtg', $dataDoc);

$idList = [];
$idList[] = 426913;

foreach ($idList as $id) {
    $card = [];
    $card['oracle_id'] = $id;
    $data = OracleInfo::getOracleCardData($card);
    
    my_dump($data);
}

/*
$idTable = $oracle->getIdTable();

$modernList = OracleInfo::getModernLegalList();

$setList = $idTable->getSetList();

foreach ($modernList as $set) {
	if (!in_array($set, $setList)) {
		echo $set . PHP_EOL;
	}
}
//*/