<?php declare(strict_types=1);

namespace Intellow\MakeRouteForLaravel\CaseConverter;

/**
 * Interface CaseConverterInterface
 *
 * @package Intellow\MakeRouteForLaravel\CaseConverter
 * @author  Jawira Portugal <dev@tugal.be>
 */
interface CaseConverterInterface
{
    public function convert(string $source): Convert;
}
