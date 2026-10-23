<?php
declare(strict_types = 1);
namespace Slothsoft\MTG;

use Exception;
use Laminas\Http\Client;
use Laminas\Http\Client\Adapter\Test as TestAdapter;
use PHPUnit\Framework\TestCase;
use Slothsoft\Core\CacheDirectoryStorage;
use Slothsoft\Core\ServerEnvironment;

/**
 * @runTestsInSeparateProcesses
 */
final class RemoteDocumentLoaderTest extends TestCase {

    private const URL = 'https://fixtures.example.test/card';

    private TestAdapter $adapter;

    private CacheDirectoryStorage $cache;

    private RemoteDocumentLoader $loader;

    /**
     * @throws Exception
     */
    protected function setUp(): void {
        ServerEnvironment::setCacheDirectory(temp_dir(self::class));
        $this->cache = new CacheDirectoryStorage('remote-tests');
        $this->adapter = new TestAdapter();
        $client = new Client();
        $client->setAdapter($this->adapter);
        $this->loader = new RemoteDocumentLoader($client, $this->cache);
    }

    public function testFreshCacheIsUsedAndZeroLifetimeForcesAFetch(): void {
        $this->respond('<oracle/>');
        $this->assertSame('oracle', $this->loader->loadDocument(self::URL, 60)->documentElement->tagName);
        $this->adapter->setResponse("HTTP/1.1 503 Unavailable\r\n\r\n");
        $this->assertSame('oracle', $this->loader->loadDocument(self::URL, 60)->documentElement->tagName);
        $this->assertNull($this->loader->loadDocument(self::URL));
    }

    public function testExpiredCacheIsRefreshed(): void {
        $this->cache->store(self::URL, '<old/>', time() - 120);
        $this->respond('<fresh/>');
        $this->assertSame('fresh', $this->loader->loadDocument(self::URL, 60)->documentElement->tagName);
    }

    public function testHtmlKeepsAnXhtmlNamespaceForGathererQueries(): void {
        $this->respond('<!doctype html><title>Test</title><a href="/card">Forest &amp; Island</a>');
        $xpath = $this->loader->loadXPath(self::URL);
        $this->assertSame('Forest & Island', $xpath->evaluate('string(//html:a)'));
    }

    public function testHttpErrorsAreNotCached(): void {
        $this->adapter->setResponse("HTTP/1.1 404 Not Found\r\n\r\nmissing");
        $this->assertNull($this->loader->loadDocument(self::URL, 60));
        $this->respond('<found/>');
        $this->assertSame('found', $this->loader->loadDocument(self::URL, 60)->documentElement->tagName);
    }

    public function testJsonAndInvalidJsonRetainNullableResults(): void {
        $this->respond('{"data":["gallery"]}');
        $this->assertSame(['data' => ['gallery']], $this->loader->loadJson(self::URL));
        $this->respond('{invalid');
        $this->assertNull($this->loader->loadJson(self::URL));
    }

    public function testParsingRestoresTheLibxmlErrorMode(): void {
        $previous = libxml_use_internal_errors(false);
        try {
            $this->respond('<!doctype html><title>Test</title>');
            $this->assertNotNull($this->loader->loadDocument(self::URL));
            $this->assertFalse(libxml_use_internal_errors());
        } finally {
            libxml_use_internal_errors($previous);
        }
    }

    private function respond(string $body): void {
        $this->adapter->setResponse("HTTP/1.1 200 OK\r\nContent-Type: text/plain; charset=utf-8\r\n\r\n" . $body);
    }
}
