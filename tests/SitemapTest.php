<?php
declare(strict_types = 1);
namespace Slothsoft\MTG\Tests;

use Slothsoft\Farah\Configuration\AssetConfigurationField;
use Slothsoft\Farah\ModuleTests\AbstractSitemapTest;
use Slothsoft\Farah\Module\Asset\AssetInterface;

class SitemapTest extends AbstractSitemapTest
{

    protected static function loadSitesAsset(): AssetInterface
    {
        return (new AssetConfigurationField('farah://slothsoft@mtg/sitemap'))->getValue();
    }
}