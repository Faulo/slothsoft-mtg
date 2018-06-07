<?php
declare(strict_types = 1);
namespace Slothsoft\MTG;

use Slothsoft\Core\IO\HTTPFile;
use Exception;

class OracleRarityImage
{

    protected $mappingList = [
        'al' => '1E',
        'be' => '2E',
        'un' => '2U',
        'rv' => '3E',
        'lg' => 'LE',
        'hl' => 'HM',
        'ai' => 'AL',
        'mr' => 'MI',
        'tp' => 'TE',
        'sh' => 'ST',
        'po2' => 'P2',
        'us' => 'UZ',
        'p3k' => 'PK',
        'ds' => 'DST',
        'on' => 'ONS',
        'ul' => 'GU',
        'ud' => 'CG',
        'tr' => 'TOR',
        'ju' => 'JUD',
        'sc' => 'SCG',
        'st2k' => 'P4',
        'uh' => 'UNH',
        'mbp' => 'PPR',
        'tsts' => 'TSB',
        'ts' => 'TSP',
        'pc' => 'PLC',
        'cs' => 'CSP',
        'me3' => 'ME3',
        'lw' => 'lrw',
        'mt' => 'mor',
        'fvd' => 'DRB',
        'cfx' => 'CON',
        'jvc' => 'DD2',
        'fve' => 'V09',
        'dvd' => 'DDC',
        'pch' => 'HOP',
        'gvl' => 'DDD',
        'me4' => 'ME4',
        'pds' => 'H09',
        'pvc' => 'DDE',
        'arc' => 'ARC',
        'fvr' => 'V10',
        'ddf' => 'DDF',
        'fvl' => 'V11',
        'v12' => 'V12',
        'cma' => 'CM1',
        'v13' => 'V13',
        '8e' => '8ED',
        '8eb' => '8ED',
        '9e' => '9ED',
        '9eb' => '9ED',
        'gp' => 'GPT',
        'di' => 'DIS'
    ];

    protected $oracle;

    protected $imageDir;

    protected $setAbbr;

    protected $rarity;

    private $_convertFunction;

    public function __construct(Oracle $oracle, $imageDir, $setAbbr, $rarity)
    {
        $this->oracle = $oracle;
        $this->imageDir = realpath($imageDir);
        $this->setAbbr = strtolower(trim($setAbbr));
        $this->rarity = trim($rarity);
        
        if (! $this->imageDir or ! strlen($this->setAbbr) or ! strlen($this->rarity)) {
            throw new Exception('INVALID ARGUMENTS');
        }
        $this->imageDir .= DIRECTORY_SEPARATOR . $this->setAbbr . DIRECTORY_SEPARATOR;
        if (! file_exists($this->imageDir)) {
            mkdir($this->imageDir, 0777, true);
        }
        $this->_convertFunction = [
            '\\Slothsoft\\Core\\Image',
            'convertFile'
        ];
        $this->_convertFunction = null;
    }

    public function getURL()
    {
        return sprintf('/getData.php/mtg/image?set=%s&rarity=%s', $this->setAbbr, $this->rarity);
    }

    public function getFile()
    {
        $ret = null;
        $fileDir = $this->imageDir;
        $fileName = sprintf('%s.%s.png', $this->setAbbr, $this->rarity);
        $fileName = strtolower($fileName);
        $filePath = $fileDir . $fileName;
        if (file_exists($filePath)) {
            $ret = HTTPFile::createFromPath($filePath, $fileName);
        } else {
            $setAbbr = isset($this->mappingList[$this->setAbbr]) ? $this->mappingList[$this->setAbbr] : $this->setAbbr;
            $url = sprintf('http://gatherer.wizards.com/Handlers/Image.ashx?type=symbol&set=%s&size=large&rarity=%s', $setAbbr, $this->rarity);
            
            if ($file = HTTPFile::createFromURL($url, $fileName)) {
                $ret = $file;
                $file->copyTo($fileDir, $fileName, $this->_convertFunction);
            } else {
                $url = sprintf('http://gatherer.wizards.com/Handlers/Image.ashx?type=symbol&set=%s&size=small&rarity=%s', $setAbbr, $this->rarity);
                if ($file = HTTPFile::createFromURL($url, $fileName)) {
                    $ret = $file;
                    // $file->copyTo($fileDir, $fileName, $this->_convertFunction);
                } else {
                    $fileName = sprintf('set.%s.png', $this->setAbbr);
                    $fileName = strtolower($fileName);
                    $filePath = $fileDir . '../' . $fileName;
                    if (file_exists($filePath)) {
                        $ret = HTTPFile::createFromPath($filePath, $fileName);
                    }
                }
            }
        }
        return $ret;
    }
}