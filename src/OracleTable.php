<?php
declare(strict_types = 1);
namespace Slothsoft\MTG;

use Serializable;
use Slothsoft\Core\DBMS\Manager;
use Slothsoft\Core\DBMS\Table;

abstract class OracleTable implements Serializable {
    
    protected string $dbName;
    
    protected string $tableName;
    
    protected Table $dbmsTable;
    
    public function __construct(string $dbName, string $tableName) {
        $this->dbName = $dbName;
        $this->tableName = $tableName;
        $this->dbmsTable = Manager::getTable($dbName, $tableName);
        $this->init();
    }
    
    public function init(): void {
        if (! $this->exists()) {
            $this->install();
        }
    }
    
    public function exists(): ?bool {
        return $this->dbmsTable->tableExists();
    }
    
    public function getDBName(): string {
        return $this->dbName;
    }
    
    public function getTableName(): string {
        return $this->tableName;
    }
    
    abstract protected function install(): void;
    
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
