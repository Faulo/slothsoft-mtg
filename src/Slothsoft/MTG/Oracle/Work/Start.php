<?php
declare(strict_types = 1);
namespace Slothsoft\MTG\Oracle\Work;


class Start extends AbstractOracleWork
{
    protected function work(): void
    {
        $this->log('Initializing...');
    }
}

