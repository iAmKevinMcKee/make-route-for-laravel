<?php declare(strict_types=1);

namespace Intellow\MakeRouteForLaravel\CaseConverter\Glue;

/**
 * Class KebabCase
 *
 * Outputs string in _Cobol case_ format: this-is-kebab-case
 *
 * @package Intellow\MakeRouteForLaravel\CaseConverter\Glue
 */
class KebabCase extends DashGluer
{
    /**
     * Format detected words in _Kebab case_
     *
     * @return string
     */
    public function glue(): string
    {
        return $this->glueUsingRules(self::DELIMITER, $this->lowerCase);
    }
}
