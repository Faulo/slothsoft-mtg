<?php
declare(strict_types = 1);

namespace Slothsoft\MTG\Oracle\Work;

use Generator;

class FetchPrices extends AbstractOracleWork {
    
    /**
     * @return Generator<int, string>
     */
    public function toChunks(): Generator {
        yield $this->log('TODO');
    }
}

