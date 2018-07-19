<?php
namespace Slothsoft\MTG\Oracle\Work;

use Slothsoft\MTG\Oracle\GathererDownloader;

class FetchGatherer extends AbstractOracleWork
{
    protected function work(): void
    {
        $oracle = $this->getOracle();
        $setName = $this->getOption('setName');
        
        $downloader = new GathererDownloader();
        
        $this->log(sprintf('Fetching "%s"...', $setName));
        
        $cardList = [];
        foreach ($downloader->getCardsBySet($setName) as $cardId => $card) {
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
                $this->log(sprintf('Will not import non-card #%s: %s (%s)', $cardId, $card->getName(), $card->getType()), true);
                continue;
            }
            
            $number = $card->getSetName() . '-' . $card->getSetNumber();
            
            if (isset($cardList[$number])) {
                continue;
            }
            
            $cardList[$number] = $card;
        }
        ksort($cardList, SORT_NATURAL);
        
        $this->thenDo(UpdateIdTable::class, ['cards' => $cardList]);
        
        $this->log(sprintf('Prepared to update %d cards from "%s"!', count($cardList), $setName));
    }
}

