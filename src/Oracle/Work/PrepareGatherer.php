<?php
declare(strict_types = 1);

namespace Slothsoft\MTG\Oracle\Work;

use Exception;
use Generator;
use RuntimeException;

class PrepareGatherer extends AbstractOracleWork {
    
    /**
     * @return Generator<int, string>
     * @throws Exception
     */
    public function toChunks(): Generator {
        $setList = $this->oracle->getOracleSetList()
            ?? throw new RuntimeException('Could not load the Oracle set list.');
        yield $this->log(sprintf('Preparing %d set downloads!', count($setList)));
        foreach ($setList as $setName) {
            try {
                yield from (new FetchGatherer($this->oracle, $setName))->toChunks();
            } catch (Exception $error) {
                yield $this->log($error->getMessage(), true);
            }
        }
    }
}
