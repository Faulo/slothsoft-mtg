<?php
declare(strict_types = 1);
namespace Slothsoft\MTG\Oracle\Work;


class End extends AbstractOracleWork
{
    protected function work(): void
    {
        $this->log('Finished!');
    }
}

