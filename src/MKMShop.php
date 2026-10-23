<?php
declare(strict_types = 1);
namespace Slothsoft\MTG;

use DOMDocument;
use DOMElement;
use DOMException;

class MKMShop {
    
    protected MKM $ownerMKM;
    
    protected array $data = [
        'name' => '',
        'uri' => '',
        'country' => ''
    ];
    
    protected array $boosterList = [];
    
    public function __construct(MKM $mkm, array $data) {
        $this->ownerMKM = $mkm;
        $this->setData($data);
    }
    
    public function getName(): string {
        return $this->data['name'];
    }
    
    public function addBooster(array $data): MKMBooster {
        $booster = new MKMBooster($this, $data);
        if ($oldBooster = $this->getBoosterByName($data['name'])) {
            if ($booster->getPrice() >= $oldBooster->getPrice()) {
                return $oldBooster;
            }
        }
        $this->boosterList[] = $booster;
        return $booster;
    }
    
    public function getBoosterByName($name): ?MKMBooster {
        foreach ($this->boosterList as $booster) {
            if ($booster->getName() === $name) {
                return $booster;
            }
        }
        return null;
    }
    
    public function setData(array $data): void {
        foreach ($this->data as $key => &$val) {
            if (isset($data[$key])) {
                settype($data[$key], gettype($val));
                $val = $data[$key];
            }
        }
        unset($val);
    }
    
    /**
     * @throws DOMException
     */
    public function asNode(DOMDocument $doc): false|DOMElement {
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