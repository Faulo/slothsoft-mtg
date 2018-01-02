<?php
$oracle = new \Slothsoft\MTG\Oracle('mtg', $dataDoc);

return \Slothsoft\Farah\HTTPFile::createFromString(implode(PHP_EOL, $oracle->getOracleSetList(true)));