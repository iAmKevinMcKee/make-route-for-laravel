<?php declare(strict_types=1);

namespace Intellow\MakeRouteForLaravel\CaseConverter;

/**
 * Class CaseConverter
 *
 * Factory class which returns a Convert object.
 *
 * @package Intellow\MakeRouteForLaravel\CaseConverter
 * @author  Jawira Portugal <dev@tugal.be>
 */
class CaseConverter implements CaseConverterInterface
{
    /**
     * Returns a Convert object
     *
     * @param string $source Input string to be converted
     *
     * @return \Intellow\MakeRouteForLaravel\CaseConverter\Convert
     * @throws \Intellow\MakeRouteForLaravel\CaseConverter\CaseConverterException
     */
    public function convert(string $source): Convert
    {
        return new Convert($source);
    }
}
