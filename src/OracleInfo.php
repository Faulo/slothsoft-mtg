<?php
declare(strict_types = 1);
namespace Slothsoft\MTG;

use DOMDocument;
use DOMNode;
use Exception;
use Slothsoft\Core\Calendar\Seconds;
use Slothsoft\Core\DOMHelper;
use Slothsoft\Core\ServerEnvironment;
use XSLTProcessor;

class OracleInfo {
    
    const EXTENSION_CARDIMAGE = 'png';
    
    const URL_ORACLE = 'https://gatherer.wizards.com/Pages/Card/Details.aspx?multiverseid=%s';
    
    const URL_ORACLE_IMAGE = 'https://gatherer.wizards.com/Handlers/Image.ashx?multiverseid=%s&type=card';
    
    const URL_TEMPLATE_ORACLE = 'farah://slothsoft@mtg/xsl/card-gatherer';
    
    const URL_LEGALITY_MODERN = 'https://magic.wizards.com/en/gameinfo/gameplay/formats/modern';
    
    const URL_LEGALITY_VINTAGE = 'https://mtg.gamepedia.com/Set';
    
    const URL_GALLERY_HOST = 'https://magic.wizards.com';
    
    const URL_GALLERY_INDEX = '/en/search-magic-ajax?f1=section&f2=18156&sort=DESC&search=&l=en';
    
    const URL_GALLERY_EXAMPLE = '/en/articles/archive/card-image-gallery/aether-revolt';
    
    // const URL_IMAGE = '/getData.php/mtg/image?id=%s&set=%s&no=%s';
    // const URL_RARITY = '/getData.php/mtg/image?set=%s&rarity=%s'; //'https://gatherer.wizards.com/Handlers/Image.ashx?type=symbol&set=%s&size=large&rarity=%s';
    const COLOR_W = 'White';
    
    const COLOR_U = 'Blue';
    
    const COLOR_B = 'Black';
    
    const COLOR_R = 'Red';
    
    const COLOR_G = 'Green';
    
    const COLOR_NO = 'Colorless';
    
