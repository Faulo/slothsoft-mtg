<?php
declare(strict_types = 1);

namespace Slothsoft\MTG\Oracle\Work;

use Generator;
use Slothsoft\Core\IO\Writable\ChunkWriterInterface;

class OracleWorkManager implements ChunkWriterInterface {
    
    /** @var list<AbstractOracleWork> */
    private array $jobs = [];
    
    public function thenDo(AbstractOracleWork $job): self {
        $this->jobs[] = $job;
        return $this;
    }
    
    /**
     * @return Generator<int, string>
     */
    public function toChunks(): Generator {
        foreach ($this->jobs as $job) {
            yield from $job->toChunks();
        }
    }
}
