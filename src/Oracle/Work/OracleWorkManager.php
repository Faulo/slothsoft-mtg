<?php
declare(strict_types = 1);
namespace Slothsoft\MTG\Oracle\Work;

use Slothsoft\Farah\PThreads\WorkManager;
use Slothsoft\MTG\Oracle;

class OracleWorkManager extends WorkManager {

    private $oracle;

    public function __construct(int $treadCount, Oracle $oracle) {
        parent::__construct($treadCount);
        $this->oracle = $oracle;
    }

    public function thenDo(string $className, array $options = []): WorkManager {
        $options['oracle'] = $this->oracle;
        return parent::thenDo($className, $options);
    }
}

