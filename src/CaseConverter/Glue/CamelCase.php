<?php declare(strict_types=1);

namespace Intellow\MakeRouteForLaravel\CaseConverter\Glue;

/**
 * Class CamelCase
 *
 * Outputs string in _Camel case_ format: thisIsCamelCase
 *
 * @package Intellow\MakeRouteForLaravel\CaseConverter\Glue
 */
class CamelCase extends UppercaseGluer
{
    /**
     * Format detected words in _Camel case_
     *
     * @return string
     */
    public function glue(): string
    {
        return $this->glueUsingRules(self::DELIMITER, $this->titleCase, $this->lowerCase);
    }
}
