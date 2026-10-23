<?php
declare(strict_types = 1);
namespace Slothsoft\MTG;

use Exception;

class OracleCustomTable extends OracleTable {
    
    protected function install(): void {
        /*
         * $mapping = [];
         * $mapping['oracle_id'] = 'ID';
         * $mapping['name'] = 'NAME';
         * $mapping['type'] = 'TYPE';
         * $mapping['cost'] = 'COST';
         * $mapping['rarity'] = 'RARITY';
         * $mapping['expansion_name'] = 'SET';
         * $mapping['expansion_number'] = 'COLLNUM';
         * $mapping['expansion_index'] = '_index';
         * $mapping['description'] = 'ORACLE';
         * //
         */
        $sqlCols = [
            'id' => 'bigint UNSIGNED NOT NULL AUTO_INCREMENT',
            'name' => 'varchar(150) NOT NULL',
            'type' => 'varchar(150) NOT NULL',
            'cost' => 'varchar(50) NOT NULL',
            'rarity' => 'varchar(50) NOT NULL',
            'expansion_name' => 'varchar(150) NOT NULL',
            'expansion_abbr' => 'varchar(15) NOT NULL',
            'expansion_number' => 'varchar(50) NOT NULL',
            'expansion_index' => 'int UNSIGNED NOT NULL',
            'description' => 'text NOT NULL',
            'legality' => 'text NOT NULL'
        ];
        $sqlKeys = [
            'id',
            [
                'type' => 'UNIQUE KEY',
                'columns' => [
                    'name'
                ]
            ]
        ];
        $this->dbmsTable->createTable($sqlCols, $sqlKeys);
    }
    
    public function getNameListBySetName($setName): ?array {
        return $this->dbmsTable->select('name', [
            'expansion_name' => $setName
        ], 'ORDER BY expansion_index');
    }
    
    public function getIdListByName($name): ?array {
        return $this->dbmsTable->select('id', [
            'name' => $name
        ]);
    }
    
    public function getCardList(): ?array {
        return $this->dbmsTable->select(true, null, 'ORDER BY expansion_abbr, expansion_index');
    }
    
    public function getCardListBySetName($setName): ?array {
        return $this->dbmsTable->select(true, [
            'expansion_name' => $setName
        ], 'ORDER BY expansion_index');
    }
    
    public function getCardListBySetAbbr($setAbbr): ?array {
        return $this->dbmsTable->select(true, [
            'expansion_abbr' => $setAbbr
        ], 'ORDER BY expansion_index');
    }
    
    public function getCardListByNameList(array $nameList): ?array {
        return $this->dbmsTable->select(true, [
            'name' => $nameList
        ]);
    }
    
    public function createRow(array $data): ?bool {
        try {
            $ret = (bool) $this->dbmsTable->insert($data, $data);
        } catch (Exception) {
            $ret = null;
        }
        return $ret;
    }
    
    public function updateRowByName(array $data, $name): ?bool {
        try {
            $ret = (bool) $this->dbmsTable->update($data, $this->getIdListByName($name));
        } catch (Exception) {
            $ret = null;
        }
        return $ret;
    }
    
    public function deleteCardsBySetAbbr($setAbbr): int|string|null {
        return $this->dbmsTable->delete($this->dbmsTable->select('id', [
            'expansion_abbr' => $setAbbr
        ]));
    }
}