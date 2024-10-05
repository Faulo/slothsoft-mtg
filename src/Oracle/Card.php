<?php
declare(strict_types = 1);
namespace Slothsoft\MTG\Oracle;

use Slothsoft\MTG\OracleInfo;

class Card {

    private $data;

    public function __construct(array $data) {
        $this->data = $data;
    }

    public function getName(): string {
        return $this->data['name'] ?? '';
    }

    public function getType(): string {
        return $this->data['type'] ?? '';
    }

    public function getTypeName(): string {
        return OracleInfo::getCardTypeName($this->data);
    }

    public function getTypeIndex(): int {
        return OracleInfo::getCardTypeIndex($this->data);
    }

    public function getSetName(): string {
        return $data['expansion_name'] ?? '';
    }

    public function getSetAbbr(): string {
        return $data['expansion_abbr'] ?? '';
    }

    public function getSetNumber(): string {
        return $data['expansion_number'] ?? '';
    }
}

