<?php
declare(strict_types = 1);

namespace Slothsoft\MTG;

use Exception;
use RuntimeException;

class OracleIdTable extends OracleTable {
    
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
            'oracle_id' => 'bigint NOT NULL',
            'name' => 'varchar(150) NOT NULL',
            'type' => 'varchar(150) NOT NULL',
            'cost' => 'varchar(50) NOT NULL',
            'cmc' => ' tinyint(3) unsigned NOT NULL',
            'colors' => ' tinyint(3) unsigned NOT NULL',
            'rarity' => 'varchar(50) NOT NULL',
            'expansion_name' => 'varchar(150) NOT NULL',
            'expansion_abbr' => 'varchar(50) NOT NULL',
            'expansion_number' => 'varchar(50) NOT NULL',
            'expansion_index' => 'int UNSIGNED NOT NULL',
            'description' => 'text NOT NULL',
            'flavor' => 'text NOT NULL',
            'legality' => 'text NOT NULL',
            'price' => 'float NOT NULL',
            'image' => 'text NOT NULL'
        ];
        $sqlKeys = [
            'id',
            [
                'type' => 'UNIQUE KEY',
                'columns' => [
                    'oracle_id'
                ]
            ],
            'name',
            'type',
            'cost',
            'rarity',
            'expansion_name',
            'expansion_abbr',
            'expansion_number',
            'expansion_index'
        ];
        $this->dbmsTable->createTable($sqlCols, $sqlKeys);
    }
    
    /**
     * @param array<array-key, array<string, mixed>> $dataList
     * @return array<array-key, array<string, mixed>>
     */
    protected function castList(array &$dataList): array {
        foreach ($dataList as $key => $data) {
            $dataList[$key] = $this->cast($data);
        }
        return $dataList;
    }
    
    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function cast(array $data): array {
        return $data;
    }
    
    /**
     * @param array<string, string> $query
     * @return array<string>|null
     */
    public function searchNameByCard(array $query): ?array {
        $ret = [];
        $sqlList = [];
        foreach ($query as $key => $queryValue) {
            if (strlen($queryValue)) {
                if ($queryValue[0] === '!') {
                    $queryValue = substr($queryValue, 1);
                    $not = true;
                } else {
                    $not = false;
                }
                $sql = [];
                $valList = explode('|', $queryValue);
                foreach ($valList as $val) {
                    $val = trim($val);
                    if (strlen($val)) {
                        $s = [];
                        $val = explode(' ', $val);
                        foreach ($val as $i => $n) {
                            // $n = str_replace(' & ', ' // ', $n);
                            $n = trim($n);
                            if (strlen($n)) {
                                switch ($key) {
                                    case 'expansion_name':
                                        $v = implode(' ', $val);
                                        if (preg_match('/[A-Z]/', $v)) {
                                            if ($i === 0) {
                                                $s[] = sprintf('%s = "%s"', $key, $this->dbmsTable->escape($v));
                                            }
                                        } else {
                                            $s[] = sprintf('%s LIKE "%s%s%s"', $key, '%', $this->dbmsTable->escape($n), '%');
                                        }
                                        break;
                                    case 'expansion_number':
                                    case 'expansion_abbr':
                                        $s[] = sprintf('%s = "%s"', $key, $this->dbmsTable->escape($n));
                                        break;
                                    case 'rarity':
                                        $s[] = sprintf('%s LIKE "%s%s"', $key, $this->dbmsTable->escape($n), '%');
                                        break;
                                    case 'price_gt':
                                        $s[] = sprintf('%s > %f', 'price', $n);
                                        break;
                                    default:
                                        $s[] = sprintf('%s LIKE "%s%s%s"', $key, '%', $this->dbmsTable->escape($n), '%');
                                        break;
                                }
                            }
                        }
                        if ($s) {
                            $s = implode(' AND ', $s);
                            $sql[] = sprintf('(%s)', $s);
                        }
                    }
                }
                if ($sql) {
                    $sql = implode(' OR ', $sql);
                    $sqlList[] = $not ? sprintf('NOT(%s)', $sql) : sprintf('(%s)', $sql);
                }
            }
        }
        if ($sqlList) {
            $sql = implode(' AND ', $sqlList);
            // echo $sql;
            $ret = $this->dbmsTable->select('DISTINCT name', $sql);
        }
        return $ret;
    }
    
    public function getMissingOracleId(): int {
        $ret = 1;
        $idList = $this->dbmsTable->select('oracle_id', '', 'ORDER BY oracle_id ASC')
            ?? throw new RuntimeException('Could not read Oracle IDs.');
        array_unshift($idList, 0);
        foreach ($idList as $i => $id) {
            if ($i !== (int) $id) {
                $ret = $i;
                break;
            }
        }
        return $ret;
    }
    
    public function getLastOracleId(): int|string|false {
        $idList = $this->dbmsTable->select('oracle_id', '1 ORDER BY oracle_id DESC LIMIT 1');
        return current($idList ?? []);
    }
    
    /**
     * @return array<int|string>|null
     */
    public function getOracleIdList(): ?array {
        return $this->dbmsTable->select('oracle_id');
    }
    
    /**
     * @return array<string>|null
     */
    public function getExpansionList(): ?array {
        return $this->dbmsTable->select('DISTINCT expansion_name');
    }
    
    /**
     * @return array<string>|null
     */
    public function getSetAbbrList(): ?array {
        return $this->dbmsTable->select('DISTINCT expansion_abbr');
    }
    
    /**
     * @return array<string>|null
     */
    public function getCustomSetAbbrList(): ?array {
        return $this->dbmsTable->select('DISTINCT expansion_abbr', 'expansion_abbr LIKE "custom-%"');
    }
    
    /**
     * @return array<array-key, string>
     */
    public function getSetList(): array {
        $ret = [];
        // SELECT expansion_name, expansion_abbr, count(id) cards FROM `oracle-ids` group by expansion_abbr ORDER BY cards DESC
        // $res = $this->dbmsTable->select(['DISTINCT expansion_abbr', 'expansion_name']);
        $res = $this->dbmsTable->select([
            'expansion_abbr',
            'expansion_name',
            'count(id) cards'
        ], '', 'GROUP BY expansion_abbr ORDER BY cards DESC');
        foreach ($res ?? [] as $row) {
            $ret[$row['expansion_abbr']] = $row['expansion_name'];
        }
        return $ret;
    }
    
    /**
     * @param array<array-key, int|string> $idList
     * @return array<array-key, int|string>
     */
    public function getMissingOracleIdList(array $idList): array {
        $existingIds = $this->dbmsTable->select('oracle_id', ['oracle_id' => $idList])
            ?? throw new RuntimeException('Could not read Oracle IDs.');
        return array_diff($idList, $existingIds);
    }
    
    /**
     * @param array<array-key, int|string> $idList
     * @return array<string>|null
     */
    public function getNameListByOracleIdList(array $idList): ?array {
        return $this->dbmsTable->select('name', [
            'oracle_id' => $idList
        ]);
    }
    
    public function getNameByOracleId(int|string $id): string|false {
        $nameList = $this->dbmsTable->select('name', [
            'oracle_id' => $id
        ]);
        return current($nameList ?? []);
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
     * @return array<string, mixed>|null
     */
    public function getCardByName(string $name): ?array {
        $ret = $this->dbmsTable->select(true, [
            'name' => $name
        ], 'ORDER BY oracle_id DESC');
        return $ret ? reset($ret) : null;
    }
    
    /**
     * @return array<array<string, mixed>>|null
     */
    public function getCardListByName(string $name): ?array {
        return $this->dbmsTable->select(true, [
            'name' => $name
        ]);
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
     * @return list<array<string, mixed>>
     */
    public function getUniqueCardList(): array {
        $retList = [];
        $nameList = $this->dbmsTable->select('DISTINCT name');
        foreach ($nameList ?? [] as $name) {
            if ($resList = $this->dbmsTable->select(true, [
                'name' => $name
            ], 'ORDER BY oracle_id DESC')) {
                $card = reset($resList);
                $card['expansion_list'] = [];
                $card['legality_list'] = [];
                foreach ($resList as $res) {
                    $card['expansion_list'][$res['oracle_id']] = [
                        'name' => $res['expansion_name'],
                        'abbr' => $res['expansion_abbr'],
                        'number' => $res['expansion_number'],
                        'index' => $res['expansion_index']
                    ];
                    if (strlen($res['legality'])) {
                        $card['legality_list'] += array_flip(explode(PHP_EOL, $res['legality']));
                    }
                }
                $card['legality_list'] = array_keys($card['legality_list']);
                sort($card['legality_list']);
                $card['legality'] = implode(PHP_EOL, $card['legality_list']);
                $retList[] = $card;
            }
        }
        return $retList;
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
    
    /**
     * @param array<string, mixed> $data
     */
    public function updateRowById(array $data, int|string $id): ?bool {
        try {
            $ret = (bool) $this->dbmsTable->update($data, $id);
        } catch (Exception) {
            $ret = null;
        }
        return $ret;
    }
    
    public function createCard(int|string $oracleId, string $name, string $expansion): ?bool {
        $data = [];
        $data['oracle_id'] = $oracleId;
        $data['name'] = $name;
        $data['expansion_name'] = $expansion;
        return $this->createRow($data);
    }
    
    public function deleteCustomCards(): int|string|null {
        $ids = $this->dbmsTable->select('id', 'oracle_id < 0');
        return $ids === null ? null : $this->dbmsTable->delete($ids);
    }
}
