<?php
declare(strict_types = 1);
namespace Slothsoft\MTG;

use Slothsoft\Core\ServerEnvironment;
use Slothsoft\Core\DOMHelper;
use Slothsoft\Core\Storage;
use Slothsoft\Core\Calendar\Seconds;
use DOMDocument;
use Exception;
use XSLTProcessor;

class OracleInfo
{

    const EXTENSION_CARDIMAGE = 'png';

    const URL_ORACLE = 'http://gatherer.wizards.com/Pages/Card/Details.aspx?multiverseid=%s';

    const URL_ORACLE_IMAGE = 'http://gatherer.wizards.com/Handlers/Image.ashx?multiverseid=%s&type=card';

    const URL_LEGALITY = 'http://gatherer.wizards.com/Pages/Card/Printings.aspx?multiverseid=%s';

    const URL_TEMPLATE_ORACLE = 'http://dende/getTemplate.php/mtg/card-gatherer';

    const URL_SET = 'http://magiccards.info/%s/en.html';

    const URL_SET_IMAGE = 'http://magiccards.info/scans/en/%s/%s.jpg';

    const URL_SET_TOKENS = 'http://magiccards.info/extras.html';

    const URL_PRICE = 'https://www.magiccardmarket.eu/Cards/%s';

    const URL_MARKET_SET_INDEX = 'https://www.magiccardmarket.eu/Products/Singles';

    const URL_MARKET_SET_LIST = 'https://www.magiccardmarket.eu/Products/Singles/%s';

    const URL_MARKET = 'https://www.mkmapi.eu/ws/v1.1/products/%s/1/1/true';

    const URL_LEGALITY_MODERN = 'http://magic.wizards.com/en/gameinfo/gameplay/formats/modern';

    const URL_LEGALITY_COMMANDER = 'http://magic.wizards.com/en/gameinfo/gameplay/formats/commander';

    const URL_LEGALITY_VINTAGE = 'https://mtg.gamepedia.com/Set';

    const URL_GALLERY_HOST = 'http://magic.wizards.com';

    const URL_GALLERY_INDEX = '/en/search-magic-ajax?f1=section&f2=18156&sort=DESC&search=&l=en';

    const URL_GALLERY_EXAMPLE = '/en/articles/archive/card-image-gallery/aether-revolt';

    // const URL_IMAGE = '/getData.php/mtg/image?id=%s&set=%s&no=%s';
    // const URL_RARITY = '/getData.php/mtg/image?set=%s&rarity=%s'; //'http://gatherer.wizards.com/Handlers/Image.ashx?type=symbol&set=%s&size=large&rarity=%s';
    const COLOR_W = 'White';

    const COLOR_U = 'Blue';

    const COLOR_B = 'Black';

    const COLOR_R = 'Red';

    const COLOR_G = 'Green';

    const COLOR_NO = 'Colorless';

    private static $_typeList = [
        'Land',
        'Planeswalker',
        'Creature',
        'Artifact',
        'Enchantment',
        'Sorcery',
        'Instant',
        // 6
        'Token',
        'Emblem',
        'Plane',
        'Scheme',
        'Conspiracy',
        'Vanguard',
        'Phenomenon',
        'Other'
    ];

    private static $_rarityList = [
        'Common',
        'Uncommon',
        'Rare',
        'Mythic Rare'
    ];

    private static $_colorList = [
        'White',
        'Blue',
        'Black',
        'Red',
        'Green',
        'Colorless'
    ];

    private static $_legalityList = [
        'Standard',
        'Modern',
        'Commander',
        // 'Legacy',
        'Vintage'
        // 'Un-Sets',
    ];

    private static $_legalityStandardList = [
        'Ixalan',
        
        'Hour of Devastation',
        'Amonkhet',
        'Welcome Deck 2017',
        'Aether Revolt',
        'Kaladesh',
        
        'Eldritch Moon',
        'Shadows over Innistrad',
        'Welcome Deck 2016',
        'Oath of the Gatewatch',
        'Battle for Zendikar'
        
        // 'Magic Origins',
        // 'Dragons of Tarkir',
        
    // 'Fate Reforged',
        // 'Khans of Tarkir',
    ];

