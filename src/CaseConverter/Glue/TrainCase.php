<?php declare(strict_types=1);

namespace Intellow\MakeRouteForLaravel\CaseConverter\Glue;

class TrainCase extends DashGluer
{
    public function glue(): string
    {
        return $this->glueUsingRules(self::DELIMITER, $this->titleCase);
    }
}
