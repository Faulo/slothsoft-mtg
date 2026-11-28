<?php
declare(strict_types = 1);

namespace Slothsoft\MTG\Assets;

use Exception;
use PHPUnit\Framework\TestCase;
use Slothsoft\Core\CacheDirectoryStorage;
use Slothsoft\Core\ServerEnvironment;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\Module;

final class WorkBuilderTest extends TestCase {
    
    /**
     * @runInSeparateProcess
     * @throws Exception
     */
    public function testWorkAssetStreamsItsJobsInOrder(): void {
        ServerEnvironment::setCacheDirectory(temp_dir(self::class));
        $cache = new CacheDirectoryStorage('mtg-remote');
        $cache->store(
            'https://gatherer.wizards.com/Pages/Advanced.aspx',
            '<div id="autoCompleteSourceBoxsetAddText0_InnerTextBoxcontainer"><span>Fixture Set</span></div>',
            time()
        );
        $cache->store(
            'https://gatherer.wizards.com/Pages/Search/Default.aspx?output=checklist&action=advanced&special=true&set=["Fixture+Set"]&page=0',
            '<html lang="en" xmlns="http://www.w3.org/1999/xhtml"><body><table/></body></html>',
            time()
        );
        
        $result = Module::resolveToResult(FarahUrl::createFromReference('farah://slothsoft@mtg/data/work'));
        $this->assertSame('work.txt', $result->lookupFileName());
        $this->assertSame('text/plain', $result->lookupMimeType());
        $this->assertSame([
            'Initializing...',
            'TODO',
            'Preparing 1 set downloads!',
            'Fetching "Fixture Set"...',
            'Prepared to update 0 cards from "Fixture Set"!',
            'Updated   0/  0 cards!',
            'TODO',
            'TODO',
            'TODO',
            'Finished!'
        ], explode(PHP_EOL, rtrim($result->lookupStringWriter()->toString(), "\r\n")));
    }
}