    private static $_legalityModernList = null;

    private static $_legalityVintageList = null;

    private static $_setList = null;

    private static $_oracleImageGallery = null;

    private static $_oracleXSLT = null;

    private static $_setTranslations = [
        // MKM
        'Battle Royale' => 'Battle Royale Box Set',
        'Beatdown' => 'Beatdown Box Set',
        'Sixth Edition' => 'Classic Sixth Edition',
        'Commander 2013' => 'Commander 2013 Edition',
        'Duel Decks: Phyrexia vs. The Coalition' => 'Duel Decks: Phyrexia vs. the Coalition',
        'From the Vault: Annihilation' => 'From the Vault: Annihilation (2014)',
        'Alpha' => 'Limited Edition Alpha',
        'Beta' => 'Limited Edition Beta',
        'Magic 2014' => 'Magic 2014 Core Set',
        'Magic 2015' => 'Magic 2015 Core Set',
        'Commander' => 'Magic: The Gathering-Commander',
        'Conspiracy' => 'Magic: The Gathering—Conspiracy',
        'Kaladesh Inventions' => 'Masterpiece Series: Kaladesh Inventions',
        'Modern Masters 2015' => 'Modern Masters 2015 Edition',
        'Planechase 2012' => 'Planechase 2012 Edition',
        'Premium Deck Series: Fire & Lightning' => 'Premium Deck Series: Fire and Lightning',
        'Revised' => 'Revised Edition',
        'Ugin\'s Fate Promos' => 'Ugin\'s Fate promos',
        'Unlimited' => 'Unlimited Edition',
        
        // Magicinfo (?)
        '10th Edition' => 'Tenth Edition',
        '9th Edition' => 'Tenth Edition',
        '8th Edition' => 'Eighth Edition',
        '7th Edition' => 'Seventh Edition',
        '6th Edition' => 'Classic Sixth Edition',
        '5th Edition' => 'Fifth Edition',
        '4th Edition' => 'Fourth Edition',
        'Alpha (Limited Edition)' => 'Limited Edition Alpha',
        'Beta (Limited Edition)' => 'Limited Edition Beta'
    ];

    private static $_setMapping = [
        'mps_akh' => 'mpsakh',
        'mps_kld' => 'mpskld',
        'ddd' => 'gvl',
        'dd3_evg' => 'evg',
        'dd3_gvl' => 'gvl',
        'dd3_dvd' => 'dvd',
        'dd3_jvc' => 'jvc',
        'dd2' => 'jvc',
        'frf_ugin' => 'ugin',
        'al' => 'ai',
        'le' => 'lg',
        'mi' => 'mr',
        'st' => 'sh',
        'ppr' => 'mbp',
        '1e' => 'al',
        '2e' => 'be',
        '2u' => 'un',
        '3e' => 'rv',
        '8ed' => '8e',
        '9ed' => '9e',
        'cm1' => 'cma',
        'cg' => 'ud',
        'con' => 'cfx',
        'dst' => 'ds',
        'gpt' => 'gp',
        'gu' => 'ul',
        'dis' => 'di',
        'mor' => 'mt',
        'lrw' => 'lw',
        'h09' => 'pds',
        'p2' => 'po2',
        
        'csp' => 'cs',
        'dde' => 'pvc',
        'ddc' => 'dvd',
        'drb' => 'fvd',
        'hop' => 'pch',
        'jud' => 'ju',
        'hm' => 'hl',
        'lgn' => 'le',
        'mrd' => 'mi',
        'ons' => 'on',
        'plc' => 'pc',
        'tor' => 'tr',
        'p4' => 'st2k',
        'p3' => 'st',
        'tsp' => 'ts',
        'pk' => 'p3k',
        'scg' => 'sc',
        'v10' => 'fvr',
        'te' => 'tp',
        'tsb' => 'tsts',
        'v09' => 'fve',
        'v11' => 'fvl',
        'unh' => 'uh',
        'uz' => 'us'
    ];

