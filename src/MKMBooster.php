<?php
declare(strict_types = 1);
namespace Slothsoft\MTG;

use DOMDocument;
use DOMElement;
use DOMException;

class MKMBooster {
    
    protected MKMShop $ownerShop;
    
    protected array $data = [
        'id' => 0,
        'name' => '',
        'price' => 0.0,
        'set' => '',
        'format' => '',
        'uri' => '',
        'language' => '',
        'country' => ''
    ];
    
    public function __construct(MKMShop $shop, array $data) {
        $this->ownerShop = $shop;
        $this->setData($data);
    }
    
    public function getName(): string {
        return $this->data['name'];
    }
    
    public function getPrice(): float {
        return $this->data['price'];
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
        $retNode = $doc->createElement('booster');
        foreach ($this->data as $key => $val) {
            $retNode->setAttribute($key, $val);
        }
        return $retNode;
    }
}