    private static array $_typeList = [
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
    
    private static array $_rarityList = [
        'Common',
        'Uncommon',
        'Rare',
        'Mythic Rare'
    ];
    
    private static array $_colorList = [
        'White',
        'Blue',
        'Black',
        'Red',
        'Green',
        'Colorless'
    ];
    
    private static array $_legalityList = [
        'Standard',
        'Modern',
        'Commander',
        // 'Legacy',
        'Vintage'
        // 'Un-Sets',
    ];
    
    private static array $_legalityStandardList = [
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
    
    private static ?array $_legalityModernList = null;
    
    private static ?array $_legalityVintageList = null;
    
    private static ?array $_setList = null;
    
    private static ?array $_oracleImageGallery = null;
    
    private static ?XSLTProcessor $_oracleXSLT = null;
    
    private static array $_setTranslations = [
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
    
    private static ?DOMHelper $_dom = null;
    
    private static function domHelper(): DOMHelper {
        if (! self::$_dom) {
            self::$_dom = new DOMHelper();
        }
        return self::$_dom;
    }
    
    public static function getTypeFragment(DOMDocument $dataDoc): DOMNode|false|null {
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
    
    public static function getRarityFragment(DOMDocument $dataDoc): DOMNode|false|null {
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
    
    public static function getColorFragment(DOMDocument $dataDoc): DOMNode|false|null {
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
    
    /**
     * @throws Exception
     */
    public static function getSetFragment(DOMDocument $dataDoc): DOMNode|false|null {
        static $retFragment = null;
        if (! $retFragment) {
            $doc = new DOMDocument();
            $retFragment = $doc->createDocumentFragment();
            foreach (self::getSetList() as $set) {
                $time = strtotime($set['date']);
                $node = $doc->createElement('set');
                $attributes = [
                    'href' => $set['href'],
                    'date' => $set['date'],
                    'date-year' => date('Y', $time),
                    'date-month' => date('m', $time),
                    'date-day' => date('d', $time),
                    'type' => $set['type']
                ];
                foreach ($attributes as $name => $value) {
                    $node->setAttribute($name, $value);
                }
                $node->appendChild($doc->createTextNode($set['name']));
                $retFragment->appendChild($node);
            }
        }
        return $dataDoc->importNode($retFragment, true);
    }

    public static function getLegalityFragment(DOMDocument $dataDoc): DOMNode|false|null {
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

    /**
     * @throws Exception
     */
    public static function getCardLegality(array $card): array {
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
                    $legal = self::isCommanderLegal();
                    break;
                case 'Vintage':
                    $legal = self::isVintageLegal();
                    break;
            }
            if ($legal) {
                $ret[] = $legality;
            }
        }
        return $ret;
    }
    
    public static function isStandardLegal(array $card): bool {
        return in_array($card['expansion_name'], self::$_legalityStandardList);
    }
    
    public static function getStandardLegalList(): array {
        return self::$_legalityStandardList;
    }
    
    /**
     * @throws Exception
     */
    public static function isModernLegal(array $card): bool {
        self::_initLegalityModernList();
        return in_array($card['expansion_name'], self::$_legalityModernList);
    }
    
    /**
     * @throws Exception
     */
    public static function getModernLegalList(): ?array {
        self::_initLegalityModernList();
        return self::$_legalityModernList;
    }
    
    /**
     * @throws Exception
     */
    protected static function _initLegalityModernList(): void {
        if (self::$_legalityModernList === null) {
            self::$_legalityModernList = self::$_legalityStandardList;
            if ($xpath = (new RemoteDocumentLoader())->loadXPath(self::URL_LEGALITY_MODERN, Seconds::MONTH)) {
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
    
    /**
     * @throws Exception
     */
    public static function getVintageLegalList(): ?array {
        self::_initLegalityVintageList();
        return self::$_legalityVintageList;
    }
    
    /**
     * @throws Exception
     */
    protected static function _initLegalityVintageList(): void {
        if (self::$_legalityVintageList === null) {
            self::$_legalityVintageList = [];
            $setList = self::getSetList();
            foreach ($setList as $set) {
                self::$_legalityVintageList[] = $set['name'];
            }
        }
    }
    
    /**
     * @throws Exception
     */
    public static function getSetList(): ?array {
        self::_initSetList();
        return self::$_setList;
    }
    
    /**
     * @throws Exception
     */
    protected static function _initSetList(): void {
        if (self::$_setList === null) {
            $_setXPathTable = '//h2[.//*[@id="List_of_Magic_expansions_and_sets"]]/following::table[1]';
            $_setXPathCell = './/tr/td[2]';
            $setList = [];
            if ($xpath = (new RemoteDocumentLoader())->loadXPath(self::URL_LEGALITY_VINTAGE, Seconds::MONTH)) {
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
                                    if ($setXPath = (new RemoteDocumentLoader())->loadXPath($href, Seconds::MONTH)) {
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
    
    public static function isCommanderLegal(): true {
        return true;
    }
    
    public static function isVintageLegal(): true {
        return true;
    }
    
    public static function translateSetName(string $setName): string {
        return self::$_setTranslations[$setName] ?? $setName;
    }
    
    // Type...
    public static function getCardTypeName(array $card): string {
        $type = ! str_contains($card['type'], 'Token') ? $card['type'] : 'Token';
        $name = 'Other';
        foreach (self::$_typeList as $name) {
            if (str_contains($type, $name)) {
                break;
            }
        }
        return $name;
    }
    
    public static function getCardTypeIndex(array $card): int {
        $type = ! str_contains($card['type'], 'Token') ? $card['type'] : 'Token';
        $i = count(self::$_typeList) - 1;
        foreach (self::$_typeList as $i => $name) {
            if (str_contains($type, $name)) {
                break;
            }
        }
        return $i;
    }
    
    public static function isCardToken(array $card): bool {
        return self::getCardTypeIndex($card) > 6;
    }
    
    // Rarity...
    public static function getCardRarityName(array $card): string {
        $ret = 0;
        foreach (self::$_rarityList as $i => $name) {
            if ($card['rarity'] === $name) {
                $ret = $i;
                break;
            }
        }
        return self::$_rarityList[$ret];
    }
    
    public static function getCardRarityIndex(array $card): int {
        $ret = 0;
        foreach (self::$_rarityList as $i => $name) {
            if ($card['rarity'] === $name) {
                $ret = $i;
                break;
            }
        }
        return $ret;
    }
    
    public static function getCardColorList(array $card): array {
        $retList = [];
        foreach (self::$_colorList as $color) {
            $retList[$color] = 0;
        }
        $isLand = self::getCardTypeIndex($card) === 0;
        $costText = $isLand ? $card['description'] : $card['cost'];
        if (strlen($costText) === 1) {
            $costText = sprintf('{%s}', $costText);
        }
        $matchList = null;
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
    
    public static function getCardCMC(array $card): int {
        $ret = 0;
        if (isset($card['cost'])) {
            $matchList = null;
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
    
    public static function getCardColors(array $card): float|int {
        $ret = self::getCardColorList($card);
        unset($ret['Colorless']);
        foreach ($ret as &$val) {
            $val = ($val > 0) ? 1 : 0;
        }
        unset($val);
        return array_sum($ret);
    }
    
    public static function getColorIndex($colorName): int {
        $i = count(self::$_colorList) - 1;
        foreach (self::$_colorList as $i => $name) {
            if ($colorName === $name) {
                break;
            }
        }
        return $i;
    }
    
    private const COLOR_NAMES = [
        'C' => self::COLOR_NO,
        'W' => self::COLOR_W,
        'U' => self::COLOR_U,
        'B' => self::COLOR_B,
        'R' => self::COLOR_R,
        'G' => self::COLOR_G
    ];

    public static function getColorKey(string $colorName): ?string {
        $key = array_search($colorName, self::COLOR_NAMES, true);
        return $key === false ? null : $key;
    }

    public static function getColorName(string $colorKey): ?string {
        return self::COLOR_NAMES[$colorKey] ?? null;
    }

    public static function getNameKey($name): string {
        $name = strtolower($name);
        $name = str_replace([
            '!',
            '"',
            '”',
            '“'
        ], '', $name);
        $match = null;
        if (preg_match('/^(.+)\((.+\/.+)\)/', $name, $match)) {
            $name = $match[1];
        }
        if (preg_match('/^(.+) \/\//', $name, $match)) {
            $name = $match[1];
        }
        $name = str_replace('who/what/when/where/why', 'who', $name);
        return trim($name);
    }
    
    public static function getOracleURL(array $card): ?string {
        return $card['oracle_id'] > 0 ? sprintf(self::URL_ORACLE, $card['oracle_id']) : null;
    }
    
    /**
     * @throws Exception
     */
    public static function getOracleCardData(array $card): ?array {
        self::_initOracleXSLT();
        $ret = null;
        if ($url = self::getOracleURL($card)) {
            if ($dataDoc = (new RemoteDocumentLoader())->loadDocument($url, Seconds::MONTH)) {
                $resDoc = self::$_oracleXSLT->transformToDoc($dataDoc);
                if ($cardNode = $resDoc->documentElement) {
                    $ret = [];
                    foreach ($cardNode->childNodes as $node) {
                        $ret[$node->tagName] = $node->textContent;
                    }
                } else {
                    trigger_error('Could not transform Oracle URL: ' . $url);
                }
            } else {
                trigger_error('Could not load Oracle URL: ' . $url);
            }
        }
        return $ret;
    }
    
    /**
     * @throws Exception
     */
    protected static function _initOracleXSLT(): void {
        if (self::$_oracleXSLT === null) {
            self::$_oracleXSLT = new XSLTProcessor();
            self::$_oracleXSLT->registerPHPFunctions();
            $success = self::$_oracleXSLT->importStylesheet(self::domHelper()->load(self::URL_TEMPLATE_ORACLE));
            if (! $success) {
                throw new Exception('Failed to load template: ' . self::URL_TEMPLATE_ORACLE);
            }
        }
    }
    
    /**
     * @throws Exception
     */
    public static function getOracleImageURL(array $card): ?string {
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
    
    /**
     * @throws Exception
     */
    protected static function _initOracleImageGallery(): void {
        if (self::$_oracleImageGallery === null) {
            self::$_oracleImageGallery = [];
            $res = (new RemoteDocumentLoader())->loadJson(self::URL_GALLERY_HOST . self::URL_GALLERY_INDEX, Seconds::WEEK);
            if ($res) {
                $dom = self::domHelper();
                foreach ($res['data'] as $html) {
                    if ($node = $dom->parse($html, null, true)) {
                        $xpath = $dom->loadXPath($node->ownerDocument);
                        $setName = $xpath->evaluate('normalize-space(.//h3)', $node);
                        $href = $xpath->evaluate('normalize-space(.//a/@href)', $node);
                        if ($setName and $href) {
                            $xpath = (new RemoteDocumentLoader())->loadXPath(self::URL_GALLERY_HOST . $href);
                            if ($xpath) {
                                self::$_oracleImageGallery[$setName] = [];
                                $nodeList = $xpath->evaluate('//*[@alt][starts-with(substring-after(@src, "://"), "media.wizards.com/")]');
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
    
    public static function getCardImageName(array $card): string {
        return strtolower(sprintf('%s-%03d.%s', $card['expansion_abbr'], $card['expansion_index'], self::EXTENSION_CARDIMAGE));
    }
    
    public static function getImageURL(array $card): string {
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
    
    public static function getImagePath(array $card): string {
        return sprintf('%s/images/set-%s/%s', ServerEnvironment::getDataDirectory(), strtolower($card['expansion_abbr']), self::getCardImageName($card));
    }
    
    public static function getRarityURL(array $card): string {
        return sprintf('/getData.php/mtg/image-rarity?expansion_abbr=%s&rarity=%s', strtolower($card['expansion_abbr']), strtolower($card['rarity']));
    }
    
    public static function getRarityPath(array $card): string {
        return sprintf('%s/images/set-%s/%s-%s.png', ServerEnvironment::getDataDirectory(), strtolower($card['expansion_abbr']), strtolower($card['expansion_abbr']), substr(strtolower($card['rarity']), 0, 1));
    }
    
    public static function getColorPath(array $card): string {
        return sprintf('%s/mtg/images/color.%s.png', ServerEnvironment::getRootDirectory(), strtolower($card['color']));
    }
}