    private static $_dom;

    private static function domHelper()
    {
        if (! self::$_dom) {
            self::$_dom = new DOMHelper();
        }
        return self::$_dom;
    }

    public static function getTypeFragment(DOMDocument $dataDoc)
    {
        static $retFragment = null;
        if (! $retFragment) {
            $xml = '';
            foreach (self::$_typeList as $name) {
                $xml .= sprintf('<type>%s</type>', $name);
            }
            $retFragment = self::domHelper()->parse($xml);
        }
        return $dataDoc->importNode($retFragment, true);
    }

    public static function getRarityFragment(DOMDocument $dataDoc)
    {
        static $retFragment = null;
        if (! $retFragment) {
            $xml = '';
            foreach (self::$_rarityList as $name) {
                $xml .= sprintf('<rarity>%s</rarity>', $name);
            }
            $retFragment = self::domHelper()->parse($xml);
        }
        return $dataDoc->importNode($retFragment, true);
    }

    public static function getColorFragment(DOMDocument $dataDoc)
    {
        static $retFragment = null;
        if (! $retFragment) {
            $xml = '';
            foreach (self::$_colorList as $name) {
                $xml .= sprintf('<color>%s</color>', $name);
            }
            $retFragment = self::domHelper()->parse($xml);
        }
        return $dataDoc->importNode($retFragment, true);
    }

    public static function getSetFragment(DOMDocument $dataDoc)
    {
        static $retFragment = null;
        if (! $retFragment) {
            $setList = self::getSetList();
            $xml = '';
            foreach ($setList as $set) {
                $time = strtotime($set['date']);
                $xml .= sprintf('<set href="%s" date="%s" date-year="%s" date-month="%s" date-day="%s" type="%s">%s</set>', $set['href'], $set['date'], date('Y', $time), date('m', $time), date('d', $time), $set['type'], $set['name']);
            }
            $retFragment = self::domHelper()->parse($xml);
        }
        return $dataDoc->importNode($retFragment, true);
    }

    public static function getLegalityFragment(DOMDocument $dataDoc)
    {
        static $retFragment = null;
        if (! $retFragment) {
            $nameList = self::$_legalityList;
            $xml = '';
            foreach ($nameList as $name) {
                $xml .= sprintf('<legality>%s</legality>', $name);
            }
            $retFragment = self::domHelper()->parse($xml);
        }
        return $dataDoc->importNode($retFragment, true);
    }

    // Legality...
    public static function getCardLegality(array &$card)
    {
        $ret = [];
        foreach (self::$_legalityList as $legality) {
            $legal = false;
            switch ($legality) {
                case 'Standard':
                    $legal = self::isStandardLegal($card);
                    break;
                case 'Modern':
                    $legal = self::isModernLegal($card);
                    break;
                case 'Commander':
                    $legal = self::isCommanderLegal($card);
                    break;
                case 'Vintage':
                    $legal = self::isVintageLegal($card);
                    break;
            }
            if ($legal) {
                $ret[] = $legality;
            }
        }
        return $ret;
    }

    public static function isStandardLegal(array &$card)
    {
        return in_array($card['expansion_name'], self::$_legalityStandardList);
    }

    public static function getStandardLegalList()
    {
        return self::$_legalityStandardList;
    }

    public static function isModernLegal(array &$card)
    {
        self::_initLegalityModernList();
        return in_array($card['expansion_name'], self::$_legalityModernList);
    }

    public static function getModernLegalList()
    {
        self::_initLegalityModernList();
        return self::$_legalityModernList;
    }

