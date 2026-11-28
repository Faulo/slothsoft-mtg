<?php
declare(strict_types = 1);

namespace Slothsoft\MTG\Oracle\Work;

use Generator;
use Slothsoft\Core\IO\Writable\ChunkWriterInterface;
use Slothsoft\MTG\Oracle;

abstract class AbstractOracleWork implements ChunkWriterInterface {
    
    public function __construct(protected readonly Oracle $oracle) {
    }
    
    /**
     * @return Generator<int, string>
     */
    abstract public function toChunks(): Generator;
    
    protected function log(string $message, bool $important = false): string {
        return ($important ? '* ' : '') . $message . PHP_EOL;
    }
}
