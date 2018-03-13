<?php
namespace Slothsoft\MTG;

use Slothsoft\Core\DOMHelper;
use Slothsoft\Core\FileSystem;
use Slothsoft\Core\Image;
use Slothsoft\Core\Storage;
use Slothsoft\Core\Calendar\Seconds;
use Slothsoft\Core\IO\HTTPFile;
use Slothsoft\Core\Lambda\Stackable;
use DOMDocument;
use NumberFormatter;

class OracleWork extends Stackable
{

    const URL_ORACLE_INFO = 'http://gatherer.wizards.com/Pages/Card/Details.aspx?multiverseid=%s';

    const URL_ORACLE_SET = 'http://gatherer.wizards.com/Pages/Search/Default.aspx?output=checklist&action=advanced&special=true&set=["%s"]&page=%d';

    const XPATH_CARD_ROOT = '//*[contains(@class, "cardDetails")][.//*[contains(@src, "multiverseid=%s&type=card")]]';

    const XPATH_CARD_NAME = './/*[@class="value"][normalize-space(preceding-sibling::*) = "Card Name:"]';

    const XPATH_CARD_TYPE = './/*[@class="value"][normalize-space(preceding-sibling::*) = "Types:"]';

    const XPATH_CARD_RARITY = './/*[@class="value"][normalize-space(preceding-sibling::*) = "Rarity:"]';

    const XPATH_CARD_COST = './/*[@class="value"][normalize-space(preceding-sibling::*) = "Mana Cost:"]';

    const XPATH_CARD_CMC = './/*[@class="value"][normalize-space(preceding-sibling::*) = "Converted Mana Cost:"]';

    const XPATH_CARD_DESCRIPTION = './/*[@class="value"][normalize-space(preceding-sibling::*) = "Card Text:"]';

    const XPATH_CARD_DESCRIPTION_COST = './/*[@class="value"][normalize-space(preceding-sibling::*) = "Card Tex:"]//@src';

    const XPATH_CARD_FLAVOR = './/*[@class="value"][normalize-space(preceding-sibling::*) = "Flavor Text:"]';

    const XPATH_CARD_EXPANSION_NAME = './/*[@class="value"][normalize-space(preceding-sibling::*) = "Expansion:"]';

    const XPATH_CARD_EXPANSION_ABBR = './/*[@class="value"][normalize-space(preceding-sibling::*) = "Expansion:"]//@src';

    const XPATH_CARD_EXPANSION_NUMBER = './/*[@class="value"][normalize-space(preceding-sibling::*) = "Card Number:"]';

    /*
     * const XPATH_CARD_NAME = '//*[@class="contentTitle"]';
     * const XPATH_CARD_NAME = '//*[@id="ctl00_ctl00_ctl00_MainContent_SubContent_SubContent_nameRow"]/*[@class="value"]';
     * const XPATH_CARD_TYPE = '//*[@id="ctl00_ctl00_ctl00_MainContent_SubContent_SubContent_typeRow"]/*[@class="value"]';
     * const XPATH_CARD_COST = '//*[@id="ctl00_ctl00_ctl00_MainContent_SubContent_SubContent_manaRow"]/*[@class="value"]//@src';
     * const XPATH_CARD_CMC = '//*[@id="ctl00_ctl00_ctl00_MainContent_SubContent_SubContent_cmcRow"]/*[@class="value"]';
     * const XPATH_CARD_DESCRIPTION = '//*[@id="ctl00_ctl00_ctl00_MainContent_SubContent_SubContent_textRow"]/*[@class="value"]/*';
     * const XPATH_CARD_DESCRIPTION_COST = '//*[@id="ctl00_ctl00_ctl00_MainContent_SubContent_SubContent_textRow"]/*[@class="value"]//@src';
     * const XPATH_CARD_FLAVOR = '//*[@id="ctl00_ctl00_ctl00_MainContent_SubContent_SubContent_flavorRow"]/*[@class="value"]/*';
     * const XPATH_CARD_EXPANSION_NAME = '//*[@id="ctl00_ctl00_ctl00_MainContent_SubContent_SubContent_currentSetSymbol"]/*[last()]';
     * const XPATH_CARD_EXPANSION_NUMBER = '//*[@id="ctl00_ctl00_ctl00_MainContent_SubContent_SubContent_numberRow"]/*[@class="value"]';
     * const XPATH_CARD_RARITY = '//*[@id="ctl00_ctl00_ctl00_MainContent_SubContent_SubContent_rarityRow"]/*[@class="value"]';
     * //
     */
    const XPATH_CARD_SETS = '//*[@id="ctl00_ctl00_ctl00_MainContent_SubContent_SubContent_otherSetsValue"]/*/*';