    protected static function _initLegalityModernList()
    {
        if (self::$_legalityModernList === null) {
            self::$_legalityModernList = self::$_legalityStandardList;
            if ($xpath = Storage::loadExternalXPath(self::URL_LEGALITY_MODERN, Seconds::MONTH)) {
                $nodeList = $xpath->evaluate('//*[@class="list"]//em');
                foreach ($nodeList as $node) {
                    $setName = $xpath->evaluate('normalize-space(.)', $node);
                    // $setName = trim(preg_replace('/\s+/u', ' ', $setName));
                    $setName = self::translateSetName($setName);
                    if (! in_array($setName, self::$_legalityModernList)) {
                        self::$_legalityModernList[] = $setName;
                    }
                }
            }
        }
    }

    public static function getVintageLegalList()
    {
        self::_initLegalityVintageList();
        return self::$_legalityVintageList;
    }

    protected static function _initLegalityVintageList()
    {
        if (self::$_legalityVintageList === null) {
            self::$_legalityVintageList = [];
            $setList = self::getSetList();
            foreach ($setList as $set) {
                self::$_legalityVintageList[] = $set['name'];
            }
        }
    }

    public static function getSetList()
    {
        self::_initSetList();
        return self::$_setList;
    }

    protected static function _initSetList()
    {
        if (self::$_setList === null) {
            $_setXPathTable = '//h2[.//*[@id="List_of_Magic_expansions_and_sets"]]/following::table[1]';
            $_setXPathCell = './/tr/td[2]';
            $setList = [];
            if ($xpath = Storage::loadExternalXPath(self::URL_LEGALITY_VINTAGE, Seconds::MONTH)) {
                // output($xpath->document);die();
                $tableNodeList = $xpath->evaluate($_setXPathTable);
                foreach ($tableNodeList as $tableNode) {
                    $cellNodeList = $xpath->evaluate($_setXPathCell, $tableNode);
                    foreach ($cellNodeList as $cellNode) {
                        $set = $xpath->evaluate('normalize-space(.)', $cellNode);
                        $href = $xpath->evaluate('normalize-space(.//@href)', $cellNode);
                        $date = $xpath->evaluate('normalize-space(preceding-sibling::td[1])', $cellNode);
                        $date .= '-01';
                        $type = $xpath->evaluate('normalize-space(following-sibling::td[3])', $cellNode);
                        $type = strtolower($type);
                        // my_dump([$set, $type]);
                        if (strlen($set) and strlen($type)) {
                            switch ($type) {
                                case 'compilation set':
                                case 'box set':
                                case 'supplemetal set':
                                case 'supplemental set':
                                case 'un-set':
                                case 'starter set':
                                    break;
                                case 'expansion set':
                                case 'core set':
                                    $href = self::URL_LEGALITY_VINTAGE . '/..' . $href;
                                    if ($setXPath = Storage::loadExternalXPath($href, Seconds::MONTH)) {
                                        if ($time = $setXPath->evaluate('normalize-space(//tr[normalize-space(td[1]) = "Release date"]/td[2]/text())')) {
                                            if ($time = strtotime($time)) {
                                                $date = date('Y-m-d', $time);
                                            }
                                        }
                                    }
                                    $setList[] = [
                                        'name' => self::translateSetName($set),
                                        'date' => $date,
                                        'type' => $type,
                                        'href' => $href
                                    ];
                                    break;
                                default:
                                    throw new Exception(sprintf('Unknown set type? "%s"', $type));
                            }
                        }
                    }
                }
            } else {
				throw new Exception(sprintf('no XML document at %s', self::URL_LEGALITY_VINTAGE));
			}
            self::$_setList = array_reverse($setList);
        }
    }

    public static function isCommanderLegal(array &$card)
    {
        return true;
    }

    public static function isVintageLegal(array &$card)
    {
        return true;
    }

    public static function translateSetName($setName)
    {
        return isset(self::$_setTranslations[$setName]) ? self::$_setTranslations[$setName] : $setName;
    }

    // Type...
    public static function getCardTypeName(array &$card)
    {
        $type = strpos($card['type'], 'Token') === false ? $card['type'] : 'Token';
        foreach (self::$_typeList as $name) {
            if (strpos($type, $name) !== false) {
                break;
            }
        }
        return $name;
    }

