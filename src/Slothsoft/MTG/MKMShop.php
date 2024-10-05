<?php
declare(strict_types = 1);
namespace Slothsoft\MTG;

use DOMDocument;

class MKMShop {

    protected $ownerMKM;

    protected $data = [
        'name' => '',
        'uri' => '',
        'country' => ''
    ];

    protected $boosterList = [];

    public function __construct(MKM $mkm, array $data) {
        $this->ownerMKM = $mkm;
        $this->setData($data);
    }

    public function getName() {
        return $this->data['name'];
    }

    public function addBooster(array $data) {
        $booster = new MKMBooster($this, $data);
        if ($oldBooster = $this->getBoosterByName($data['name'])) {
            if ($booster->getPrice() >= $oldBooster->getPrice()) {
                return $oldBooster;
            }
        }
        $this->boosterList[] = $booster;
        return $booster;
    }

    public function getBoosterByName($name) {
        foreach ($this->boosterList as $booster) {
            if ($booster->getName() === $name) {
                return $booster;
            }
        }
        return null;
    }

    public function setData(array $data) {
        foreach ($this->data as $key => &$val) {
            if (isset($data[$key])) {
                settype($data[$key], gettype($val));
                $val = $data[$key];
            }
        }
        unset($val);
    }

    public function asNode(DOMDocument $doc) {
        $retNode = $doc->createElement('shop');
        foreach ($this->data as $key => $val) {
            $retNode->setAttribute($key, $val);
        }
        foreach ($this->boosterList as $booster) {
            $retNode->appendChild($booster->asNode($doc));
        }
        return $retNode;
    }
}