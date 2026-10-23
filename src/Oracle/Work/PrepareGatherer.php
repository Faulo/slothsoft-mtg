<?php
declare(strict_types = 1);
namespace Slothsoft\MTG\Oracle\Work;

use Exception;

class PrepareGatherer extends AbstractOracleWork {

    /**
     * @throws Exception
     */
    protected function work(): void {
        $oracle = $this->getOracle();
        
        $setList = $oracle->getOracleSetList();
        $this->log(sprintf('Preparing %d set download threads!', count($setList)));
        foreach ($setList as $setName) {
            $this->thenDo(FetchGatherer::class, [
                'setName' => $setName
            ]);
        }
    }
}