    public static function getCardTypeIndex(array &$card)
    {
        $type = strpos($card['type'], 'Token') === false ? $card['type'] : 'Token';
        foreach (self::$_typeList as $i => $name) {
            if (strpos($type, $name) !== false) {
                break;
            }
        }
        return $i;
    }

    public static function isCardToken(array &$card)
    {
        return self::getCardTypeIndex($card) > 6;
    }

    // Rarity...
    public static function getCardRarityName(array &$card)
    {
        $ret = 0;
        foreach (self::$_rarityList as $i => $name) {
            if ($card['rarity'] === $name) {
                $ret = $i;
                break;
            }
        }
        return self::$_rarityList[$ret];
    }

    public static function getCardRarityIndex(array &$card)
    {
        $ret = 0;
        foreach (self::$_rarityList as $i => $name) {
            if ($card['rarity'] === $name) {
                $ret = $i;
                break;
            }
        }
        return $ret;
    }

    public static function getCardColorList(array &$card)
    {
        $retList = [];
        foreach (self::$_colorList as $color) {
            $retList[$color] = 0;
        }
        $isLand = self::getCardTypeIndex($card) === 0;
        $costText = $isLand ? $card['description'] : $card['cost'];
        if (strlen($costText) === 1) {
            $costText = sprintf('{%s}', $costText);
        }
        if (preg_match_all('/{([^}]+)}/', $costText, $matchList)) {
            foreach ($matchList[1] as $match) {
                $match = explode('/', $match);
                $colorCount = count($match);
                foreach ($match as $c) {
                    $key = null;
                    $val = 1;
                    if (preg_match('/\d+/', $c)) {
                        $val = (int) $c;
                        $key = self::COLOR_NO;
                        if ($isLand) {
                            $val = 0;
                        }
                    } else {
                        $c = substr($c, 0, 1);
                        if ($c = self::getColorName($c)) {
                            $key = $c;
                        }
                    }
                    if ($key) {
                        $retList[$key] += $val / $colorCount;
                    }
                }
            }
        }
        return $retList;
    }

    public static function getCardCMC(array &$card)
    {
        $ret = 0;
        if (isset($card['cost'])) {
            if (preg_match_all('/{([^}]+)}/', $card['cost'], $matchList)) {
                foreach ($matchList[1] as $match) {
                    if (preg_match('/^\d+$/', $match)) {
                        $ret += (int) $match;
                    } else {
                        if ($match !== 'X') {
                            $ret ++;
                        }
                    }
                }
            }
        }
        return $ret;
    }

    public static function getCardColors(array &$card)
    {
        $ret = self::getCardColorList($card);
        unset($ret['Colorless']);
        foreach ($ret as &$val) {
            $val = ($val > 0) ? 1 : 0;
        }
        unset($val);
        return array_sum($ret);
    }

    public static function getColorIndex($colorName)
    {
        foreach (self::$_colorList as $i => $name) {
            if ($colorName === $name) {
                break;
            }
        }
        return $i;
    }

    public static function getColorKey($colorName)
    {
        $ret = null;
        switch ($colorName) {
            case self::COLOR_NO:
                $ret = 'C';
                break;
            case self::COLOR_W:
                $ret = 'W';
                break;
            case self::COLOR_U:
                $ret = 'U';
                break;
            case self::COLOR_B:
                $ret = 'B';
                break;
            case self::COLOR_R:
                $ret = 'R';
                break;
            case self::COLOR_G:
                $ret = 'G';
                break;
        }
        return $ret;
    }

    public static function getColorName($colorKey)
    {
        $ret = null;
        switch ($colorKey) {
            case 'C':
                $ret = self::COLOR_NO;
                break;
            case 'W':
                $ret = self::COLOR_W;
                break;
            case 'U':
                $ret = self::COLOR_U;
                break;
            case 'B':
                $ret = self::COLOR_B;
                break;
            case 'R':
                $ret = self::COLOR_R;
                break;
            case 'G':
                $ret = self::COLOR_G;
                break;
        }
        return $ret;
    }

