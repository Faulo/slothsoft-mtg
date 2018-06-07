<?php
declare(strict_types = 1);
namespace Slothsoft\MTG;

use DOMDocument;

class MKMBooster
{

    protected $ownerShop;

    protected $data = [
        'id' => 0,
        'name' => '',
        'price' => 0.0,
        'set' => '',
        'format' => '',
        'uri' => '',
        'language' => '',
        'country' => ''
    ];

    public function __construct(MKMShop $shop, array $data)
    {
        $this->ownerShop = $shop;
        $this->setData($data);
    }

    public function getName()
    {
        return $this->data['name'];
    }

    public function getPrice()
    {
        return $this->data['price'];
    }

    public function setData(array $data)
    {
        foreach ($this->data as $key => &$val) {
            if (isset($data[$key])) {
                settype($data[$key], gettype($val));
                $val = $data[$key];
            }
        }
        unset($val);
    }

    public function asNode(DOMDocument $doc)
    {
        $retNode = $doc->createElement('booster');
        foreach ($this->data as $key => $val) {
            $retNode->setAttribute($key, $val);
        }
        return $retNode;
    }
}