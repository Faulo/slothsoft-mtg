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
    
    /**
     * @return array<string>|null
     */
    public function getNameListBySetName(string $setName): ?array {
        return $this->dbmsTable->select('name', [
            'expansion_name' => $setName
        ], 'ORDER BY expansion_index');
    }
    
    /**
     * @return array<int|string>|null
     */
    public function getIdListByName(string $name): ?array {
        return $this->dbmsTable->select('id', [
            'name' => $name
        ]);
    }
    
    /**
     * @return array<array<string, mixed>>|null
     */
    public function getCardList(): ?array {
        return $this->dbmsTable->select(true, '', 'ORDER BY expansion_abbr, expansion_index');
    }
    
    /**
     * @return array<array<string, mixed>>|null
     */
    public function getCardListBySetName(string $setName): ?array {
        return $this->dbmsTable->select(true, [
            'expansion_name' => $setName
        ], 'ORDER BY expansion_index');
    }
    
    /**
     * @return array<array<string, mixed>>|null
     */
    public function getCardListBySetAbbr(string $setAbbr): ?array {
        return $this->dbmsTable->select(true, [
            'expansion_abbr' => $setAbbr
        ], 'ORDER BY expansion_index');
    }
    
    /**
     * @param array<array-key, string> $nameList
     * @return array<array<string, mixed>>|null
     */
    public function getCardListByNameList(array $nameList): ?array {
        return $this->dbmsTable->select(true, [
            'name' => $nameList
        ]);
    }
    
    /**
     * @param array<string, mixed> $data
     */
    public function createRow(array $data): ?bool {
        try {
            $ret = (bool) $this->dbmsTable->insert($data, $data);
        } catch (Exception) {
            $ret = null;
        }
        return $ret;
    }
    
    /**
     * @param array<string, mixed> $data
     */
    public function updateRowByName(array $data, string $name): ?bool {
        try {
            $ids = $this->getIdListByName($name);
            $ret = $ids === null ? null : (bool) $this->dbmsTable->update($data, $ids);
        } catch (Exception) {
            $ret = null;
        }
        return $ret;
    }
    
    public function deleteCardsBySetAbbr(string $setAbbr): int|string|null {
        $ids = $this->dbmsTable->select('id', [
            'expansion_abbr' => $setAbbr
        ]);
        return $ids === null ? null : $this->dbmsTable->delete($ids);
    }
}