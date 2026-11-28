<?php
declare(strict_types = 1);

namespace Slothsoft\MTG\Oracle\Work;

use Generator;
use Slothsoft\MTG\Oracle;
use Slothsoft\MTG\Oracle\Card;

class UpdateIdTable extends AbstractOracleWork {
    
    /**
     * @param array<string, Card> $cards
     */
    public function __construct(Oracle $oracle, private readonly array $cards) {
        parent::__construct($oracle);
    }
    
    /**
     * @return Generator<int, string>
     */
    public function toChunks(): Generator {
        $idTable = null;
        
        $lastCard = null;
        $successCount = 0;
        $totalCount = 0;
        
        $i = 0;
        foreach ($this->cards as $card) {
            $data = $card->getData();
            
            $totalCount++;
            $data['expansion_index'] = $i;
            $idTable ??= $this->oracle->getIdTable();
            $res = $idTable->createRow($data);
            if ($res === null) {
                yield $this->log(sprintf('ERROR in %s, row %d!', $data['expansion_name'], $i + 1), true);
                break;
            } else {
                $i++;
                if ($res) {
                    $lastCard = $data;
                    $successCount++;
                }
            }
        }
        
        if ($lastCard) {
            $lastCard = sprintf(' Last card: %s', $lastCard['name']);
        } else {
            $lastCard = '';
        }
        
        yield $this->log(sprintf('Updated %3d/%3d cards!%s', $successCount, $totalCount, $lastCard), $successCount > 0);
    }
}