    public static function getMarketOAuth()
    {
        $authority = MKM::getDefaultAuthority();
        return [
            'appToken' => $authority->appToken,
            'appSecret' => $authority->appSecret,
            'accessToken' => $authority->accessToken,
            'accessSecret' => $authority->accessSecret
        ];
    }

    public static function getNameKey($name)
    {
        $name = strtolower($name);
        $name = str_replace([
            '!',
            '"',
            '”',
            '“'
        ], '', $name);
        if (preg_match('/^(.+)\((.+\/.+)\)/', $name, $match)) {
            $name = $match[1];
        }
        if (preg_match('/^(.+) \/\//', $name, $match)) {
            $name = $match[1];
        }
        $name = str_replace('who/what/when/where/why', 'who', $name);
        $name = trim($name);
        return $name;
    }

    public static function getOracleURL(array &$card)
    {
        return $card['oracle_id'] > 0 ? sprintf(self::URL_ORACLE, $card['oracle_id']) : null;
    }

    public static function getOracleCardData(array &$card)
    {
        self::_initOracleXSLT();
        $ret = null;
        if ($url = self::getOracleURL($card)) {
            if ($dataDoc = Storage::loadExternalDocument($url, Seconds::MONTH)) {
                $resDoc = self::$_oracleXSLT->transformToDoc($dataDoc);
                if ($cardNode = $resDoc->documentElement) {
                    $ret = [];
                    foreach ($cardNode->childNodes as $node) {
                        $ret[$node->tagName] = $node->textContent;
                    }
                }
            } else {
                trigger_error('Could not load Oracle URL: ' . $url);
            }
        }
        return $ret;
    }

    protected static function _initOracleXSLT()
    {
        if (self::$_oracleXSLT === null) {
            self::$_oracleXSLT = new XSLTProcessor();
            self::$_oracleXSLT->registerPHPFunctions();
            self::$_oracleXSLT->importStylesheet(self::domHelper()->load(self::URL_TEMPLATE_ORACLE));
        }
    }

    public static function getOracleImageURL(array &$card)
    {
        self::_initOracleImageGallery();
        $ret = null;
        if (isset($card['oracle_id']) and $card['oracle_id'] > 0) {
            $ret = sprintf(self::URL_ORACLE_IMAGE, $card['oracle_id']);
        }
        if (isset($card['name'], $card['expansion_name'])) {
            $cardName = $card['name'];
            $setName = $card['expansion_name'];
            if (isset(self::$_oracleImageGallery[$setName], self::$_oracleImageGallery[$setName][$cardName])) {
                $ret = self::$_oracleImageGallery[$setName][$cardName];
            }
        }
        return $ret;
    }

