<?php
declare(strict_types = 1);
namespace Slothsoft\MTG\Assets;

use Slothsoft\Farah\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\Module\Asset\AssetInterface;
use Slothsoft\Farah\Module\Asset\ExecutableBuilderStrategy\ExecutableBuilderStrategyInterface;
use Slothsoft\Farah\Module\Executable\ExecutableStrategies;
use Slothsoft\Farah\Module\Executable\ResultBuilderStrategy\FileInfoResultBuilder;
use Slothsoft\MTG\Oracle;
use Slothsoft\MTG\OracleInfo;
use Exception;
use SplFileInfo;

class RarityImageBuilder implements ExecutableBuilderStrategyInterface {
    
    public function buildExecutableStrategies(AssetInterface $context, FarahUrlArguments $args): ExecutableStrategies {
        $card = [];
        $card['expansion_name'] = $args->get('expansion_name');
        $card['expansion_abbr'] = $args->get('expansion_abbr');
        $card['rarity'] = $args->get('rarity');
        
        if ($card['expansion_name']) {
            $oracle = new Oracle('mtg');
            $idTable = $oracle->getIdTable();
            $setList = $idTable->getSetList();
            $card['expansion_abbr'] = array_search($card['expansion_name'], $setList);
            
            if (! $card['expansion_abbr']) {
                throw new Exception("Unknown expansion: $card[expansion_name]");
            }
        }
        
        $path = OracleInfo::getRarityPath($card);
        
        $file = new SplFileInfo($path);
        
        $resultBuilder = new FileInfoResultBuilder($file);
        
        return new ExecutableStrategies($resultBuilder);
    }
}

