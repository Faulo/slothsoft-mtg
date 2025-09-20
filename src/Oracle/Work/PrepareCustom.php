<?php
declare(strict_types = 1);
namespace Slothsoft\MTG\Oracle\Work;

class PrepareCustom extends AbstractOracleWork {
    
    protected function work(): void {
        $oracle = $this->getOracle();
        
        $this->thenDo(FetchCustom::class);
    }
}

