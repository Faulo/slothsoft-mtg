<?php
declare(strict_types = 1);
namespace Slothsoft\MTG;

use DOMDocument;
use DOMXPath;
use Laminas\Http\Client;
use Masterminds\HTML5;
use Slothsoft\Core\CacheDirectoryStorage;
use Slothsoft\Core\DOMHelper;

final class RemoteDocumentLoader {

    private Client $client;

    private CacheDirectoryStorage $cache;

    public function __construct(?Client $client = null, ?CacheDirectoryStorage $cache = null) {
        $this->client = $client ?? new Client();
        $this->cache = $cache ?? new CacheDirectoryStorage('mtg-remote');
    }

    private function loadText(string $url, int $cacheTime): ?string {
        if ($cacheTime > 0) {
            $cached = $this->cache->retrieve($url, time() - $cacheTime);
            if ($cached !== null) {
                return $cached;
            }
        }
        $response = $this->client->setUri($url)->setMethod('GET')->send();
        if (! $response->isSuccess()) {
            return null;
        }
        $text = $response->getBody();
        if ($text === '') {
            return null;
        }
        $this->cache->store($url, $text, time());
        return $text;
    }

    public function loadDocument(string $url, int $cacheTime = 0): ?DOMDocument {
        $text = $this->loadText($url, $cacheTime);
        if ($text === null) {
            return null;
        }
        $previous = libxml_use_internal_errors(true);
        try {
            $document = new DOMDocument();
            if ($document->loadXML($text, LIBXML_NONET | LIBXML_PARSEHUGE)) {
                return $document;
            }
            $document = (new HTML5())->loadHTML($text);
            return $document->documentElement ? $document : null;
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }

    public function loadXPath(string $url, int $cacheTime = 0): ?DOMXPath {
        $document = $this->loadDocument($url, $cacheTime);
        return $document ? DOMHelper::loadXPath($document) : null;
    }

    public function loadJson(string $url, int $cacheTime = 0): ?array {
        $text = $this->loadText($url, $cacheTime);
        if ($text === null) {
            return null;
        }
        $data = json_decode($text, true);
        return is_array($data) ? $data : null;
    }
}
