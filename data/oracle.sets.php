<?php
$oracle = new \Slothsoft\MTG\Oracle('mtg', $dataDoc);

return \Slothsoft\CMS\HTTPFile::createFromString(implode(PHP_EOL, $oracle->getOracleSetList(true)));