    const XPATH_DOUBLE_MANALIST = '//*[
		@id="ctl00_ctl00_ctl00_MainContent_SubContent_SubContent_ctl03_manaRow" or 
		@id="ctl00_ctl00_ctl00_MainContent_SubContent_SubContent_ctl04_manaRow" or 
		@id="ctl00_ctl00_ctl00_MainContent_SubContent_SubContent_ctl05_manaRow" or
		@id="ctl00_ctl00_ctl00_MainContent_SubContent_SubContent_ctl06_manaRow"
	]//@src';

    const XPATH_LEGALITY_FORMATLIST = '//html:tr[html:td[normalize-space(.) = "Legal"]]/html:td[1]';

    const XPATH_PRICE_FIRST = 'normalize-space(//*[@itemprop="lowPrice"])';

    const XPATH_MARKET_PRICELIST = '/response/product/priceGuide/AVG';

    // 'normalize-space(//tr[.//*[contains(@onmouseover, "English")]]/td[@class="algn-r nowrap"]/text())';
    const XPATH_SET_LIST = '//tr[@class = "even" or @class="odd"]';

    const XPATH_SET_CARDNUMBER = 'normalize-space(td[1])';

    const XPATH_SET_CARDNAME = 'normalize-space(td[2])';

    const HTTP_CACHETIME = Seconds::YEAR;

    const MAX_ERRORS = - 1;

    const ERR_SUCCESS = 0;

    const ERR_URL_NOTFOUND = 1;

    const ERR_NAME_NOTFOUND = 2;

    const ERR_CREATE_FAILED = 3;

    const ERR_NUMBER_NOTFOUND = 4;

    const ERR_TYPE_INVALID = 5;

    const ERR_ID_BLACKLISTED = 6;

    protected $options;

    public function __construct(array $options)
    {
        $this->options = (array) $options;
    }

    public function getOptions()
    {
        return (array) $this->options;
    }

    public function work()
    {
        $options = $this->getOptions();
        switch ($options['mode']) {
            case 'start':
                return $this->workStart($options);
            case 'end':
                return $this->workEnd($options);
            
            case 'review_cfb':
                return $this->workReviewCFB($options);
            
            case 'index_custom':
                return $this->workIndexCustom($options);
            case 'index_spoiler':
                return $this->workIndexSpoiler($options);
            case 'index_tokens':
                return $this->workIndexTokens($options);
            case 'index_gatherer':
                return $this->workIndexGatherer($options);
            case 'index_convert':
                return $this->workIndexConvert($options);
            case 'index_images':
                return $this->workIndexImages($options);
            case 'index_prices':
                return $this->workIndexPrices($options);
            
            // case 'oracle_import': return $this->workOracleImport($options);
            case 'oracle_set':
                return $this->workOracleSet($options);
            // case 'oracle_price': return $this->workOraclePrice($options);
            case 'oracle_xml':
                return $this->workOracleXML($options);
            // case 'oracle_id': return $this->workOracleId($options);
            // case 'oracle_request': return $this->workOracleRequest($options);
            // case 'oracle_image': return $this->workOracleImage($options);
            case 'custom_card':
                return $this->workCustomCard($options);
            case 'custom_cardlist':
                return $this->workCustomCardList($options);
            case 'custom_import':
                return $this->workCustomImport($options);
            case 'download_images':
                return $this->workDownloadImages($options);
            case 'download_prices':
                return $this->workDownloadPrices($options);
            // case 'xpath_query': return $this->workXPathQuery($options);
        }
        $this->log(sprintf('Unknown mode?%s%s', PHP_EOL, print_r($options, true)), true);
    }

    protected function workStart(array $options)
    {
        $this->log('Started OracleWork!');
    }

    protected function workEnd(array $options)
    {
        $this->log('Finished OracleWork!');
    }

    protected function workReviewCFB(array $options)
    {
        $ret = [];
        $oracle = $options['oracle'];
        $reviewFile = sprintf('%s%s%s.json', $options['reviewDir'], DIRECTORY_SEPARATOR, $options['reviewName']);
        if ($review = $oracle->getReview($reviewFile)) {
            $this->log(sprintf('Downloading Reviews for %s...', $options['reviewName']));
            foreach ($options['setList'] as $setName => $url) {
                $updateCount = 0;
                $urlList = [];
                if ($xpath = Storage::loadExternalXPath($url)) {
                    $nodeList = $xpath->evaluate('//*[@class="postTitle"]/a');
                    foreach ($nodeList as $node) {
                        $urlList[] = $node->getAttribute('href');
                    }
                }
                foreach ($urlList as $url) {
                    if ($xpath = Storage::loadExternalXPath($url, self::HTTP_CACHETIME)) {
                        $nameNodeList = $xpath->evaluate('//h1[. = "Ratings Scale"]/following-sibling::h1[following-sibling::h3]');
                        foreach ($nameNodeList as $nameNode) {
                            $cardName = $xpath->evaluate('normalize-space(.)', $nameNode);
                            $rating = $xpath->evaluate('normalize-space(following-sibling::h3)', $nameNode);
                            $comment = $xpath->evaluate('normalize-space(following-sibling::p)', $nameNode);
                            
                            // $this->log(['name' => $cardName, 'rating' => $rating, 'comment' => $comment]);
                            if (preg_match('/(\d\.\d)/', $rating, $match)) {
                                $rating = $match[1];
                                $rating = 2 * (float) $rating;
                                if ($review->updateCard($setName, $cardName, $rating, $comment)) {
                                    $updateCount ++;
                                }
                            }
                        }
                    }
                }
                $this->log(sprintf('Reviewed %d cards for %s!', $updateCount, $setName), (bool) $updateCount);
                if ($updateCount) {
                    $review->save();
                }
            }
        }
        return $ret;
    }

    protected function workIndexCustom(array $options)
    {
        $ret = [];
        $oracle = $options['oracle'];
        $setDir = $options['setDir'];
        $imageDir = $options['imageDir'];
        $borderImage = $imageDir . DIRECTORY_SEPARATOR . 'border.png';
        
        $queryList = [];
        $queryList['name'] = 'concat(.//*[@class="pt"], " ", .//*[@class="name"])';
        $queryList['type'] = './/*[@class="type"]';
        $queryList['cost'] = 'concat("{", substring-before(.//*[@class="pt"], "/"), "}")';
        $queryList['rarity'] = '"Common"';
        $queryList['description'] = './/*[@class="rule-text"]';
        $queryList['flavor'] = './/*[@class="flavor-text"]';
        $queryList['expansion_name'] = '//h1';
        $queryList['expansion_number'] = 'substring-before(.//*[@class="card-number"], "/")';
        $queryList['legality'] = '"Custom"';
        $queryList['image'] = 'normalize-space(.//*[@class="scard"]/html:img/@src)';
        
        // legality
        $idTable = $oracle->getIdTable();
        $idCardList = $idTable->getUniqueCardList();
        // $matchCount = 0;
        if ($setDir) {
            $fileList = FileSystem::scanDir($setDir, FileSystem::SCANDIR_EXCLUDE_DIRS | FileSystem::SCANDIR_REALPATH);
            $dom = new DOMHelper();
            foreach ($fileList as $file) {
                $cardList = [];
                $setPath = pathinfo($file, PATHINFO_FILENAME);
                $setAbbr = sprintf('custom-%s', $setPath);
                $doc = $dom->loadDocument($file, true);
                $xpath = $dom->loadXPath($doc);
                
                $cardNodeList = $xpath->evaluate('//*[@class="card"]');
                foreach ($cardNodeList as $cardIndex => $cardNode) {
                    $card = [];
                    foreach ($queryList as $key => $query) {
                        $val = $xpath->evaluate($query, $cardNode);
                        
                        if (is_object($val)) {
                            $card[$key] = [];
                            foreach ($val as $node) {
                                $card[$key][] = $xpath->evaluate('normalize-space(.)', $node);
                            }
                            $card[$key] = implode(PHP_EOL . PHP_EOL, $card[$key]);
                        } else {
                            $card[$key] = $val;
                        }
                    }
                    $card['expansion_abbr'] = $setAbbr;
                    $card['expansion_index'] = $cardIndex;
                    $sourceImage = sprintf('%s%s%s-files%scard.%d.png', $setDir, DIRECTORY_SEPARATOR, $setPath, DIRECTORY_SEPARATOR, $card['expansion_number']);
                    $targetImage = sprintf('%s%s%s.%03d.png', $imageDir, DIRECTORY_SEPARATOR, $card['expansion_abbr'], $card['expansion_index']);
                    
                    // $card['sourceImage'] = $sourceImage;
                    // $card['targetImage'] = $targetImage;
                    
                    if (Image::mergeFile($sourceImage, $borderImage, $targetImage)) {
                        $card['image'] = sprintf('http://slothsoft.net/getResource.php/mtg/custom-cards/%s.%03d?%d', $card['expansion_abbr'], $card['expansion_index'], time());
                    }
                    
                    // $this->log($card);
                    
                    // legality
                    $query = [];
                    $pt = strstr($card['name'], ' ', true);
                    $types = mb_substr(strstr($card['type'], '—'), 1);
                    
                    $isArtifact = strpos($card['type'], 'Artifact') === false ? '' : 'artifact';
                    $isCreature = strpos($card['type'], 'Creature') === false ? '' : 'creature';
                    
                    $query = sprintf('~%s[a-z ]* %s %s %s token~', preg_quote($pt), preg_quote($types), preg_quote($isArtifact), preg_quote($isCreature));
                    $query = preg_replace('/\s+/', ' ', $query);
                    // $this->log($card['name']);
                    
                    $legalityList = [
                        $card['legality'] => null
                    ];
                    foreach ($idCardList as $idCard) {
                        // 1/1 colorless servo artifact creature token
                        if (preg_match($query, $idCard['description'])) {
                            // $this->log($idCard['name'] . ' (' . count($idCard['expansion_list']) . ')');
                            $legalityList += array_flip($idCard['legality_list']);
                            // $matchCount += count($idCard['expansion_list']);
                        }
                    }
                    $legalityList = array_keys($legalityList);
                    sort($legalityList);
                    $card['legality'] = implode(PHP_EOL, $legalityList);
                    
                    $cardList[] = $card;
                }
                if ($cardList) {
                    // rarity images
                    $rarityList = [];
                    $sourceImage = sprintf('%s%s%s-files%sset-symbol.png', $setDir, DIRECTORY_SEPARATOR, $setPath, DIRECTORY_SEPARATOR);
                    if ($file = HTTPFile::createFromPath($sourceImage)) {
                        foreach ($cardList as $card) {
                            if ($rarity = $card['rarity']) {
                                if (! isset($rarityList[$rarity])) {
                                    $rarityList[$rarity] = OracleInfo::getRarityPath($card);
                                }
                            }
                        }
                        foreach ($rarityList as $targetImage) {
                            $targetDir = dirname($targetImage);
                            $targetName = basename($targetImage);
                            $this->_verifyDirectory($targetDir);
                            $file->copyTo($targetDir, $targetName);
                        }
                    }
                    
                    $ret[] = [
                        'mode' => 'custom_cardlist',
                        'setAbbr' => $setAbbr,
                        'cardList' => $cardList
                    ];
                }
            }
        }
        // $this->log("matched $matchCount tokens to real cards!");
        $this->log(sprintf('IndexCustom: Prepared %d custom set threads!!', count($ret)));
        return $ret;
    }

    protected function workIndexSpoiler(array $options)
    {
        $ret = [];
        $oracle = $options['oracle'];
        $setList = $options['setList'];
        
        $idTable = $oracle->getIdTable();
        
        $queryList = [];
        $queryList['name'] = 'normalize-space(substring-before(//html:title, "|"))';
        // $queryList['image'] = 'normalize-space(//html:meta[@property="og:image"]/@content)';
        $queryList['image'] = 'normalize-space(//html:td[@width="265"]/html:img/@src)';
        // $queryList['type'] = 'normalize-space(//html:td[comment()="TYPE"])';
        // $queryList['cost'] = 'normalize-space(//html:td[comment()="MANA COST"])';
        // $queryList['description'] = '//html:td[comment()="CARD TEXT"]/text() | //html:td[comment()="CARD TEXT"]/*';
        // $queryList['flavor'] = 'normalize-space(//html:i[comment()="FLAVOR TEXT"])';
        $queryList['legality'] = '"Spoilers"';
        
        $keyList = [];
        $keyList['type'] = 'type';
        $keyList['cost'] = 'mana cost';
        $keyList['description'] = 'card text';
        $keyList['flavor'] = 'flavor text';
        
        $rarityList = [
            'common' => 'Common',
            'uncommon' => 'Uncommon',
            'rare' => 'Rare',
            'mythic' => 'Mythic Rare',
            'eldrazi' => 'Mythic Rare'
        ];
        
        $cardList = [];
        $setAbbr = 'spoilers';
        
        foreach ($setList as $set) {
            $setURL = $set['url'];
            $setName = $set['name'];
            
            if ($xpath = Storage::loadExternalXPath($setURL, 0)) {
                $cardObjectList = [];
                $cardNodeList = $xpath->evaluate('//html:a[starts-with(@href, "cards/")]');
                foreach ($cardNodeList as $cardNode) {
                    $rarity = $xpath->evaluate('normalize-space(preceding-sibling::comment()[position() = 1])', $cardNode);
                    $rarity = strtolower($rarity);
                    $rarity = isset($rarityList[$rarity]) ? $rarityList[$rarity] : 'Common';
                    $url = $setURL . $cardNode->getAttribute('href');
                    if ($tmpPath = Storage::loadExternalXPath($url, self::HTTP_CACHETIME)) {
                        $cardObjectList[] = [
                            'url' => $url,
                            'xpath' => $tmpPath,
                            'card' => [
                                'rarity' => $rarity
                            ]
                        ];
                    }
                }
                
                foreach ($cardObjectList as $cardObject) {
                    $url = $cardObject['url'];
                    $tmpPath = $cardObject['xpath'];
                    $card = $cardObject['card'];
                    
                    $arr = [];
                    $arr['url'] = $url;
                    $nodeList = $tmpPath->evaluate('//comment()');
                    foreach ($nodeList as $node) {
                        $key = $tmpPath->evaluate('normalize-space(.)', $node);
                        $key = strtolower($key);
                        $val = $tmpPath->evaluate('normalize-space(..)', $node);
                        if (strlen($val) < 256) {
                            $arr[$key] = $val;
                        }
                    }
                    foreach ($keyList as $key => $val) {
                        $card[$key] = isset($arr[$val]) ? $arr[$val] : '';
                    }
                    
                    foreach ($queryList as $key => $query) {
                        $val = $tmpPath->evaluate($query);
                        
                        if (is_object($val)) {
                            $card[$key] = [];
                            foreach ($val as $node) {
                                $v = $tmpPath->evaluate('normalize-space(.)', $node);
                                if (strlen($v)) {
                                    $card[$key][] = $v;
                                }
                            }
                            $card[$key] = implode(PHP_EOL . PHP_EOL, $card[$key]);
                        } else {
                            $card[$key] = $val;
                        }
                    }
                    
                    $card['expansion_name'] = $setName;
                    $card['expansion_abbr'] = $setAbbr;
                    $card['name'] = str_replace('/', ' // ', $card['name']);
                    $card['type'] = str_replace('-', '—', $card['type']);
                    $card['type'] = str_replace('Creauture', 'Creature', $card['type']);
                    
                    preg_match_all('/\d+/', $card['cost'], $matchA);
                    preg_match_all('/[A-Z]/', $card['cost'], $matchB);
                    $cost = array_merge($matchA[0], $matchB[0]);
                    foreach ($cost as &$c) {
                        $c = sprintf('{%s}', $c);
                    }
                    unset($c);
                    $card['cost'] = implode($cost);
                    
                    if (strlen($card['image'])) {
                        $card['image'] = dirname($url) . '/' . $card['image'];
                    } else {
                        $card['image'] = str_replace('.html', '.jpg', $url);
                    }
                    
                    if ($reprint = $idTable->getCardByName($card['name'])) {
                        foreach ($keyList as $key => $tmp) {
                            $card[$key] = $reprint[$key];
                        }
                    }
                    
                    // $this->log($card);continue;
                    
                    $cardList[] = $card;
                }
            } else {
                $this->log(sprintf('SPOILER URL NOT FOUND: %s', $setURL), true);
                // $this->log(gettype(Storage::loadExternalDocument($setURL, 0)));
                // $this->log(Storage::loadExternalFile($setURL, 0));
            }
        }
        
        $cardList = array_reverse($cardList);
        foreach ($cardList as $i => &$card) {
            $card['expansion_index'] = $i;
            $card['expansion_number'] = $i;
        }
        unset($card);
        
        $ret[] = [
            'mode' => 'custom_cardlist',
            'setAbbr' => $setAbbr,
            'cardList' => $cardList
        ];
        
        $this->log(sprintf('IndexSpoiler: Prepared %d spoiled cards!!', count($cardList)));
        return $ret;
    }

    protected function workIndexSpoilerOLD(array $options)
    {
        $ret = [];
        $oracle = $options['oracle'];
        $setList = $options['setList'];
        $setAbbr = 'spoilers';
        
        $queryList = [];
        $queryList['type'] = '""';
        $queryList['cost'] = '""';
        $queryList['name'] = 'normalize-space(.//html:h1 | .//html:a[@rel="bookmark"])';
        $queryList['description'] = './/*[@class="card-content"]/*';
        $queryList['legality'] = '"Spoilers"';
        $queryList['image'] = 'normalize-space(.//*[@class="scard"]/html:img/@src)';
        
        $translateList = [];
        $translateList['set'] = 'expansion_name';
        $translateList['color'] = 'cost';
        
        $cardList = [];
        foreach ($setList as $set) {
            $urlList = [
                $set['url']
            ];
            while (count($urlList)) {
                $url = array_shift($urlList);
                if ($xpath = Storage::loadExternalXPath($url, 0)) {
                    if ($url = $xpath->evaluate('string(//*[@class="nextpostslink"]/@href)')) {
                        $urlList[] = $url;
                    }
                    $cardXPathList = [];
                    $cardNodeList = $xpath->evaluate('//*[@class="spoiler-set-card"]/html:h3/html:a');
                    foreach ($cardNodeList as $cardNode) {
                        $url = $cardNode->getAttribute('href');
                        if ($tmpPath = Storage::loadExternalXPath($url, self::HTTP_CACHETIME)) {
                            $cardXPathList[] = $tmpPath;
                        }
                    }
                    
                    foreach ($cardXPathList as $tmpPath) {
                        $card = [];
                        foreach ($queryList as $key => $query) {
                            $val = $tmpPath->evaluate($query);
                            
                            if (is_object($val)) {
                                $card[$key] = [];
                                foreach ($val as $node) {
                                    $v = $tmpPath->evaluate('normalize-space(.)', $node);
                                    if (strlen($v)) {
                                        $card[$key][] = $v;
                                    }
                                }
                                $card[$key] = implode(PHP_EOL . PHP_EOL, $card[$key]);
                            } else {
                                $card[$key] = $val;
                            }
                        }
                        
                        $propNodeList = $tmpPath->evaluate('.//*[@class="card-type"]');
                        foreach ($propNodeList as $propNode) {
                            if ($prop = $tmpPath->evaluate('normalize-space(.)', $propNode)) {
                                $prop = explode(':', $prop);
                                if (count($prop) === 2) {
                                    $key = strtolower($prop[0]);
                                    $val = trim($prop[1]);
                                    if (isset($translateList[$key])) {
                                        $key = $translateList[$key];
                                    }
                                    $card[$key] = $val;
                                }
                            }
                        }
                        
                        $card['expansion_abbr'] = $setAbbr;
                        $card['name'] = str_replace('/', ' // ', $card['name']);
                        $card['type'] = str_replace('-', '—', $card['type']);
                        if (! isset($card['rarity']) or ! $card['rarity']) {
                            $card['rarity'] = 'Common';
                        }
                        switch ($card['cost']) {
                            case 'White':
                                $card['cost'] = '{W}';
                                break;
                            case 'Blue':
                                $card['cost'] = '{U}';
                                break;
                            case 'Black':
                                $card['cost'] = '{B}';
                                break;
                            case 'Red':
                                $card['cost'] = '{R}';
                                break;
                            case 'Green':
                                $card['cost'] = '{G}';
                                break;
                            case 'Multicolored':
                                $card['cost'] = '{W}{U}{B}{R}{G}';
                                break;
                            default:
                                $card['cost'] = '';
                                break;
                        }
                        
                        $cardList[] = $card;
                    }
                }
            }
        }
        
        if ($cardList) {
            $cardList = array_reverse($cardList);
            foreach ($cardList as $i => &$card) {
                $card['expansion_index'] = $i;
                $card['expansion_number'] = $i;
            }
            unset($card);
            
            $ret[] = [
                'mode' => 'custom_cardlist',
                'setAbbr' => $setAbbr,
                'cardList' => $cardList
            ];
        }
        
        $this->log(sprintf('IndexSpoiler: Prepared %d custom set threads!!', count($ret)));
        return $ret;
    }

    protected function workIndexTokens(array $options)
    {
        $ret = [];
        $oracle = $options['oracle'];
        
        $this->log(sprintf('IndexTokens: [DEACTIVATED] Prepared %d custom set threads!!', count($ret)));
        return $ret;
    }

    protected function workIndexGatherer(array $options)
    {
        $ret = [];
        $oracle = $options['oracle'];
        
        $setList = $oracle->getOracleSetList();
        foreach ($setList as $setName) {
            $options = [];
            $options['mode'] = 'oracle_set';
            $options['setName'] = $setName;
            
            $ret[] = $options;
        }
        
        $this->log(sprintf('IndexGatherer: Prepared %d set download threads!!', count($ret)));
        return $ret;
    }

    protected function workIndexConvert(array $options)
    {
        $ret = [];
        $oracle = $options['oracle'];
        $requestSize = 128;
        
        $idTable = $oracle->getIdTable();
        $cardList = $idTable->getUniqueCardList();
        
        $tokenCardList = [];
        $realCardList = [];
        foreach ($cardList as $card) {
            
            if (in_array(OracleInfo::getCardTypeName($card), [
                'Token',
                'Emblem'
            ])) {
                $tokenCardList[] = $card;
                /*
                 * $tmpList = $idTable->getCardListByName($card['name']);
                 * $range = range('A', 'Z');
                 * foreach ($tmpList as $i => $tmp) {
                 * $tmp['name'] .= ' ' . $range[$i];
                 * $tokenCardList[] = $tmp;
                 * }
                 * //
                 */
            } else {
                $realCardList[] = $card;
                
                // legality update
                $idTable->updateRowByName([
                    'legality' => $card['legality']
                ], $card['name']);
            }
        }
        
        $cardListList = array_chunk($realCardList, $requestSize);
        
        foreach ($cardListList as $cardList) {
            $options = [];
            $options['mode'] = 'oracle_xml';
            $options['cardList'] = $cardList;
            
            $ret[] = $options;
        }
        
        $cardListList = array_chunk($tokenCardList, $requestSize);
        
        foreach ($cardListList as $cardList) {
            $options = [];
            $options['mode'] = 'oracle_xml';
            $options['cardList'] = $cardList;
            
            $ret[] = $options;
        }
        
        $this->log(sprintf('IndexConvert: Prepared %d card convert threads!! (%d cards, %d tokens)', count($ret), count($realCardList), count($tokenCardList)));
        return $ret;
    }

    protected function workIndexImages(array $options)
    {
        $ret = [];
        $oracle = $options['oracle'];
        $imageDir = $options['imageDir'];
        
        $idTable = $oracle->getIdTable();
        $setList = isset($options['setList']) ? $options['setList'] : $idTable->getSetAbbrList();
        
        foreach ($setList as $setAbbr) {
            $options = [];
            $options['mode'] = 'download_images';
            $options['imageDir'] = $imageDir;
            $options['setAbbr'] = $setAbbr;
            $options['cardList'] = $idTable->getCardListBySetAbbr($setAbbr);
            
            $ret[] = $options;
        }
        
        $this->log(sprintf('IndexImages: Prepared %d image download threads!!', count($ret)));
        return $ret;
    }

    protected function workIndexPrices(array $options)
    {
        $ret = [];
        $oracle = $options['oracle'];
        
        $idTable = $oracle->getIdTable();
        
        $setList = isset($options['setList']) ? $options['setList'] : $idTable->getExpansionList();
        $urlList = OracleInfo::getMarketSetURLList();
        
        foreach ($setList as $set) {
            if (isset($urlList[$set])) {
                $url = $urlList[$set];
                
                $options = [];
                $options['mode'] = 'download_prices';
                $options['setName'] = $set;
                $options['marketURL'] = $url;
                
                $ret[] = $options;
            }
        }
        
        $this->log(sprintf('IndexPrices: Prepared %d set download threads!!', count($ret)));
        return $ret;
    }

    protected function workDownloadPrices(array $options)
    {
        $ret = [];
        $oracle = $options['oracle'];
        $setName = $options['setName'];
        $marketURL = $options['marketURL'];
        
        $idTable = $oracle->getIdTable();
        
        // $this->log(sprintf('Downloading prices for set "%s" from URL %s...', $setName, $marketURL));
        
        $priceList = [];
        $nf = new NumberFormatter(setlocale(LC_ALL, 0), NumberFormatter::PATTERN_DECIMAL);
        while ($marketURL) {
            $xpath = Storage::loadExternalXPath($marketURL, Seconds::WEEK);
            $marketURL = null;
            if ($xpath) {
                if ($next = $xpath->evaluate('string(//*[@rel="next"]/@href)')) {
                    $marketURL = 'https://www.magiccardmarket.eu' . $next;
                }
                
                $nodeList = $xpath->evaluate('//*[@class="MKMTable fullWidth"]/tbody/tr');
                foreach ($nodeList as $node) {
                    $cardName = $xpath->evaluate('normalize-space(td[3])', $node);
                    $cardPrice = $xpath->evaluate('normalize-space(td[6])', $node);
                    $cardPrice = $nf->parse($cardPrice);
                    $priceList[$cardName] = $cardPrice;
                }
            }
        }
        
        $lastCard = null;
        $successCount = 0;
        $totalCount = 0;
        
        $cardList = $idTable->getCardListBySetName($setName);
        foreach ($cardList as $card) {
            if (isset($priceList[$card['name']])) {
                $totalCount ++;
                if ($idTable->updateRowById([
                    'price' => $priceList[$card['name']]
                ], $card['id'])) {
                    $successCount ++;
                    $lastCard = $card;
                }
            }
        }
        
        if ($lastCard) {
            $lastCard = sprintf(' Last card: %s', $lastCard['name']);
        } else {
            $lastCard = '';
        }
        
        $this->log(sprintf('DownloadPrices: Updated %3d/%3d card prices from %s!!%s', $successCount, $totalCount, $options['setName'], $lastCard), $successCount);
        
        return $ret;
    }

    protected function workOracleSet(array $options)
    {
        $ret = [];
        $oracle = $options['oracle'];
        
        $cardList = [];
        // $foundList = [];
        for ($setPage = 0; $setPage < 10; $setPage ++) {
            $setURI = sprintf(self::URL_ORACLE_SET, urlencode($options['setName']), $setPage);
            $newCards = false;
            
            if ($xpath = Storage::loadExternalXPath($setURI, 0)) {
                $nodeList = $xpath->evaluate('//html:tr[@class = "cardItem"]');
                foreach ($nodeList as $node) {
                    // $name = $xpath->evaluate('normalize-space(.//html:a[@class = "nameLink"])', $node);
                    // $no = $xpath->evaluate('normalize-space(.//*[@class = "number"])', $node);
                    $cardId = (int) $xpath->evaluate('substring-after(.//html:a[@class = "nameLink"]/@href, "multiverseid=")', $node);
                    if ($cardId > 0) {
                        $idList = $this->_downloadOracleIds($cardId);
                        foreach ($idList as $id) {
                            if (! isset($cardList[$id])) {
                                $newCards = true;
                                $cardList[$id] = $id;
                            }
                        }
                        /*
                         * //$foundList ????????
                         * if (isset($foundList[$id])) {
                         * if ($foundList[$id] === false) {
                         * $uri = sprintf(self::URL_ORACLE_INFO, $id);
                         * if ($tmpPath = Storage::loadExternalXPath($uri, self::HTTP_CACHETIME)) {
                         * $foundList[$id] = true;
                         * $nodeList = $tmpPath->evaluate(self::XPATH_CARD_SETS);
                         * foreach ($nodeList as $node) {
                         * if (strpos($node->getAttribute('title'), $options['setName']) === 0) {
                         * $uri = $node->parentNode->getAttribute('href');
                         * $query = parse_url($uri, PHP_URL_QUERY);
                         * parse_str($query, $arr);
                         * if (isset($arr['multiverseid'])) {
                         * $cardList[] = (int) $arr['multiverseid'];
                         * }
                         * }
                         * }
                         * }
                         * }
                         * } else {
                         * $foundList[$id] = false;
                         * $cardList[] = $id;
                         * }
                         * //
                         */
                        /*
                         * if (isset($cardList[$no])) {
                         * //hier vielleicht doppel-karten sortieren
                         * } else {
                         * $cardList[(int) $no] = $id;
                         * }
                         * //
                         */
                    }
                }
                if (! $newCards) {
                    break;
                }
            } else {
                $this->log(sprintf('ERROR gathering cards: %s', $setURI), true);
            }
        }
        
        $idTable = $oracle->getIdTable();
        $lastCard = null;
        $successCount = 0;
        $totalCount = 0;
        
        // sort($cardList, SORT_NUMERIC);
        // $this->log(sprintf('Gatherer: Found %d cards, downloading... (%s)', count($cardList), $options['setName']));
        
        $dataList = [];
        foreach ($cardList as $cardId) {
            $data = $this->_downloadOracleData($cardId);
            if (is_array($data)) {
                $type = OracleInfo::getCardTypeName($data);
                // 'Conspiracy',
                if (in_array($type, [
                    'Token',
                    'Emblem',
                    'Plane',
                    'Scheme',
                    'Vanguard',
                    'Phenomenon'
                ])) {
                    continue;
                }
                if ($type === 'Other') {
                    $this->log(sprintf('Will not import non-card #%s: %s (%s)', $cardId, $data['name'], $data['type']), true);
                    continue;
                }
                $no = $data['expansion_abbr'] . '-' . $data['expansion_number'];
                if (isset($dataList[$no])) {
                    if (strlen($data['cost']) and ! strlen($dataList[$no]['cost'])) {
                        $this->log(sprintf('#%s "%s" looks better than #%s "%s"...', $data['oracle_id'], $data['name'], $dataList[$no]['oracle_id'], $dataList[$no]['name']));
                        $dataList[$no] = $data;
                        continue;
                    }
                    if (! strlen($data['cost']) and strlen($dataList[$no]['cost'])) {
                        $this->log(sprintf('#%s "%s" looks worse than #%s "%s"...', $data['oracle_id'], $data['name'], $dataList[$no]['oracle_id'], $dataList[$no]['name']));
                        continue;
                    }
                    $error = sprintf('ERROR? expansion number already exists: %s %s #%s <> %s #%s', $no, $data['name'], $data['oracle_id'], $dataList[$no]['name'], $dataList[$no]['oracle_id']);
                    // $this->log($error, true);
                    continue;
                }
                
                // $legality = $this->_downloadOracleLegality($data['oracle_id']);
                $legality = OracleInfo::getCardLegality($data);
                if ($legality !== null) {
                    $data['legality'] = implode(PHP_EOL, $legality);
                }
                $data['cmc'] = OracleInfo::getCardCMC($data);
                $data['colors'] = OracleInfo::getCardColors($data);
                
                $dataList[$no] = $data;
            }
        }
        ksort($dataList, SORT_NATURAL);
        
        // $this->log(sprintf('Gatherer: Downloaded %d cards, importing... (%s)', count($cardList), $options['setName']));
        
        $i = 0;
        foreach ($dataList as $data) {
            $totalCount ++;
            $data['expansion_index'] = $i;
            $res = $idTable->createRow($data);
            if ($res === null) {
                $this->log(sprintf('ERROR in %s, row %d!', $data['expansion_name'], $i + 1), true);
                break;
            } else {
                // $this->log(sprintf('Created #%06d: %s [%s]', $data['oracle_id'], $data['name'], $data['expansion_number']));
                $i ++;
                if ($res) {
                    $lastCard = $data;
                    $successCount ++;
                }
            }
        }
        
        if ($lastCard) {
            $lastCard = sprintf(' Last card: %s', $lastCard['name']);
        } else {
            $lastCard = '';
        }
        
        $this->log(sprintf('Gatherer: Imported %3d/%3d cards from %s!!%s', $successCount, $totalCount, $options['setName'], $lastCard), $successCount);
        return $ret;
    }

    protected function workOracleImport(array $options)
    {
        $ret = [];
        $cardList = array_values($options['cardList']);
        $setAbbr = $options['setAbbr'];
        $totalCount = count($cardList);
        
        $oracle = $options['oracle'];
        $idTable = $oracle->getIdTable();
        
        $setNumberList = $this->_downloadSetNumberList($setAbbr);
        
        $sortList = [];
        foreach ($cardList as &$card) {
            $card['_import'] = false;
            $card['ID'] = (int) $card['ID'];
            if ($card['ID'] > 0) {
                $card['_import'] = true;
                $prop = $card['PROPERTIES'];
                switch (true) {
                    case preg_match('/{PART=([^,]+), OTHER_PART=([^,]+), FLIPID=(\d+)}/', $prop, $match):
                    case preg_match('/{PART=(.+), OTHER_PART=(.+)}/', $prop, $match):
                        // Fuse Cards http://gatherer.wizards.com/Pages/Card/Details.aspx?multiverseid=368950
                        if (preg_match('/(\w+) \/\/ (\w+)/', $card['NAME'], $match)) {
                            $name = [
                                $match[1],
                                $match[2]
                            ];
                            $name = implode(' // ', $name);
                            $card['NAME'] = $name;
                        }
                        if ($arr = $this->_downloadOracleDoubleData($card['ID'])) {
                            // $this->log(sprintf('Cost %s => %s', $card['COST'], $arr['cost']));
                            $card['_overruleList'] = $arr;
                        } else {
                            $this->log('DOUBLE DATA NOT NOT FOUND ??? #' . $card['ID']);
                        }
                        break;
                    default:
                        $arr = $this->_downloadOracleData($card['ID']);
                        if (is_array($arr)) {
                            // $this->log(sprintf('OracleData: %s', print_r($arr, true)));
                            $card['_overruleList'] = $arr;
                        } else {
                            // $this->log('ORACLE DATA NOT NOT FOUND ??? #' . $card['ID']);
                        }
                        break;
                }
                switch (true) {
                    case preg_match('/^XX.+ \(([^\)]+)\)$/', $card['NAME'], $match):
                    case preg_match('/^[a-z].+ \(([^\)]+)\)$/', $card['NAME'], $match):
                        // XXValor (Valor)
                        // rathi Berserker
                        $card['NAME'] = $match[1];
                        break;
                    case preg_match('/^(.+) \([^\)]+\)$/', $card['NAME'], $match):
                        // Flip Cards http://gatherer.wizards.com/Pages/Card/Details.aspx?multiverseid=78691
                        $card['NAME'] = $match[1];
                        break;
                }
            }
            $nameKey = OracleInfo::getNameKey($card['NAME']);
            if (isset($setNumberList[$nameKey])) {
                $cardNo = $setNumberList[$nameKey];
                $card['COLLNUM'] = $cardNo;
                if (preg_match('/^\d+$/', (string) $cardNo)) {
                    // advance card number for multiply numbered cards (forests etc)
                    $cardNo = 1 + (int) $cardNo;
                    if (! in_array($cardNo, $setNumberList)) {
                        $setNumberList[$nameKey] = $cardNo;
                    }
                }
            } else {
                $card['_import'] = false;
                // $this->log(sprintf('CARD NUMER NOT FOUND ;__; #%d, %s, in set %s', $data['oracle_id'], $data['name'], $data['expansion_abbr']), true);
                // $this->log($nameKey);
                // $this->log($setList);
            }
            $sortList[] = $card['COLLNUM'];
        }
        unset($card);
        
        natsort($sortList);
        
        $mapping = [];
        $mapping['oracle_id'] = 'ID';
        $mapping['name'] = 'NAME';
        $mapping['type'] = 'TYPE';
        $mapping['cost'] = 'COST';
        $mapping['rarity'] = 'RARITY';
        $mapping['expansion_name'] = 'SET';
        $mapping['expansion_abbr'] = '_set';
        $mapping['expansion_number'] = 'COLLNUM';
        $mapping['description'] = 'ORACLE';
        
        $i = 0;
        $successCount = 0;
        $lastCard = null;
        foreach ($sortList as $key => $tmp) {
            $card = $cardList[$key];
            
            $import = $card['_import'];
            $data = [];
            $data['expansion_index'] = $i;
            foreach ($mapping as $dataKey => $attrKey) {
                $data[$dataKey] = $card[$attrKey];
            }
            
            if (isset($card['_overruleList'])) {
                foreach ($card['_overruleList'] as $key => $val) {
                    if (strlen($val)) {
                        $data[$key] = $val;
                    }
                }
            }
            
            if ($import) {
                $legality = $this->_downloadOracleLegality($data['oracle_id']);
                if ($legality !== null) {
                    /*
                     * if (!in_array('Standard', $legality)
                     * and in_array($options['setAbbr'], [
                     * 'ths', 'jou', 'bng', 'm15',
                     * 'ktk', 'frf',
                     * ])) {
                     * $legality[] = 'Standard';
                     * }
                     * //
                     */
                    $data['legality'] = implode(PHP_EOL, $legality);
                }
                
                $data['cmc'] = OracleInfo::getCardCMC($data);
                $data['colors'] = OracleInfo::getCardColors($data);
                
                $res = $idTable->createRow($data);
                if ($res === null) {
                    $this->log(sprintf('ERROR in %s, row %d!', $data['expansion_name'], $i + 1), true);
                    break;
                } else {
                    // $this->log(sprintf('Created #%06d: %s [%s]', $data['oracle_id'], $data['name'], $data['expansion_number']));
                    $i ++;
                    if ($res) {
                        $lastCard = $data['name'];
                        $successCount ++;
                    }
                }
            }
        }
        if ($lastCard) {
            $lastCard = sprintf(' Last card: %s', $lastCard);
        } else {
            $lastCard = '';
        }
        
        $this->log(sprintf('MagicDB: Imported %3d/%3d cards from %s!!%s', $successCount, $i, $data['expansion_name'], $lastCard), $successCount);
        return $ret;
    }

    protected function workOraclePrice(array $options)
    {
        $ret = [];
        $cardList = $options['cardList'];
        $totalCount = count($cardList);
        $successCount = 0;
        
        $oracle = $options['oracle'];
        $idTable = $oracle->getIdTable();
        $doc = new DOMDocument();
        
        $lastCard = null;
        while (count($cardList)) {
            $card = array_pop($cardList);
            $name = $card['name'];
            
            $price = $this->_downloadCardPrice($card);
            
            if ($price === null) {
                $this->log(sprintf('ERROR PRICING %s! URL:%s%s', $name, PHP_EOL, OracleInfo::getMarketURL($card)), true);
                break;
            } else {
                $res = $idTable->updateRowByName([
                    'price' => $price
                ], $name);
                if ($res === null) {
                    $this->log(sprintf('ERROR updating card %s, price %s!', $name, $price), true);
                    break;
                } else {
                    if ($res) {
                        $lastCard = $name;
                        $successCount ++;
                    }
                }
            }
        }
        
        if ($lastCard) {
            $lastCard = sprintf(' Last card: %s', $lastCard);
        } else {
            $lastCard = '';
        }
        
        $this->log(sprintf('Priced %3d/%3d cards!!%s', $successCount, $totalCount, $lastCard), $successCount);
        return $ret;
    }

    protected function workOracleXML(array $options)
    {
        $ret = [];
        $cardList = $options['cardList'];
        $totalCount = count($cardList);
        $successCount = 0;
        
        $oracle = $options['oracle'];
        // $idTable = $oracle->getIdTable();
        $xmlTable = $oracle->getXMLTable();
        $doc = new DOMDocument();
        
        $lastCard = null;
        while (count($cardList)) {
            $card = array_pop($cardList);
            $name = $card['name'];
            $xml = $this->_convertOracleData($card, $doc);
            
            $res = $xmlTable->createCard($name, $xml);
            if ($res === null) {
                $this->log(sprintf('ERROR CREATING %s! XML:%s%s', $name, PHP_EOL, $xml), true);
                break;
            } else {
                // legality update
                // $idTable->updateRowByName(['legality' => $card['legality']], $name);
                
                if ($res) {
                    $lastCard = $name;
                    $successCount ++;
                }
            }
        }
        
        if ($lastCard) {
            $lastCard = sprintf(' Last card: %s', $lastCard);
        } else {
            $lastCard = '';
        }
        
        $this->log(sprintf('OracleXML: Converted %3d/%3d cards!!%s', $successCount, $totalCount, $lastCard), $successCount);
        return $ret;
    }

    protected function workOracleImage(array $options)
    {
        $ret = [];
        $setAbbr = $options['setAbbr'];
        $dir = $options['dir'];
        $oracle = $options['oracle'];
        
        if ($image = $oracle->getSetImage($dir, $setAbbr)) {
            if ($file = $image->getFile(true)) {
                $this->log(sprintf('SUCCESS n__n http://dev.slothsoft.net/getData.php/mtg/image?set=%s', $setAbbr));
            } else {
                $this->log(sprintf('ERROR ;A; creating %s', $setAbbr), true);
            }
        } else {
            $this->log(sprintf('ERROR °A° %s: Oracle::getSetImage()', $setAbbr), true);
        }
        return $ret;
    }

    protected function workCustomCard(array $options)
    {
        $ret = [];
        $oracle = $options['oracle'];
        $card = $options['card'];
        $uri = $options['image'];
        
        $customTable = $oracle->getCustomTable();
        
        if ($image = $oracle->getCardImage($options['imageDir'], 0, $card['expansion_abbr'], $card['expansion_number'])) {
            if ($file = $image->getFile(false, $uri)) {
                
                $res = $customTable->createRow($card);
                if ($res === null) {
                    $this->log(sprintf('CustomCard: ERROR CREATING CARD "%s"!', $card['name']), true);
                    $this->log(print_r($card, true), true);
                } else {
                    if ($res) {
                        $this->log(sprintf('CustomCard: Created "%s"!', $card['name']));
                    }
                }
            } else {
                $this->log(sprintf('CustomCard: ERROR DOWNLOADING IMAGE "%s"!%s%s', $card['name'], PHP_EOL, $uri), true);
            }
        } else {
            $this->log(sprintf('CustomCard: ERROR INITIALIZING OracleCardImage "%s"!', $card['name']), true);
        }
        
        return $ret;
    }

    protected function workCustomCardList(array $options)
    {
        $ret = [];
        $oracle = $options['oracle'];
        $cardList = $options['cardList'];
        $setAbbr = $options['setAbbr'];
        
        $customTable = $oracle->getCustomTable();
        $customTable->deleteCardsBySetAbbr($setAbbr);
        
        foreach ($cardList as $card) {
            $res = $customTable->createRow($card);
            if ($res === null) {
                $this->log(sprintf('CustomCardList: ERROR CREATING CARD "%s"!', $card['name']), true);
                $this->log(print_r($card, true), true);
            } else {
                if ($res) {
                    $this->log(sprintf('CustomCardList: Created "%s"!', $card['name']));
                }
            }
        }
        
        return $ret;
    }

    protected function workCustomImport(array $options)
    {
        $ret = [];
        $oracle = $options['oracle'];
        $idTable = $oracle->getIdTable();
        $customTable = $oracle->getCustomTable();
        
        $successCount = 0;
        $totalCount = 0;
        $lastCard = null;
        
        $count = $idTable->deleteCustomCards();
        $this->log(sprintf('CustomImport: Cleared %d custom cards!', $count));
        
        $cardList = $customTable->getCardList();
        $nameList = [];
        $oracleId = 0;
        foreach ($cardList as $card) {
            if (OracleInfo::isCardToken($card)) {
                $name = $card['name'];
                if (! isset($nameList[$name])) {
                    $nameList[$name] = range('A', 'Z');
                }
                $card['name'] = sprintf('%s %s', $name, $nameList[$name] ? array_shift($nameList[$name]) : '?');
            }
            
            $oracleId --;
            unset($card['id']);
            $card['oracle_id'] = $oracleId;
            $card['cmc'] = OracleInfo::getCardCMC($card);
            $card['colors'] = OracleInfo::getCardColors($card);
            
            $res = $idTable->createRow($card);
            if ($res === null) {
                $this->log(sprintf('CustomImport: ERROR custom-importing %s!', $card['name']), true);
                $this->log($card, true);
                break;
            } else {
                $totalCount ++;
                if ($res) {
                    $successCount ++;
                    $lastCard = $card['name'];
                }
            }
        }
        
        if ($lastCard) {
            $lastCard = sprintf(' Last card: %s', $lastCard);
        } else {
            $lastCard = '';
        }
        $this->log(sprintf('CustomImport: Imported %3d/%3d cards!!%s', $successCount, $totalCount, $lastCard), $successCount);
        return $ret;
    }

    protected function workDownloadImages(array $options)
    {
        static $abbrExceptionList = [
            '8eb' => '8ed',
            '9eb' => '9ed'
        ];
        $ret = [];
        $oracle = $options['oracle'];
        $imageDir = $options['imageDir'];
        $setAbbr = $options['setAbbr'];
        
        $successCount = 0;
        $totalCount = 0;
        $lastCard = null;
        
        $storageDir = sprintf('%s\\_raw\\', $imageDir);
        $exportDir = sprintf('%s\\set-%s\\', $imageDir, $setAbbr);
        
        $this->_verifyDirectory($storageDir);
        $this->_verifyDirectory($exportDir);
        
        $rarityList = [];
        
        foreach ($options['cardList'] as $card) {
            $totalCount ++;
            if ($rarity = $card['rarity']) {
                if (! isset($rarityList[$rarity])) {
                    $rarityList[$rarity] = OracleInfo::getRarityPath($card);
                }
            }
            if ($url = $card['image']) {
                $storageName = sprintf('%s.bin', $card['oracle_id']);
                $storagePath = $storageDir . $storageName;
                
                $exportName = OracleInfo::getCardImageName($card);
                $exportPath = $exportDir . $exportName;
                
                /*
                 * if (file_exists($exportPath) and HTTPFile::verifyDownload($storagePath, $url)) {
                 * //file already exists, nothing to do
                 * $this->log(sprintf('already exists: %s%s%s', $exportName, PHP_EOL, $storagePath));
                 * } else {
                 * //
                 */
                $file = HTTPFile::createFromDownload($storagePath, $url);
                if (! $file) {
                    $tmp = OracleInfo::getOracleImageURL($card);
                    if ($url !== $tmp) {
                        $url = $tmp;
                        $file = HTTPFile::createFromDownload($storagePath, $url);
                    }
                }
                if ($file) {
                    // $this->log($exportPath);
                    if ($file->copyTo($exportDir, $exportName, [
                        '\\Image',
                        'convertFile'
                    ])) {
                        $successCount ++;
                        $lastCard = $card;
                    } else {
                        $this->log('could not convert?' . PHP_EOL . $url . PHP_EOL . $storagePath . PHP_EOL . $exportPath, true);
                        $file->delete();
                        // break;
                    }
                } else {
                    // $this->log("'$setAbbr' => '$setAbbr',");
                    $this->log('could not download?' . PHP_EOL . $url . PHP_EOL . $storagePath, true);
                    break;
                }
                // }
            } else {
                // $this->log('not image url? ' . $card['name']);
            }
        }
        
        foreach ($rarityList as $rarity => $path) {
            $url = sprintf('http://gatherer.wizards.com/Handlers/Image.ashx?type=symbol&set=%s&size=large&rarity=%s', isset($abbrExceptionList[$setAbbr]) ? $abbrExceptionList[$setAbbr] : $setAbbr, substr($rarity, 0, 1));
            if (HTTPFile::verifyURL($url)) {
                $file = HTTPFile::createFromDownload($path, $url);
                if (! $file) {
                    $this->log(sprintf('Error downloading rarity image %s from %s!', $path, $url), true);
                }
            } else {
                // $this->log(sprintf('Gatherer has no rarity image at %s!', $url), true);
            }
        }
        
        if ($lastCard) {
            $lastCard = sprintf(' Last card: %s', $lastCard['name']);
        } else {
            $lastCard = '';
        }
        
        $this->log(sprintf('DownloadImages: Converted %3d/%3d images!! [%s]%s', $successCount, $totalCount, $setAbbr, $lastCard), $successCount !== $totalCount);
        return $ret;
    }

    /*
     * protected function workXPathQuery(array $options) {
     * $ret = [];
     * foreach ($options['uriList'] as $i => $uri) {
     * if ($xpath = Storage::loadExternalXPath($uri, self::HTTP_CACHETIME)) {
     * $price = $xpath->evaluate($options['query']);
     * if (strlen($price)) {
     * $ret[$i] = $price;
     * }
     * } else {
     * //$ret[$i] = -1;
     * }
     * }
     * //return $ret;
     * }
     * //
     */
    protected function _downloadOracleIds($id)
    {
        $ret = [
            $id => $id
        ];
        $url = sprintf(self::URL_ORACLE_INFO, $id);
        if ($xpath = Storage::loadExternalXPath($url, self::HTTP_CACHETIME)) {
            $nodeList = $xpath->evaluate('//*[@class="variationLink"]');
            foreach ($nodeList as $node) {
                $id = $node->getAttribute('id');
                $ret[$id] = $id;
            }
        }
        return array_values($ret);
    }

    protected function _downloadOracleData($id)
    {
        static $setNumberMap = [];
        static $setExceptionList = [
            25498 => '6e',
            25492 => '6e',
            25477 => '6e',
            25503 => '6e',
            25500 => '6e',
            25507 => '6e',
            25514 => '6e',
            25510 => '6e',
            25483 => '6e',
            25502 => '6e',
            25486 => '6e',
            25536 => '6e',
            25526 => '6e',
            25452 => '6e',
            25508 => '6e',
            25458 => '6e',
            25481 => '6e',
            25528 => '6e',
            25540 => '6e',
            25454 => '6e',
            25506 => '6e',
            25499 => '6e',
            25529 => '6e',
            25533 => '6e',
            25487 => '6e',
            25501 => '6e',
            25543 => '6e',
            25497 => '6e',
            25479 => '6e',
            25504 => '6e',
            25515 => '6e',
            25448 => '6e',
            // http://gatherer.wizards.com/Pages/Search/Default.aspx?action=advanced&set=[%22Promo%20set%20for%20Gatherer%22]
            // http://magiccards.info/mbp/en.html
            25496 => 'ppr',
            // http://magiccards.info/8eb/en.html
            47784 => '8eb',
            47788 => '8eb',
            47785 => '8eb',
            47786 => '8eb',
            47789 => '8eb',
            47787 => '8eb',
            49056 => '8eb',
            // http://magiccards.info/9eb/en.html
            83064 => '9eb',
            83319 => '9eb',
            84073 => '9eb',
            83104 => '9eb',
            94912 => '9eb',
            94911 => '9eb',
            94910 => '9eb',
            83075 => '9eb',
            94914 => '9eb'
        ];
        static $idBlackList = [
            190199,
            201842,
            201843,
            386322,
            159048,
            159047,
            159056,
            209163,
            207998,
            209162,
            197936,
            197937,
            197261,
            5503,
            4979,
            5560,
            5472,
            5607,
            5601
        ];
        
        $ret = [];
        
        if (in_array($id, $idBlackList)) {
            // $this->log(sprintf('BLACKLISTED ORACLE ID: %s', $id));
            return self::ERR_ID_BLACKLISTED;
        }
        
        $ret['oracle_id'] = $id;
        
        if ($data = OracleInfo::getOracleCardData($ret)) {
            $ret += $data;
        } else {
            $this->log(sprintf('OracleInfo::getOracleCardData ERROR: %s', $id), true);
            return self::ERR_URL_NOTFOUND;
        }
        /*
         * $query = sprintf(self::XPATH_CARD_ROOT, $id);
         * $rootNode = $xpath->evaluate($query);
         * $rootNode = $rootNode->item(0);
         *
         * if (!$rootNode) {
         * $this->log(sprintf('CARD ROOT NOT FOUND: %s', $url));
         * return self::ERR_NAME_NOTFOUND;
         * }
         *
         * $queryList = [];
         * $queryList['name'] = self::XPATH_CARD_NAME;
         * $queryList['type'] = self::XPATH_CARD_TYPE;
         * $queryList['rarity'] = self::XPATH_CARD_RARITY;
         * //$queryList['cmc'] = self::XPATH_CARD_CMC;
         * $queryList['cost'] = self::XPATH_CARD_COST;
         * $queryList['description'] = self::XPATH_CARD_DESCRIPTION;
         * $queryList['flavor'] = self::XPATH_CARD_FLAVOR;
         * $queryList['expansion_name'] = self::XPATH_CARD_EXPANSION_NAME;
         * $queryList['expansion_abbr'] = self::XPATH_CARD_EXPANSION_ABBR;
         * $queryList['expansion_number'] = self::XPATH_CARD_EXPANSION_NUMBER;
         *
         * $ret['oracle_id'] = $id;
         * foreach ($queryList as $key => $query) {
         * $ret[$key] = [];
         * $nodeList = $xpath->evaluate($query, $rootNode);
         * foreach ($nodeList as $node) {
         * $imgNodeList = $xpath->evaluate('.//*[@src]', $node);
         * foreach ($imgNodeList as $imgNode) {
         * parse_str(parse_url($imgNode->getAttribute('src'), PHP_URL_QUERY), $arr);
         * if (isset($arr['name'])) {
         * $imgNode->textContent = sprintf('{%s}', $arr['name']);
         * }
         * }
         * $ret[$key][] = $xpath->evaluate('normalize-space(.)', $node);
         * }
         * $ret[$key] = implode(PHP_EOL, $ret[$key]);
         * }
         * $ret['expansion_abbr'] = preg_match('/set=([^&]+)/u', $ret['expansion_abbr'], $match)
         * ? strtolower($match[1])
         * : '';
         * //
         */
        
        if (isset($setExceptionList[$id])) {
            $ret['expansion_abbr'] = $setExceptionList[$id];
        }
        $ret['type'] = str_replace([
            'Summon —',
            'Eaturecray —',
            'Summon - The Biggest, Baddest, Nastiest,',
            'Scariest Creature You\'ll Ever See'
        ], 'Creature —', $ret['type']);
        $ret['type'] = str_replace([
            'Enchant Player'
        ], 'Enchantment — Aura Curse', $ret['type']);
        $ret['type'] = str_replace([
            'Interrupt'
        ], 'Instant', $ret['type']);
        $ret['rarity'] = str_replace('Basic Land', 'Land', $ret['rarity']);
        $ret['image'] = OracleInfo::getOracleImageURL($ret);
        
        if (OracleInfo::isCardToken($ret)) {
            // $this->log(sprintf('INVALID CARD TYPE: %s', $ret['type']));
            return self::ERR_TYPE_INVALID;
        }
        if (! strlen($ret['name']) or ! strlen($ret['expansion_name']) or ! strlen($ret['expansion_abbr'])) {
            $this->log(sprintf('CARD NAME NOT FOUND: %s', $url));
            return self::ERR_NAME_NOTFOUND;
        }
        
        $abbr = $ret['expansion_abbr'];
        if (! isset($setNumberMap[$abbr])) {
            $setNumberMap[$abbr] = $this->_downloadSetNameList($abbr);
            // $this->log(sprintf('_downloadSetNameList: %s', print_r($setNumberMap[$abbr])));
        }
        
        if (count($setNumberMap[$abbr])) {
            $no = $ret['expansion_number'];
            $ret['expansion_number'] = sprintf('x-%s', $ret['oracle_id']);
            $nameKey = OracleInfo::getNameKey($ret['name']);
            if (isset($setNumberMap[$abbr][$no]) and $setNumberMap[$abbr][$no] === $nameKey) {
                $ret['expansion_number'] = $no;
                $ret['image'] = OracleInfo::getSetImageURL($ret);
            } else {
                foreach ($setNumberMap[$abbr] as $i => $key) {
                    if ($key === $nameKey) {
                        $ret['expansion_number'] = $i;
                        $ret['image'] = OracleInfo::getSetImageURL($ret);
                        unset($setNumberMap[$abbr][$i]);
                        break;
                    }
                }
            }
        }
        
        if (! strlen($ret['expansion_number'])) {
            $this->log(sprintf('CARD NUMBER NOT FOUND: %s [%s] #%s%s%s', $ret['name'], OracleInfo::getNameKey($ret['name']), $ret['oracle_id'], PHP_EOL, print_r($setNumberMap[$abbr], true)), true);
            // $this->log(print_r($ret, true));
            // $this->log(sprintf('_downloadSetNameList: %s', print_r($setNumberMap[$abbr], true)));
            return self::ERR_NUMBER_NOTFOUND;
        }
        
        /*
         * $query = self::XPATH_CARD_COST;
         * $nodeList = $xpath->evaluate($query, $rootNode);
         * $costList = [];
         * foreach ($nodeList as $node) {
         * $uri = $xpath->evaluate('normalize-space(.)', $node);
         *
         * $query = parse_url($uri, PHP_URL_QUERY);
         * parse_str($query, $arr);
         * if (isset($arr['name'])) {
         * $costList[] = sprintf('{%s}', $arr['name']);
         * }
         * }
         * $ret['cost'] = implode('', $costList);
         *
         * $query = self::XPATH_CARD_DESCRIPTION_COST;
         * $nodeList = $xpath->evaluate($query, $rootNode);
         * $costList = [];
         * foreach ($nodeList as $node) {
         * $uri = $xpath->evaluate('normalize-space(.)', $node);
         *
         * $query = parse_url($uri, PHP_URL_QUERY);
         * parse_str($query, $arr);
         * if (isset($arr['name'])) {
         * $costList[] = sprintf('{%s}', $arr['name']);
         * }
         * }
         * if ($costList) {
         * $ret['description'] .= PHP_EOL . implode('', $costList);
         * }
         * //
         */
        return $ret;
    }

    protected function _downloadOracleDoubleData($id)
    {
        $ret = null;
        $url = sprintf(self::URL_ORACLE_INFO, $id);
        if ($xpath = Storage::loadExternalXPath($url, self::HTTP_CACHETIME)) {
            $nodeList = $xpath->evaluate(self::XPATH_DOUBLE_MANALIST);
            $manaList = [];
            foreach ($nodeList as $node) {
                $mana = [];
                $url = $xpath->evaluate('normalize-space(.)', $node);
                $url = parse_url($url, PHP_URL_QUERY);
                parse_str($url, $mana);
                $mana = $mana['name'];
                // Hier WG => W/G konvertierung...später...
                $manaList[] = sprintf('{%s}', $mana);
            }
            if ($manaList) {
                $ret = [];
                $ret['cost'] = implode('', $manaList);
            }
        } else {
            $this->log(sprintf('DOUBLE CARD URL NOT FOUND: %s', $url), true);
        }
        return $ret;
    }

    protected function _downloadOracleLegality($id)
    {
        $ret = null;
        $data = [
            'oracle_id' => $id
        ];
        $legalityURI = OracleInfo::getLegalityURL($data);
        if ($xpath = Storage::loadExternalXPath($legalityURI, self::HTTP_CACHETIME)) {
            $query = self::XPATH_LEGALITY_FORMATLIST;
            $nodeList = $xpath->evaluate($query);
            $legalityList = [];
            foreach ($nodeList as $node) {
                $legality = $xpath->evaluate('normalize-space(.)', $node);
                if (strlen($legality)) {
                    /*
                     * if (!strpos($legality, 'Block')) {
                     * if ($legality === 'Standard' or strpos($legality, 'Standard') === false) {
                     * $legalityList[] = $legality;
                     * }
                     * }
                     * //
                     */
                    if (! strpos($legality, ' ')) {
                        $legalityList[] = $legality;
                    }
                }
            }
            if (count($legalityList)) {
                // $ret = implode(PHP_EOL, $legalityList);
                $ret = $legalityList;
            }
        } else {
            $this->log(sprintf('Card legality not found?! %s', $legalityURI), true);
        }
        return $ret;
    }

    protected function _downloadCardPrice(array $card)
    {
        $ret = null;
        $options = [];
        $options['oauth'] = OracleInfo::getMarketOAuth();
        $options['cache'] = 0;
        if ($url = OracleInfo::getMarketURL($card)) {
            if ($xpath = Storage::loadExternalXPath($url, self::HTTP_CACHETIME, null, $options)) {
                $priceList = [];
                $nodeList = $xpath->evaluate(self::XPATH_MARKET_PRICELIST);
                foreach ($nodeList as $node) {
                    $price = (float) $node->textContent;
                    if ($price > 0) {
                        $priceList[] = $price;
                    }
                }
                if (count($priceList)) {
                    $ret = min($priceList);
                    $ret = sprintf('%.2f', $ret);
                }
                if ($ret === null) {
                    $ret = '0.00';
                    // Storage::clearExternalDocument($url, self::HTTP_CACHETIME);
                    // $this->log(sprintf('Market price not found?! %s', $url), true);
                }
            } else {
                $this->log(sprintf('MARKET URL NOT FOUND: %s', $url), true);
            }
        }
        return $ret;
    }

    protected function _downloadSetNumberList($setAbbr)
    {
        $ret = [];
        $data = [
            'expansion_abbr' => $setAbbr
        ];
        $url = OracleInfo::getSetURL($data);
        if ($xpath = Storage::loadExternalXPath($url, self::HTTP_CACHETIME)) {
            $nodeList = $xpath->evaluate(self::XPATH_SET_LIST);
            foreach ($nodeList as $node) {
                $number = $xpath->evaluate(self::XPATH_SET_CARDNUMBER, $node);
                $name = $xpath->evaluate(self::XPATH_SET_CARDNAME, $node);
                if (preg_match('/\((\w+\/\w+)\)/', $name, $match)) {
                    $name = str_replace('/', ' // ', $match[1]);
                }
                $name = preg_replace('/\([^\)]+\)/', '', $name);
                $name = OracleInfo::getNameKey($name);
                if (strlen($name)) {
                    if (! isset($ret[$name])) {
                        $ret[$name] = $number;
                    }
                }
            }
            if (! $ret) {
                Storage::clearExternalDocument($url, self::HTTP_CACHETIME);
                // $this->log(sprintf('SET NUMBER LIST NOT FOUND: %s', $url), true);
            }
            // $this->log($setAbbr);
            // $this->log($ret);
        } else {
            $this->log(sprintf('SET NUMBER URL NOT FOUND: %s', $url), true);
        }
        return $ret;
    }

    protected function _downloadSetNameList($setAbbr)
    {
        $ret = [];
        $data = [
            'expansion_abbr' => $setAbbr
        ];
        $url = OracleInfo::getSetURL($data);
        if ($xpath = Storage::loadExternalXPath($url, self::HTTP_CACHETIME)) {
            $nodeList = $xpath->evaluate(self::XPATH_SET_LIST);
            foreach ($nodeList as $node) {
                $number = $xpath->evaluate(self::XPATH_SET_CARDNUMBER, $node);
                $name = $xpath->evaluate(self::XPATH_SET_CARDNAME, $node);
                if (preg_match('/\((.+\/.+)\)/', $name, $match)) {
                    // $name = $match[1];
                    // $name = str_replace('/', ' // ', $name);
                }
                // $name = preg_replace('/\([^\)]+\)/', '', $name);
                $name = OracleInfo::getNameKey($name);
                $ret[$number] = $name;
            }
            if (! $ret) {
                Storage::clearExternalDocument($url, self::HTTP_CACHETIME);
                $this->log(sprintf('SET NUMBER LIST NOT FOUND: %s', $url), true);
            }
            // $this->log($setAbbr);
            // $this->log($ret);
        } else {
            $this->log(sprintf('SET NAME URL NOT FOUND: %s', $url), true);
        }
        // $this->log(sprintf('_downloadSetNameList: %s', print_r($ret, true)));
        return $ret;
    }

    // <card name="Volcanic Geyser" mana="2" type="Instant" set-rarity-name="Magic 2014 Core Set (Uncommon)" set-no="160" type-sup="Instant" set="Magic 2014 Core Set" rarity="Uncommon" href="http://gatherer.wizards.com/Pages/Card/Details.aspx?multiverseid=370614" image="/getResource.php/mtg/cards/Magic2014CoreSet.160" set-rarity="http://gatherer.wizards.com/Pages/Card/../../Handlers/Image.ashx?type=symbol&amp;set=M14&amp;size=small&amp;rarity=U"><mana color="Red" val="2"/></card>
    protected function _convertOracleData(array $data, DOMDocument $doc)
    {
        $attrList = [];
        $attrList['id'] = $data['oracle_id'];
        $attrList['name'] = $data['name'];
        $attrList['type'] = OracleInfo::getCardTypeName($data);
        $attrList['rarity'] = OracleInfo::getCardRarityName($data);
        $attrList['cmc'] = $data['cmc'];
        $attrList['description'] = $data['description'];
        $attrList['colors'] = '';
        
        $childList = [];
        
        $childList['type'] = [];
        $typeList = explode(' ', str_replace('—', '', $data['type']));
        foreach ($typeList as $type) {
            $type = trim($type);
            if (strlen($type)) {
                $childList['type'][] = [
                    $type
                ];
            }
        }
        
        $childList['set'] = [];
        if (! isset($data['expansion_list']) or count($data['expansion_list']) > 100) {
            $childList['set'][] = [
                $data['expansion_name']
            ];
        } else {
            foreach ($data['expansion_list'] as $id => $exp) {
                $childList['set'][] = [
                    $exp['name'],
                    'sort' => PHP_INT_MAX - $id
                ];
            }
        }
        
        $colorList = OracleInfo::getCardColorList($data);
        // $attrList['cmc'] = array_sum($colorList);
        $childList['color'] = [];
        foreach ($colorList as $key => $val) {
            if ($val > 0) {
                $childList['color'][] = [
                    $key,
                    'sort' => OracleInfo::getColorIndex($key),
                    'val' => $val,
                    'key' => OracleInfo::getColorKey($key)
                ];
            }
        }
        array_pop($colorList); // removes "colorless"
        $colorIndex = [];
        foreach ($colorList as $color => $tmp) {
            $colorIndex[] = $tmp === 0 ? '1' : '0';
            $attrList['colors'] .= $tmp === 0 ? '0' : '1';
        }
        $colorIndex = implode('', $colorIndex);
        
        $colorList = array_filter($colorList);
        $colorCount = count($colorList);
        if ($colorCount === 0) {
            $colorCount = 9; // colorless cards go in the back~
        }
        
        if (strlen($data['legality'])) {
            $childList['legality'] = explode(PHP_EOL, $data['legality']);
        }
        
        $sortIndex = [];
        $sortIndex[] = $colorCount;
        $sortIndex[] = $colorIndex;
        $sortIndex[] = sprintf('%02d', $attrList['cmc']);
        $sortIndex[] = sprintf('%02d', OracleInfo::getCardTypeIndex($data));
        $sortIndex[] = OracleInfo::getCardRarityIndex($data);
        $attrList['sort'] = implode('.', $sortIndex);
        
        if ($href = OracleInfo::getOracleURL($data)) {
            $attrList['href-oracle'] = $href;
        }
        $attrList['href-image'] = OracleInfo::getImageURL($data);
        $attrList['href-rarity'] = OracleInfo::getRarityURL($data);
        // $attrList['href-set'] = OracleInfo::getSetURL($data);
        if ($href = OracleInfo::getPriceURL($data)) {
            $attrList['href-price'] = $href;
        }
        // $attrList['price'] = $this->_downloadCardPrice($data);
        if ($data['price'] > 0) {
            $attrList['price'] = sprintf('%.02f', $data['price']);
        }
        
        $retNode = $doc->createElement('card');
        foreach ($attrList as $key => $val) {
            $retNode->setAttribute($key, $val);
        }
        foreach ($childList as $tagName => $list) {
            foreach ($list as $arr) {
                if (! is_array($arr)) {
                    $arr = [
                        $arr
                    ];
                }
                $content = array_shift($arr);
                $node = $doc->createElement($tagName);
                $node->appendChild($doc->createTextNode($content));
                foreach ($arr as $key => $val) {
                    if ($val !== null) {
                        $node->setAttribute($key, $val);
                    }
                }
                $retNode->appendChild($node);
            }
        }
        
        return $doc->saveXML($retNode);
    }

    protected function _verifyDirectory($dir)
    {
        if (strlen($dir)) {
            if (! is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
        }
    }

    protected function log($message, $important = false)
    {
        if (! is_string($message)) {
            $message = print_r($message, true);
        }
        if ($important) {
            $message = '!!! ' . $message;
        } else {
            $message = '    ' . $message;
        }
        return parent::log($message);
    }
}