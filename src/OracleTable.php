<?php
declare(strict_types = 1);
namespace Slothsoft\MTG;

use Slothsoft\Core\DBMS\Manager;
use Serializable;

abstract class OracleTable implements Serializable {
    
    protected $dbName;
    
    protected $tableName;
    
    protected $dbmsTable;
    
    public function __construct($dbName, $tableName) {
        $this->dbName = $dbName;
        $this->tableName = $tableName;
        $this->dbmsTable = Manager::getTable($dbName, $tableName);
        $this->init();
    }
    
    public function init() {
        if (! $this->exists()) {
            $this->install();
        }
    }
    
    public function exists() {
        return $this->dbmsTable->tableExists();
    }
    
    public function getDBName() {
        return $this->dbName;
    }
    
    public function getTableName() {
        return $this->tableName;
    }
    
    abstract protected function install();
    
    public function __serialize(): array {
        return [
            'db' => $this->dbName,
            'table' => $this->tableName
        ];
    }

    public function __unserialize(array $data): void {
        $this->__construct($data['db'], $data['table']);
    }

    public function serialize(): string {
        return serialize($this->__serialize());
    }

    public function unserialize(string $data): void {
        $this->__unserialize(unserialize($data));
    }
}
