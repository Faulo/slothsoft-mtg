<?php
declare(strict_types = 1);

namespace Slothsoft\MTG\Oracle\Work;

use Generator;

class PrepareCustom extends AbstractOracleWork {
    
    /**
     * @return Generator<int, string>
     */
    public function toChunks(): Generator {
        yield from (new FetchCustom($this->oracle))->toChunks();
    }
}

