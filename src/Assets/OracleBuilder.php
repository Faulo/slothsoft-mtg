<?php
declare(strict_types = 1);

namespace Slothsoft\MTG\Assets;

use DOMDocument;
use RuntimeException;
use Slothsoft\Core\DOMHelper;
use Slothsoft\Core\IO\Writable\Delegates\DOMWriterFromDocumentDelegate;
use Slothsoft\Farah\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\Module\Asset\AssetInterface;
use Slothsoft\Farah\Module\Asset\ExecutableBuilderStrategy\ExecutableBuilderStrategyInterface;
use Slothsoft\Farah\Module\Executable\ExecutableStrategies;
use Slothsoft\Farah\Module\Executable\ResultBuilderStrategy\DOMWriterResultBuilder;
use Slothsoft\Farah\Module\Executable\ResultBuilderStrategy\ProxyResultBuilder;
use Slothsoft\Farah\Module\Module;
use Slothsoft\MTG\Oracle;

class OracleBuilder implements ExecutableBuilderStrategyInterface {
    
    public function buildExecutableStrategies(AssetInterface $context, FarahUrlArguments $args): ExecutableStrategies {
        $url = $context->createUrl()->withPath('/static/oracle');
        $query = $args->get('search-query', []);
        
        if (! is_array($query) || $query === []) {
            $proxy = Module::resolveToExecutable($url);
            $resultBuilder = new ProxyResultBuilder($proxy);
        } else {
            $closure = function () use ($url, $query): DOMDocument {
                $targetDoc = DOMHelper::loadDocument((string) $url);
                $root = $targetDoc->documentElement ?? throw new RuntimeException('The Oracle document has no root element.');
                $oracle = new Oracle('mtg', $targetDoc);
                $root->appendChild($oracle->createSearchElement($targetDoc, $query));
                return $targetDoc;
            };
            $writer = new DOMWriterFromDocumentDelegate($closure);
            $resultBuilder = new DOMWriterResultBuilder($writer, 'oracle.xml');
        }
        
        return new ExecutableStrategies($resultBuilder);
    }
}

