<?php
declare(strict_types = 1);

namespace Slothsoft\MTG;

use Exception;

class OracleXMLTable extends OracleTable {
    
    protected function install(): void {
        $sqlCols = [
            'id' => 'bigint UNSIGNED NOT NULL AUTO_INCREMENT',
            'name' => 'varchar(150) NOT NULL',
            'xml' => 'text NOT NULL'
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
    
    private function buildNameQuery(string $name): string {
        $sqlList = [];
        foreach (explode(' ', $name) as $part) {
            $part = trim($part);
            if (strlen($part)) {
                $sqlList[] = sprintf('name LIKE "%s%s%s"', '%', $this->dbmsTable->escape($part), '%');
            }
        }
        return implode(' AND ', $sqlList);
    }
    
    /**
     * @return array<array-key, string>
     */
    public function searchCardByName(string $name): array {
        $ret = [];
        if (strlen($name)) {
            $sqlList = $this->buildNameQuery($name);
            $resList = $this->dbmsTable->select([
                'name',
                'xml'
            ], $sqlList);
            foreach ($resList ?? [] as $res) {
                $ret[$res['name']] = $res['xml'];
            }
        }
        return $ret;
    }
    
    /**
     * @return array<string>|null
     */
    public function searchNameByName(string $name): ?array {
        $ret = [];
        if (strlen($name)) {
            $sqlList = $this->buildNameQuery($name);
            $ret = $this->dbmsTable->select('name', $sqlList);
        }
        return $ret;
    }
    
    /**
     * @param array<array-key, string> $nameList
     * @return array<array-key, string>
     */
    public function getCardListByNameList(array $nameList): array {
        $ret = [];
        $resList = $this->dbmsTable->select([
            'name',
            'xml'
        ], [
            'name' => $nameList
        ]);
        foreach ($resList ?? [] as $res) {
            $ret[$res['name']] = $res['xml'];
        }
        return $ret;
    }
    
    /**
     * @param array<array-key, string> $nameList
     * @return array<string>|null
     */
    public function getXMLListByNameList(array $nameList): ?array {
        return $this->dbmsTable->select('xml', [
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
    
    public function createCard(string $name, string $xml): ?bool {
        $data = [];
        $data['name'] = $name;
        $data['xml'] = $xml;
        return $this->createRow($data);
    }
}