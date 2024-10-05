<?php
declare(strict_types = 1);
namespace Slothsoft\MTG\Assets;

use Slothsoft\Core\IO\Sanitizer\ArraySanitizer;
use Slothsoft\Farah\Module\Asset\ParameterFilterStrategy\AbstractMapParameterFilter;

class OracleParameterFilter extends AbstractMapParameterFilter {

    protected function createValueSanitizers(): array {
        return [
            'search-query' => new ArraySanitizer()
        ];
    }
}

