<?php
declare(strict_types = 1);
namespace Slothsoft\MTG;

use Exception;

class OracleIdTable extends OracleTable
{

    protected function install()
    {
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

    protected function castList(array &$dataList)
    {
        foreach ($dataList as &$data) {
            $this->cast($data);
        }
        unset($data);
        return $dataList;
    }

    protected function cast(array &$data)
    {
        foreach ([] as $key) {
            if (isset($data[$key])) {
                $data[$key] = (int) $data[$key];
            }
        }
        return $data;
    }

    public function searchNameByCard(array $query)
    {
        $ret = [];
        $sqlList = [];
        foreach ($query as $key => $val) {
            if (strlen($val)) {
                if ($val[0] === '!') {
                    $val = substr($val, 1);
                    $not = true;
                } else {
                    $not = false;
                }
                $sql = [];
                $valList = explode('|', $val);
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

    public function getMissingOracleId()
    {
        $ret = 1;
        $idList = $this->dbmsTable->select('oracle_id', '1 ORDER BY oracle_id ASC');
        array_unshift($idList, 0);
        foreach ($idList as $i => $id) {
            if ($i !== (int) $id) {
                $ret = $i;
                break;
            }
        }
        return $ret;
    }

    public function getLastOracleId()
    {
        $idList = $this->dbmsTable->select('oracle_id', '1 ORDER BY oracle_id DESC LIMIT 1');
        return current($idList);
    }

    public function getOracleIdList()
    {
        return $this->dbmsTable->select('oracle_id');
    }

    public function getExpansionList()
    {
        return $this->dbmsTable->select('DISTINCT expansion_name');
    }

    public function getSetAbbrList()
    {
        return $this->dbmsTable->select('DISTINCT expansion_abbr');
    }

    public function getCustomSetAbbrList()
    {
        return $this->dbmsTable->select('DISTINCT expansion_abbr', 'expansion_abbr LIKE "custom-%"');
    }

    public function getSetList()
    {
        $ret = [];
        // SELECT expansion_name, expansion_abbr, count(id) cards FROM `oracle-ids` group by expansion_abbr ORDER BY cards DESC
        // $res = $this->dbmsTable->select(['DISTINCT expansion_abbr', 'expansion_name']);
        $res = $this->dbmsTable->select([
            'expansion_abbr',
            'expansion_name',
            'count(id) cards'
        ], '', 'GROUP BY expansion_abbr ORDER BY cards DESC');
        foreach ($res as $row) {
            $ret[$row['expansion_abbr']] = $row['expansion_name'];
        }
        return $ret;
    }

    public function getMissingOracleIdList(array $idList)
    {
        return array_diff($idList, $this->dbmsTable->select('oracle_id', sprintf('oracle_id IN (%s)', implode(',', $idList))));
    }

    public function getNameListByOracleIdList(array $idList)
    {
        $nameList = $this->dbmsTable->select('name', [
            'oracle_id' => $idList
        ]);
        return $nameList;
    }

    public function getNameByOracleId($id)
    {
        $nameList = $this->dbmsTable->select('name', [
            'oracle_id' => $id
        ]);
        return current($nameList);
    }

    public function getNameListBySetName($setName)
    {
        return $this->dbmsTable->select('name', [
            'expansion_name' => $setName
        ], 'ORDER BY expansion_index');
    }

    public function getIdListByName($name)
    {
        return $this->dbmsTable->select('id', [
            'name' => $name
        ]);
    }

    public function getCardListBySetName($setName)
    {
        return $this->dbmsTable->select(true, [
            'expansion_name' => $setName
        ], 'ORDER BY expansion_index');
    }

    public function getCardListBySetAbbr($setAbbr)
    {
        return $this->dbmsTable->select(true, [
            'expansion_abbr' => $setAbbr
        ], 'ORDER BY expansion_index');
    }

    public function getCardByName($name)
    {
        $ret = $this->dbmsTable->select(true, [
            'name' => $name
        ], 'ORDER BY oracle_id DESC');
        return $ret ? reset($ret) : null;
    }

    public function getCardListByName($name)
    {
        return $this->dbmsTable->select(true, [
            'name' => $name
        ]);
    }

    public function getCardListByNameList(array $nameList)
    {
        return $this->dbmsTable->select(true, [
            'name' => $nameList
        ]);
    }

    public function getUniqueCardList()
    {
        $retList = [];
        $nameList = $this->dbmsTable->select('DISTINCT name');
        foreach ($nameList as $name) {
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

    public function createRow(array $data)
    {
        try {
            $ret = (bool) $this->dbmsTable->insert($data, $data);
        } catch (Exception $e) {
            $ret = null;
        }
        return $ret;
    }

    public function updateRowByName(array $data, $name)
    {
        try {
            $ret = (bool) $this->dbmsTable->update($data, $this->getIdListByName($name));
        } catch (Exception $e) {
            $ret = null;
        }
        return $ret;
    }

    public function updateRowById(array $data, $id)
    {
        try {
            $ret = (bool) $this->dbmsTable->update($data, $id);
        } catch (Exception $e) {
            $ret = null;
        }
        return $ret;
    }

    public function createCard($oracleId, $name, $expansion)
    {
        $data = [];
        $data['oracle_id'] = $oracleId;
        $data['name'] = $name;
        $data['expansion_name'] = $expansion;
        return $this->createRow($data);
    }

    public function deleteCustomCards()
    {
        return $this->dbmsTable->delete($this->dbmsTable->select('id', 'oracle_id < 0'));
    }
}