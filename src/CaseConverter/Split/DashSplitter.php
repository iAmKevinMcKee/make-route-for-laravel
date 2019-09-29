<?php declare(strict_types=1);

namespace Intellow\MakeRouteForLaravel\CaseConverter\Split;

use Intellow\MakeRouteForLaravel\CaseConverter\Glue\DashGluer;

class DashSplitter extends Splitter
{
    const PATTERN = DashGluer::DELIMITER . '+';

    /**
     * @return string[]
     */
    public function split(): array
    {
        return $this->splitUsingPattern($this->inputString, self::PATTERN);
    }
}
