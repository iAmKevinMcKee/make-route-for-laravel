<?php declare(strict_types=1);

namespace Intellow\MakeRouteForLaravel\CaseConverter\Split;

use Intellow\MakeRouteForLaravel\CaseConverter\Glue\DotNotation;
use Intellow\MakeRouteForLaravel\CaseConverter\Glue\SpaceGluer;

class DotSplitter extends Splitter
{
    const PATTERN = '\\' . DotNotation::DELIMITER . '+';

    /**
     * @return string[]
     */
    public function split(): array
    {
        return $this->splitUsingPattern($this->inputString, self::PATTERN);
    }
}
