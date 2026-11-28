<?php
declare(strict_types = 1);

namespace Slothsoft\MTG\Oracle\Work;

use Exception;
use Generator;
use Slothsoft\MTG\Oracle;
use Slothsoft\MTG\Oracle\GathererDownloader;

class FetchGatherer extends AbstractOracleWork {
    
    public function __construct(Oracle $oracle, private readonly string $setName) {
        parent::__construct($oracle);
    }
    
    /**
     * @return Generator<int, string>
     * @throws Exception
     */
    public function toChunks(): Generator {
        $downloader = new GathererDownloader();
        
        yield $this->log(sprintf('Fetching "%s"...', $this->setName));
        
        $cardList = [];
        foreach ($downloader->getCardIdsBySetName($this->setName) as $cardId) {
            try {
                $card = $downloader->getCardById($cardId);
                
                $type = $card->getTypeName();
                if (in_array($type, [
                    'Token',
                    'Emblem',
                    'Plane',
                    'Scheme',
                    'Vanguard',
                    'Phenomenon'
                ])) {
                    continue;
                }
                if ($type === 'Other') {
                    yield $this->log(sprintf('Will not import non-card #%s: %s (%s)', $cardId, $card->getName(), $card->getType()), true);
                    continue;
                }
                
                $number = $card->getSetName() . '-' . $card->getSetNumber();
                
                if (isset($cardList[$number])) {
                    continue;
                }
                
                $cardList[$number] = $card;
            } catch (Exception $e) {
                yield $this->log($e->getMessage(), true);
            }
        }
        ksort($cardList, SORT_NATURAL);
        
        yield $this->log(sprintf('Prepared to update %d cards from "%s"!', count($cardList), $this->setName));
        yield from (new UpdateIdTable($this->oracle, $cardList))->toChunks();
    }
}

