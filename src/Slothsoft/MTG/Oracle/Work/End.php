<?php
namespace Slothsoft\MTG\Oracle\Work;


class End extends AbstractOracleWork
{
    protected function work(): void
    {
        $this->log('Finished!');
    }
}