    protected static function _initOracleImageGallery()
    {
        if (self::$_oracleImageGallery === null) {
            self::$_oracleImageGallery = [];
            $res = Storage::loadExternalJSON(self::URL_GALLERY_HOST . self::URL_GALLERY_INDEX, Seconds::WEEK);
            if ($res) {
                $dom = self::domHelper();
                foreach ($res['data'] as $html) {
                    if ($node = $dom->parse($html, null, true)) {
                        $xpath = $dom->loadXPath($node->ownerDocument);
                        if ($xpath) {
                            $setName = $xpath->evaluate('normalize-space(.//h3)', $node);
                            $href = $xpath->evaluate('normalize-space(.//a/@href)', $node);
                            if ($setName and $href) {
                                $xpath = Storage::loadExternalXPath(self::URL_GALLERY_HOST . $href);
                                if ($xpath) {
                                    self::$_oracleImageGallery[$setName] = [];
                                    $nodeList = $xpath->evaluate('//*[@alt][starts-with(@src, "http://media.wizards.com")]');
                                    foreach ($nodeList as $node) {
                                        $cardName = $node->getAttribute('alt');
                                        $cardImage = $node->getAttribute('src');
                                        $cardName = str_replace(' /// ', ' // ', $cardName); // Amonkhet
                                        if ($cardName and $cardImage) {
                                            self::$_oracleImageGallery[$setName][$cardName] = $cardImage;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    public static function getLegalityURL(array &$card)
    {
        return sprintf(self::URL_LEGALITY, $card['oracle_id']);
    }

    public static function getSetURL(array &$card)
    {
        $abbr = $card['expansion_abbr'];
        if (isset(self::$_setMapping[$abbr])) {
            $abbr = self::$_setMapping[$abbr];
        }
        return sprintf(self::URL_SET, $abbr);
    }

    public static function getSetImageURL(array &$card)
    {
        $abbr = $card['expansion_abbr'];
        if (isset(self::$_setMapping[$abbr])) {
            $abbr = self::$_setMapping[$abbr];
        }
        $no = $card['expansion_number'];
        return sprintf(self::URL_SET_IMAGE, $abbr, $no);
    }

    public static function getPriceURL(array &$card)
    {
        return $card['oracle_id'] > 0 ? sprintf(self::URL_PRICE, rawurlencode($card['name'])) : null;
    }

    public static function getMarketURL(array &$card)
    {
        $name = $card['name'];
        $name = str_replace([
            'Æ'
        ], [
            'AE'
        ], $name);
        $name = rawurlencode($name);
        return sprintf(self::URL_MARKET, $name);
    }

    public static function getMarketSetURLList()
    {
        $ret = [];
        if ($xpath = Storage::loadExternalXPath(self::URL_MARKET_SET_INDEX, Seconds::WEEK)) {
            $nodeList = $xpath->evaluate('//*[@name="idExpansion"]/*[@value > 0]');
            foreach ($nodeList as $node) {
                $setName = $xpath->evaluate('normalize-space(.)', $node);
                // $setName = trim(preg_replace('/\s+/u', ' ', $setName));
                $ret[self::translateSetName($setName)] = sprintf(self::URL_MARKET_SET_LIST, rawurlencode($setName));
            }
        } else {
            // $ret[] = Storage::loadExternalFile(self::URL_MARKET_SET_INDEX, Seconds::MONTH);
        }
        return $ret;
    }

    public static function getCardImageName(array &$card)
    {
        return sprintf('%s-%03d.%s', $card['expansion_abbr'], $card['expansion_index'], self::EXTENSION_CARDIMAGE);
    }

    public static function getImageURL(array &$card)
    {
        // return sprintf(self::URL_IMAGE, $card['oracle_id'], $card['expansion_abbr'], $card['expansion_number']);
        /*
         * return sprintf(
         * '/getData.php/mtg/image-card?expansion_abbr=%s&expansion_index=%s',
         * $card['expansion_abbr'], $card['expansion_index']
         * );
         * //
         */
        return sprintf('/getData.php/mtg/image-card?name=%s', rawurlencode($card['name']));
    }

    public static function getImagePath(array &$card)
    {
        return sprintf('%smod/mtg/res/images/set-%s/%s', ServerEnvironment::getRootDirectory(), $card['expansion_abbr'], self::getCardImageName($card));
    }

    public static function getRarityURL(array &$card)
    {
        return sprintf('/getData.php/mtg/image-rarity?expansion_abbr=%s&rarity=%s', $card['expansion_abbr'], $card['rarity']);
    }

    public static function getRarityPath(array &$card)
    {
        return sprintf('%smod/mtg/res/images/set-%s/%s-%s.png', ServerEnvironment::getRootDirectory(), $card['expansion_abbr'], $card['expansion_abbr'], substr(strtolower($card['rarity']), 0, 1));
    }

    public static function getColorPath(array &$card)
    {
        return sprintf('%smod/mtg/res/images/color.%s.png', ServerEnvironment::getRootDirectory(), $card['color']);
    }
}