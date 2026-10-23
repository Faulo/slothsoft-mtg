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
    
    public function searchCardByName($name): array {
        $ret = [];
        if (strlen($name)) {
            $name = explode(' ', $name);
            $sqlList = [];
            foreach ($name as $n) {
                // $n = str_replace(' & ', ' // ', $n);
                $n = trim($n);
                if (strlen($n)) {
                    $sqlList[] = sprintf('name LIKE "%s%s%s"', '%', $this->dbmsTable->escape($n), '%');
                }
            }
            $sqlList = implode(' AND ', $sqlList);
            $resList = $this->dbmsTable->select([
                'name',
                'xml'
            ], $sqlList);
            foreach ($resList as $res) {
                $ret[$res['name']] = $res['xml'];
            }
        }
        return $ret;
    }
    
    public function searchNameByName($name): ?array {
        $ret = [];
        if (strlen($name)) {
            $name = explode(' ', $name);
            $sqlList = [];
            foreach ($name as $n) {
                // $n = str_replace(' & ', ' // ', $n);
                $n = trim($n);
                if (strlen($n)) {
                    $sqlList[] = sprintf('name LIKE "%s%s%s"', '%', $this->dbmsTable->escape($n), '%');
                }
            }
            $sqlList = implode(' AND ', $sqlList);
            $ret = $this->dbmsTable->select('name', $sqlList);
        }
        return $ret;
    }
    
    public function getCardListByNameList(array $nameList): array {
        $ret = [];
        $resList = $this->dbmsTable->select([
            'name',
            'xml'
        ], [
            'name' => $nameList
        ]);
        foreach ($resList as $res) {
            $ret[$res['name']] = $res['xml'];
        }
        return $ret;
    }
    
    public function getXMLListByNameList(array $nameList): ?array {
        return $this->dbmsTable->select('xml', [
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
    
    public function createCard($name, $xml): ?bool {
        $data = [];
        $data['name'] = $name;
        $data['xml'] = $xml;
        return $this->createRow($data);
    }
}