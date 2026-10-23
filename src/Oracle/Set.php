<?php
declare(strict_types = 1);
namespace Slothsoft\MTG\Oracle;

class Set {
    
    private array $data;
    
    public function __construct(array $data) {
        $this->data = $data;
    }
}

