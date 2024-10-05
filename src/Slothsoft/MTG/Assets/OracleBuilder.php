<?php
declare(strict_types = 1);
namespace Slothsoft\MTG\Assets;

use Slothsoft\Core\DOMHelper;
use Slothsoft\Core\IO\Writable\Delegates\DOMWriterFromDocumentDelegate;
use Slothsoft\Farah\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\Asset\AssetInterface;
use Slothsoft\Farah\Module\Asset\ExecutableBuilderStrategy\ExecutableBuilderStrategyInterface;
use Slothsoft\Farah\Module\Executable\ExecutableStrategies;
use Slothsoft\Farah\Module\Executable\ResultBuilderStrategy\DOMWriterResultBuilder;
use Slothsoft\Farah\Module\Executable\ResultBuilderStrategy\ProxyResultBuilder;
use Slothsoft\MTG\Oracle;
use DOMDocument;

class OracleBuilder implements ExecutableBuilderStrategyInterface {

    public function buildExecutableStrategies(AssetInterface $context, FarahUrlArguments $args): ExecutableStrategies {
        $url = $context->createUrl()->withPath('/static/oracle');
        $query = $args->get('search-query');

        if ($query === []) {
            $proxy = Module::resolveToExecutable($url);
            $resultBuilder = new ProxyResultBuilder($proxy);
        } else {
            $closure = function () use ($url, $query): DOMDocument {
                $targetDoc = DOMHelper::loadDocument((string) $url);
                if ($query !== []) {
                    $oracle = new Oracle('mtg', $targetDoc);
                    $queryNode = $oracle->createSearchElement($targetDoc, $query);
                    $targetDoc->documentElement->appendChild($queryNode);
                }
                return $targetDoc;
            };
            $writer = new DOMWriterFromDocumentDelegate($closure);
            $resultBuilder = new DOMWriterResultBuilder($writer, 'oracle.xml');
        }

        return new ExecutableStrategies($resultBuilder);
    }
}

