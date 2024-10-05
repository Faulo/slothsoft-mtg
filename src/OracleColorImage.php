<?php
declare(strict_types = 1);
namespace Slothsoft\MTG;

use Slothsoft\Core\IO\HTTPFile;
use Exception;

class OracleColorImage {

    protected $oracle;

    protected $imageDir;

    protected $color;

    private $_convertFunction;

    public function __construct(Oracle $oracle, $imageDir, $color) {
        $this->oracle = $oracle;
        $this->imageDir = realpath($imageDir);
        $this->color = trim(strtoupper($color));

        if (! $this->imageDir or ! strlen($this->color)) {
            throw new Exception('INVALID ARGUMENTS');
        }
        $this->imageDir .= DIRECTORY_SEPARATOR;
        $this->_convertFunction = [
            '\\Slothsoft\\Core\\Image',
            'convertFile'
        ];
        $this->_convertFunction = null;
    }

    public function getURL() {
        return sprintf('/getData.php/mtg/image?color=%s', $this->color);
    }

    public function getFile() {
        $ret = null;
        $fileDir = $this->imageDir;
        $fileName = sprintf('color.%s.png', $this->color);
        $fileName = strtolower($fileName);
        $filePath = $fileDir . $fileName;
        if (file_exists($filePath)) {
            $ret = HTTPFile::createFromPath($filePath, $fileName);
        } else {
            $url = sprintf('http://gatherer.wizards.com/Handlers/Image.ashx?size=large&name=%s&type=symbol', $this->color);
            if ($file = HTTPFile::createFromURL($url, $fileName)) {
                $ret = $file;
                $file->copyTo($fileDir, $fileName, $this->_convertFunction);
            } else {
                $url = sprintf('http://gatherer.wizards.com/Handlers/Image.ashx?size=small&name=%s&type=symbol', $this->color);
                if ($file = HTTPFile::createFromURL($url, $fileName)) {
                    $ret = $file;
                    // $file->copyTo($fileDir, $fileName, $this->_convertFunction);
                }
            }
        }
        return $ret;
    }
}