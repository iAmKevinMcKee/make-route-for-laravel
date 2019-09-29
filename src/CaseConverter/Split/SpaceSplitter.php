<?php declare(strict_types=1);

namespace Intellow\MakeRouteForLaravel\CaseConverter\Split;

use Intellow\MakeRouteForLaravel\CaseConverter\Glue\SpaceGluer;

class SpaceSplitter extends Splitter
{
    const PATTERN = SpaceGluer::DELIMITER . '+';

    /**
     * @return string[]
     */
    public function split(): array
    {
        return $this->splitUsingPattern($this->inputString, self::PATTERN);
    }
}
