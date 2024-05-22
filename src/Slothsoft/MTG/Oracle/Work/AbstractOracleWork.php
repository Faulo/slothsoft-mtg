<?php
declare(strict_types = 1);
namespace Slothsoft\MTG\Oracle\Work;

use Slothsoft\Farah\PThreads\AbstractWorkThread;
use Slothsoft\MTG\Oracle;

abstract class AbstractOracleWork extends AbstractWorkThread
{
    protected function getOracle() : Oracle {
        return $this->getOption('oracle');
    }
    protected function thenDo(string $className, array $options = []) : void {
        $options['oracle'] = $this->getOracle();
        parent::thenDo($className, $options);
    }
}

