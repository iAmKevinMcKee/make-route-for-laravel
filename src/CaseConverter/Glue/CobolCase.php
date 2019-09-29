<?php declare(strict_types=1);

namespace Intellow\MakeRouteForLaravel\CaseConverter\Glue;

/**
 * Class CobolCase
 *
 * Outputs string in _Cobol case_ format: THIS-IS-COBOL-CASE
 *
 * @package Intellow\MakeRouteForLaravel\CaseConverter\Glue
 */
class CobolCase extends DashGluer
{
    /**
     * Format detected words in _Cobol case_
     *
     * @return string
     */
    public function glue(): string
    {
        return $this->glueUsingRules(self::DELIMITER, $this->upperCase);
    }
}
