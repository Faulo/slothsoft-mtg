<?php
declare(strict_types = 1);
namespace Slothsoft\MTG\Assets;

use Slothsoft\Core\IO\Sanitizer\StringSanitizer;
use Slothsoft\Farah\Module\Asset\ParameterFilterStrategy\AbstractMapParameterFilter;

class RarityImageParameterFilter extends AbstractMapParameterFilter {
    
    protected function createValueSanitizers(): array {
        return [
            'expansion_name' => new StringSanitizer(),
            'expansion_abbr' => new StringSanitizer(),
            'rarity' => new StringSanitizer()
        ];
    }
}

