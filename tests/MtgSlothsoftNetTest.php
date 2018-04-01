<?php
namespace tests;

use Slothsoft\Farah\Configuration\AssetConfigurationField;
use Slothsoft\Farah\ModuleTests\AbstractSitesTest;
use Slothsoft\Farah\Module\Node\Asset\AssetInterface;

class MtgSlothsoftNetTest extends AbstractSitesTest
{
    protected static function loadSitesAsset() : AssetInterface {
        return (new AssetConfigurationField('farah://slothsoft@mtg/sites/mtg.slothsoft.net'))->getValue();
    }
}