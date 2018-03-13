<?php
declare(strict_types = 1);
namespace Slothsoft\MTG;

use Slothsoft\Core\Storage;
use Slothsoft\Core\Calendar\Seconds;
use DOMDocument;

class MKM
{
    private static $defaultAuthority;
    public static function setDefaultAuthority(MKMAuthority $defaultAuthority) {
        self::$defaultAuthority = $defaultAuthority;
    }
    public static function getDefaultAuthority() : MKMAuthority {
        return self::$defaultAuthority;
    }
    

    const HOST = 'https://www.magiccardmarket.eu';

    const URL_BOOSTERS = '%s/Products/Boosters?onlyAvailable=no&sortBy=releaseDate&sortDir=desc&view=list&resultsPage=%d';

    const URL_SHOP = '%s/Users/%s';

    protected $storageTime;

    protected $shopList = [];

    public function __construct(Oracle $oracle)
    {
        $this->oracle = $oracle;
        $this->storageTime = Seconds::DAY;
    }

    public function getShopByName($name)
    {
        foreach ($this->shopList as $shop) {
            if ($shop->getName() === $name) {
                return $shop;
            }
        }
        return $this->addShop([
            'name' => $name,
            'uri' => sprintf(self::URL_SHOP, self::HOST, $name)
        ]);
        
        return isset($this->shopList[$name]) ? $this->shopList[$name] : $this->addShop([
            'name' => $name,
            'uri' => sprintf(self::URL_SHOP, self::HOST, $name)
        ]);
    }

    public function addShop(array $data)
    {
        $shop = new MKMShop($this, $data);
        $this->shopList[] = $shop;
        return $shop;
    }

    public function createShoppingElement(DOMDocument $doc, array $req)
    {
        $idTable = $this->oracle->getIdTable();
        $supplementalList = $idTable->getSetList();
        $vintageList = OracleInfo::getVintageLegalList();
        $standardList = OracleInfo::getStandardLegalList();
        $modernList = OracleInfo::getModernLegalList();
        
        $formatList = [];
        $formatList['Standard'] = null;
        $formatList['Modern'] = null;
        $formatList['Vintage'] = null;
        $formatList['Supplemental'] = null;
        if (isset($req['format'])) {
            foreach ($req['format'] as $format) {
                $formatList[$format] = true;
            }
        }
        $languageList = [];
        if (isset($req['language'])) {
            foreach ($req['language'] as $language) {
                $languageList[$language] = true;
            }
        }
        $countryList = [];
        if (isset($req['country'])) {
            foreach ($req['country'] as $country) {
                $countryList[$country] = true;
            }
        }
        
        $retNode = $doc->createElement('shopping');
        $boosterList = [];
        for ($i = 0; $i < 10; $i ++) {
            $uri = sprintf(self::URL_BOOSTERS, self::HOST, $i);
            if ($xpath = Storage::loadExternalXPath($uri, $this->storageTime)) {
                $rowNodeList = $xpath->evaluate('//table[@class="MKMTable fullWidth"]/tbody/tr/td/a');
                $success = false;
                foreach ($rowNodeList as $rowNode) {
                    $success = true;
                    $name = $xpath->evaluate('normalize-space(.)', $rowNode);
                    $uri = self::HOST . $rowNode->getAttribute('href');
                    
                    // , 'Phyrexian Faction Pack', 'Mirran Faction Pack'
                    $setName = trim(str_replace([
                        'Booster'
                    ], '', $name));
                    $setName = OracleInfo::translateSetName($setName);
                    
                    $format = null;
                    if (in_array($setName, $supplementalList)) {
                        $format = 'Supplemental';
                    }
                    if (in_array($setName, $vintageList)) {
                        $format = 'Vintage';
                    }
                    if (in_array($setName, $modernList)) {
                        $format = 'Modern';
                    }
                    if (in_array($setName, $standardList)) {
                        $format = 'Standard';
                    }
                    
                    if ($format) {
                        $boosterList[] = [
                            'name' => $name,
                            'set' => $setName,
                            'format' => $format,
                            'uri' => $uri
                        ];
                    }
                }
                if (! $success) {
                    break;
                }
            }
        }
        
        foreach ($boosterList as $booster) {
            if ($xpath = Storage::loadExternalXPath($booster['uri'], $this->storageTime)) {
                $rowNodeList = $xpath->evaluate('//table[@class="MKMTable fullWidth mt-40"]/tbody/tr');
                foreach ($rowNodeList as $rowNode) {
                    $shopName = $xpath->evaluate('normalize-space(td[1]//a)', $rowNode);
                    $id = $xpath->evaluate('normalize-space(.//select/@name)', $rowNode);
                    // $id = (int) preg_replace('/\D+/', '', $id);
                    // my_dump($id);
                    $country = $xpath->evaluate('normalize-space(td[1]//@onmouseover[contains(., "location")])', $rowNode);
                    $country = preg_replace('/^.+\: (.+)\'.+$/', '$1', $country);
                    $language = $xpath->evaluate('normalize-space(td[2]//@onmouseover)', $rowNode);
                    $language = preg_replace('/^.+\'(.+)\'.+$/', '$1', $language);
                    $price = $xpath->evaluate('normalize-space(td[@class="st_price"])', $rowNode);
                    $price = str_replace(',', '.', $price);
                    $price = (float) $price;
                    $format = $booster['format'];
                    
                    $skip = false;
                    if (! isset($formatList[$format])) {
                        $formatList[$format] = null;
                        $skip = true;
                    }
                    if (! isset($languageList[$language])) {
                        $languageList[$language] = null;
                        $skip = true;
                    }
                    if (! isset($countryList[$country])) {
                        $countryList[$country] = null;
                        $skip = true;
                    }
                    if ($skip) {
                        continue;
                    }
                    
                    $booster['id'] = $id;
                    $booster['language'] = $language;
                    $booster['country'] = $country;
                    $booster['price'] = $price;
                    
                    $shop = $this->getShopByName($shopName);
                    $shop->addBooster($booster);
                }
            }
        }
        
        foreach ($this->shopList as $shop) {
            $node = $shop->asNode($doc);
            $retNode->appendChild($node);
        }
        foreach ($boosterList as $booster) {
            $node = $doc->createElement('booster');
            foreach ($booster as $key => $val) {
                $node->setAttribute($key, $val);
            }
            $retNode->appendChild($node);
        }
        
        foreach ($formatList as $format => $active) {
            $node = $doc->createElement('format');
            $node->setAttribute('name', $format);
            if ($active) {
                $node->setAttribute('active', '');
            }
            $retNode->appendChild($node);
        }
        ksort($languageList);
        foreach ($languageList as $language => $active) {
            $node = $doc->createElement('language');
            $node->setAttribute('name', $language);
            if ($active) {
                $node->setAttribute('active', '');
            }
            $retNode->appendChild($node);
        }
        ksort($countryList);
        foreach ($countryList as $country => $active) {
            $node = $doc->createElement('country');
            $node->setAttribute('name', $country);
            if ($active) {
                $node->setAttribute('active', '');
            }
            $retNode->appendChild($node);
        }
        
        return $retNode;
    }
}