<?php
declare(strict_types = 1);

namespace Slothsoft\MTG\Assets;

use Slothsoft\Farah\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\Module\Asset\AssetInterface;
use Slothsoft\Farah\Module\Asset\ExecutableBuilderStrategy\ExecutableBuilderStrategyInterface;
use Slothsoft\Farah\Module\Executable\ExecutableStrategies;
use Slothsoft\Farah\Module\Executable\ResultBuilderStrategy\ChunkWriterResultBuilder;
use Slothsoft\MTG\Oracle;
use Slothsoft\MTG\Oracle\Work\End;
use Slothsoft\MTG\Oracle\Work\OracleWorkManager;
use Slothsoft\MTG\Oracle\Work\PrepareCustom;
use Slothsoft\MTG\Oracle\Work\PrepareGatherer;
use Slothsoft\MTG\Oracle\Work\PrepareImages;
use Slothsoft\MTG\Oracle\Work\PreparePrices;
use Slothsoft\MTG\Oracle\Work\PrepareXml;
use Slothsoft\MTG\Oracle\Work\Start;

class WorkBuilder implements ExecutableBuilderStrategyInterface {
    
    public function buildExecutableStrategies(AssetInterface $context, FarahUrlArguments $args): ExecutableStrategies {
        $oracle = new Oracle('mtg');
        $writer = (new OracleWorkManager())
            ->thenDo(new Start($oracle))
            ->thenDo(new PrepareCustom($oracle))
            ->thenDo(new PrepareGatherer($oracle))
            ->thenDo(new PreparePrices($oracle))
            ->thenDo(new PrepareXml($oracle))
            ->thenDo(new PrepareImages($oracle))
            ->thenDo(new End($oracle));
        $resultBuilder = new ChunkWriterResultBuilder($writer, 'work.txt');
        return new ExecutableStrategies($resultBuilder);
    }
}

