<?php
declare(strict_types = 1);
namespace Slothsoft\MTG;

use DOMDocument;

class OracleReview
{

    protected $file;

    protected $oracle;

    protected $data;

    protected $name;

    // protected $dataChanged = false;
    public function __construct($reviewFile, Oracle $oracle)
    {
        $this->file = $reviewFile;
        $this->oracle = $oracle;
        $this->data = is_readable($this->file) ? json_decode(file_get_contents($this->file), true) : null;
        $this->name = pathinfo($this->file, PATHINFO_FILENAME);
        
        if (! $this->data) {
            $this->data = [];
            // $this->updateCard('Kaladesh', 'Mind Rot', 2, '?!');
            // $this->data = ['Kaladesh' => [['name' => 'Mind Rot', 'rating' => 2, 'comment' => '...']]];
            // throw new Exception(sprintf('Empty review file?!%s%s%s%s', PHP_EOL, $this->file, PHP_EOL, file_get_contents($this->file)));
        }
    }

    public function updateData(array $setList)
    {
        $ret = 0;
        foreach ($setList as $setName => $cardList) {
            foreach ($cardList as $cardName => $card) {
                if ($this->updateCard($setName, $cardName, $card['rating'], $card['comment'])) {
                    $ret ++;
                }
            }
        }
        return $ret;
    }

    public function updateCard($setName, $cardName, $rating, $comment)
    {
        $ret = false;
        $rating = (int) $rating;
        $comment = (string) $comment;
        if (! isset($this->data[$setName])) {
            $this->data[$setName] = [];
        }
        if (! isset($this->data[$setName][$cardName])) {
            $this->data[$setName][$cardName] = [
                'rating' => null,
                'comment' => null
            ];
        }
        if ($this->data[$setName][$cardName]['rating'] !== $rating) {
            $this->data[$setName][$cardName]['rating'] = $rating;
            $ret = true;
        }
        if ($this->data[$setName][$cardName]['comment'] !== $comment) {
            $this->data[$setName][$cardName]['comment'] = $comment;
            $ret = true;
        }
        return $ret;
    }

    public function save()
    {
        return (bool) file_put_contents($this->file, json_encode($this->data));
    }

    public function getName()
    {
        return $this->name;
    }

    public function getSetList()
    {
        return array_keys($this->data);
    }

    public function asNode(DOMDocument $dataDoc = null)
    {
        $returnDocument = $dataDoc === null;
        
        if ($returnDocument) {
            $dataDoc = new DOMDocument();
        }
        
        $arr = [];
        $arr['name'] = $this->name;
        
        $retNode = $dataDoc->createElement('review');
        foreach ($arr as $key => $val) {
            $retNode->setAttribute($key, $val);
        }
        
        foreach ($this->data as $setName => $cardList) {
            $arr = [];
            $arr['name'] = $setName;
            
            $setNode = $dataDoc->createElement('set');
            foreach ($arr as $key => $val) {
                $setNode->setAttribute($key, $val);
            }
            
            foreach ($cardList as $cardName => $arr) {
                $arr['name'] = $cardName;
                
                $cardNode = $dataDoc->createElement('card');
                foreach ($arr as $key => $val) {
                    $cardNode->setAttribute($key, $val);
                }
                $setNode->appendChild($cardNode);
            }
            $retNode->appendChild($setNode);
        }
        
        if ($returnDocument) {
            $dataDoc->appendChild($retNode);
            $retNode = $dataDoc;
        }
        return $retNode;
    }
}