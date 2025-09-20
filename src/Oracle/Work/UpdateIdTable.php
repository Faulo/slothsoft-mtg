<?php
declare(strict_types = 1);
namespace Slothsoft\MTG\Oracle\Work;

class UpdateIdTable extends AbstractOracleWork {
    
    protected function work(): void {
        $oracle = $this->getOracle();
        $idTable = $oracle->getIdTable();
        $cards = $this->getOption('cards');
        
        $lastCard = null;
        $successCount = 0;
        $totalCount = 0;
        
        $i = 0;
        foreach ($cards as $card) {
            $data = $card->getData();
            
            $totalCount ++;
            $data['expansion_index'] = $i;
            $res = $idTable->createRow($data);
            if ($res === null) {
                $this->log(sprintf('ERROR in %s, row %d!', $data['expansion_name'], $i + 1), true);
                break;
            } else {
                $i ++;
                if ($res) {
                    $lastCard = $data;
                    $successCount ++;
                }
            }
        }
        
        if ($lastCard) {
            $lastCard = sprintf(' Last card: %s', $lastCard['name']);
        } else {
            $lastCard = '';
        }
        
        $this->log(sprintf('Updated %3d/%3d cards!%s', $successCount, $totalCount, $lastCard), $successCount > 0);
    }
}

