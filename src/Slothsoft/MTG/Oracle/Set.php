<?php
declare(strict_types = 1);
namespace Slothsoft\MTG\Oracle;

class Set
{
    private $data;
    public function __construct(array $data) {
        $this->data = $data;
    }
}

