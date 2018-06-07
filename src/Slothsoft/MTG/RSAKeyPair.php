<?php
declare(strict_types = 1);
namespace Slothsoft\MTG;

class RSAKeyPair
{

    public $encryptionExponent;

    public $decryptionExponent;

    public $modulus;

    public $digitSize;

    public $chunkSize;

    public $radix;

    public $barrett;

    public function __construct($encryptionExponent, $decryptionExponent, $modulus)
    {
        $this->encryptionExponent = BigInt::createFromHex($encryptionExponent);
        $this->decryptionExponent = BigInt::createFromHex($decryptionExponent);
        $this->modulus = BigInt::createFromHex($modulus);
        
        $this->digitSize = 2 * $this->modulus->getHighIndex() + 2;
        $this->chunkSize = $this->digitSize - 11;
        
        $this->radix = 16;
        // $this->barrett = new BarrettMu($this->modulus);
    }

    public function encryptUser($user, $password, $hash)
    {
        $ret = sprintf('%s\\%s\\%s', $hash, base64_encode($user), base64_encode($password));
        return $this->encryptString($ret);
    }

    public function encryptString($input)
    {
        $ret = [];
        for ($i = 0, $inputLength = strlen($input); $i < $inputLength; $i += $this->chunkSize) {
            $msgLength = ($i + $this->chunkSize) > $inputLength ? $inputLength % $this->chunkSize : $this->chunkSize;
            
            $b = [];
            for ($x = 0; $x < $msgLength; $x ++) {
                $b[$x] = $input[$i + $msgLength - 1 - $x];
            }
            $b[$msgLength] = 0;
            $paddedSize = max(8, $this->digitSize - 3 - $msgLength);
            for ($x = 0; $x < $paddedSize; $x ++) {
                $b[$msgLength + 1 + $x] = rand(1, 255);
            }
            $b[$this->digitSize - 2] = 2;
            $b[$this->digitSize - 1] = 0;
            
            $block = BigInt::createFromDec(0);
            for ($j = 0, $k = 0; $k < $this->digitSize; $j ++) {
                $block->digits[$j] = $b[$k ++];
                $block->digits[$j] += $b[$k ++] << 8;
            }
            $crypt = $block->powMod($this->modulus, $this->encryptionExponent);
            $ret[] = $crypt->toHex();
            // var crypt = $this->barrett.powMod(block, $this->e);
            // var text = $this->radix == 16 ? biToHex(crypt) : biToString(crypt, $this->radix);
        }
        return implode(' ', $ret);
        /*
         *
         * var a = new Array();
         * var sl = s.length;
         *
         * var i = 0;
         * while (i < sl) {
         * a[i] = s.charCodeAt(i);
         * i++;
         * }
         *
         * //while (a.length % $this->chunkSize != 0) {
         * // a[i++] = 0;
         * //}
         *
         * var al = a.length;
         * var result = "";
         * var j, k, block;
         * for (i = 0; i < al; i += $this->chunkSize) {
         * block = new BigInt();
         * j = 0;
         *
         * //for (k = i; k < i + $this->chunkSize; ++j) {
         * // block.digits[j] = a[k++];
         * // block.digits[j] += a[k++] << 8;
         * //}
         *
         * ////////////////////////////////// TYF
         * // Add PKCS#1 v1.5 padding
         * // 0x00 || 0x02 || PseudoRandomNonZeroBytes || 0x00 || Message
         * // Variable a before padding must be of at most digitSize-11
         * // That is for 3 marker bytes plus at least 8 random non-zero bytes
         * var x;
         * var msgLength = (i + $this->chunkSize) > al ? al % $this->chunkSize : $this->chunkSize;
         *
         * // Variable b with 0x00 || 0x02 at the highest index.
         * var b = new Array();
         * for (x = 0; x < msgLength; x++) {
         * b[x] = a[i + msgLength - 1 - x];
         * }
         * b[msgLength] = 0; // marker
         * var paddedSize = Math.max(8, $this->digitSize - 3 - msgLength);
         *
         * for (x = 0; x < paddedSize; x++) {
         * b[msgLength + 1 + x] = Math.floor(Math.random() * 254) + 1; // [1,255]
         * }
         * // It can be asserted that msgLength+paddedSize == $this->digitSize-3
         * b[$this->digitSize - 2] = 2; // marker
         * b[$this->digitSize - 1] = 0; // marker
         *
         * for (k = 0; k < $this->digitSize; ++j) {
         * block.digits[j] = b[k++];
         * block.digits[j] += b[k++] << 8;
         * }
         * ////////////////////////////////// TYF
         *
         * var crypt = $this->barrett.powMod(block, $this->e);
         * var text = $this->radix == 16 ? biToHex(crypt) : biToString(crypt, $this->radix);
         * result += text + " ";
         * }
         * return result.substring(0, result.length - 1); // Remove last space.
         * //
         */
    }
}