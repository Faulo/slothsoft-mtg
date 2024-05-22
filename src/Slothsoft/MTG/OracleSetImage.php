<?php
declare(strict_types = 1);
namespace Slothsoft\MTG;

use Slothsoft\Core\Image;
use Slothsoft\Core\IO\HTTPFile;
use Exception;

class OracleSetImage
{

    protected $oracle;

    protected $imageDir;

    protected $setAbbr;

    public function __construct(Oracle $oracle, $imageDir, $setAbbr)
    {
        $this->oracle = $oracle;
        $this->imageDir = realpath($imageDir);
        $this->setAbbr = strtolower(trim($setAbbr));
        
        if (! $this->imageDir or ! strlen($this->setAbbr)) {
            throw new Exception('INVALID ARGUMENTS');
        }
        $this->imageDir .= DIRECTORY_SEPARATOR;
    }

    public function getFile($recreate = false)
    {
        $fileDir = $this->imageDir;
        $fileName = sprintf('set.%s.png', $this->setAbbr);
        $fileName = strtolower($fileName);
        $filePath = $fileDir . '_sets' . DIRECTORY_SEPARATOR . $fileName;
        if ($recreate or ! file_exists($filePath)) {
            // $thumbWidth = 78;
            $thumbWidth = 312;
            // $thumbHeight = 111;
            $thumbHeight = 445;
            $widthCount = 32;
            $heightCount = 12;
            $imageList = [];
            $idTable = $this->oracle->getIdTable();
            $resList = $idTable->getCardListBySetAbbr($this->setAbbr);
            foreach ($resList as $res) {
                if ($image = $this->oracle->getCardImage($this->imageDir, $res['oracle_id'], $res['expansion_abbr'], $res['expansion_number'])) {
                    if ($file = $image->getFile()) {
                        // my_dump($file);
                        if ($path = $file->getPath()) {
                            if ($thumbPath = Image::generateThumbnail($path, $thumbWidth, $thumbHeight, false)) {
                                $imageList[$res['expansion_index']] = $thumbPath;
                            }
                        }
                    }
                }
            }
            if ($imageList) {
                Image::createSprite($filePath, $thumbWidth, $thumbHeight, $widthCount, $heightCount, $imageList);
            }
        }
        return file_exists($filePath) ? HTTPFile::createFromPath($filePath, $fileName) : null;
    }
}