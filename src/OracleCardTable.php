<?php
declare(strict_types = 1);
namespace Slothsoft\MTG;

use w3c\dom\Node;

class OracleCardTable extends OracleTable {
    
    protected function install(): void {
        $sqlCols = [
            'id' => 'bigint NOT NULL AUTO_INCREMENT',
            'oracle_id' => 'bigint NOT NULL',
            'expansion_id' => 'int NOT NULL',
            'name' => 'varchar(200) NOT NULL',
            'mana' => 'tinytext NOT NULL',
            'types' => 'tinytext NOT NULL',
            'rarity' => 'enum ("Basic Land", "Common", "Uncommon", "Rare", "Mythic Rare")',
            'image_uri' => 'tinytext NOT NULL'
        ];
        $sqlKeys = [
            'id',
            'oracle_id',
            'expansion_id',
            'name'
        ];
        $this->dbmsTable->createTable($sqlCols, $sqlKeys);
    }
    
    protected function castList(array &$dataList): array {
        foreach ($dataList as &$data) {
            $this->cast($data);
        }
        unset($data);
        return $dataList;
    }
    
    protected function cast(array &$data): array {
        foreach ([
            'id',
            'parent_id',
            'type',
            'namespace',
            'name',
            'position'
        ] as $key) {
            if (isset($data[$key])) {
                $data[$key] = (int) $data[$key];
            }
        }
        return $data;
    }
    
    public function cleanse($documentId): void {
        while ($idList = $this->dbmsTable->select('id', [
            'parent_id' => null
        ], sprintf(' AND id != %d', $documentId))) {
            $childIdList = $this->dbmsTable->select('id', sprintf('parent_id IN (%s) AND id != %d', implode(',', $idList), $documentId));
            $this->dbmsTable->delete($idList);
            $this->dbmsTable->update([
                'parent_id' => null
            ], $childIdList);
        }
    }
    
    public function getDataList(): array {
        $dataList = $this->dbmsTable->select();
        return $this->castList($dataList);
    }
    
    public function getNode($id): ?array {
        $dataList = $this->dbmsTable->select(true, [
            'id' => $id
        ]);
        return $dataList ? $this->cast($dataList[0]) : null;
    }
    
    public function getIdByAttribute($attrValue, array $attrIdList): ?int {
        $idList = $this->dbmsTable->select('parent_id', [
            'value' => $attrValue
        ], sprintf('AND parent_id IS NOT NULL AND name IN (%s) LIMIT 1', implode(',', $attrIdList)));
        return count($idList) ? (int) $idList[0] : null;
    }
    
    public function getIdListByAttribute($attrValue, array $attrIdList): ?array {
        $idList = $this->dbmsTable->select('parent_id', [
            'value' => $attrValue
        ], sprintf('AND parent_id IS NOT NULL AND name IN (%s)', implode(',', $attrIdList)));
        foreach ($idList as &$id) {
            $id = (int) $id;
        }
        unset($id);
        return $idList;
    }
    
    public function getNodeListByParent($parentId): array {
        $dataList = $this->dbmsTable->select(true, [
            'parent_id' => $parentId
        ]);
        return $this->castList($dataList);
    }
    
    public function getChildNodes($parentId): array {
        $dataList = $this->dbmsTable->select(true, [
            'parent_id' => $parentId
        ], 'AND type != ' . Node::ATTRIBUTE_NODE);
        return $this->castList($dataList);
    }
    
    public function getAttributes($parentId): array {
        $dataList = $this->dbmsTable->select(true, [
            'parent_id' => $parentId,
            'type' => Node::ATTRIBUTE_NODE
        ]);
        return $this->castList($dataList);
    }
    
    public function getNodeByType($type): ?array {
        $dataList = $this->dbmsTable->select(true, [
            'type' => $type
        ], 'LIMIT 1');
        return $dataList ? $this->cast($dataList[0]) : null;
    }
    
    public function getNodeListByType($type): ?array {
        return $this->dbmsTable->select(true, [
            'type' => $type
        ]);
    }
    
    public function getNamespaceList(): ?array {
        return $this->dbmsTable->select('DISTINCT namespace');
    }
    
    public function createNode($type, $ns, $name, $value, $parentId): array {
        $data = [];
        $data['type'] = $type;
        $data['namespace'] = $ns;
        $data['name'] = $name;
        $data['value'] = $value;
        $data['parent_id'] = $parentId;
        $id = $this->dbmsTable->insert($data);
        $data['id'] = $id;
        // $data['parent_id'] = null;
        return $this->cast($data);
    }
    
    public function updateNodeValue(Node $node, $value): void {
        if ($node->data['value'] !== $value) {
            $node->data['value'] = $value;
            $this->dbmsTable->update([
                'value' => $value
            ], $node->data['id']);
        }
    }
    
    public function updateNodeParent(Node $node, $parentId): void {
        if ($node->data['parent_id'] !== $parentId) {
            $node->data['parent_id'] = $parentId;
            $this->dbmsTable->update([
                'parent_id' => $parentId
            ], $node->data['id']);
        }
    }
}