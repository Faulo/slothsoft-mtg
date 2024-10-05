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

class CardImageBuilder implements ExecutableBuilderStrategyInterface {

    public function buildExecutableStrategies(AssetInterface $context, FarahUrlArguments $args): ExecutableStrategies {
        if ($name = $args->get('name')) {
            $oracle = new Oracle('mtg');
            $idTable = $oracle->getIdTable();
            $card = $idTable->getCardByName($name);
            if (! $card) {
                throw new Exception(sprintf('Card with name "%s" not found!', $name));
            }
        } else {
            $card = [];
            $card['expansion_abbr'] = $args->get('expansion_abbr');
            $card['expansion_index'] = $args->get('expansion_index');
        }

        $file = OracleInfo::getImageFile($card);

        $resultBuilder = new FileInfoResultBuilder($file);

        return new ExecutableStrategies($resultBuilder);
    }
}

