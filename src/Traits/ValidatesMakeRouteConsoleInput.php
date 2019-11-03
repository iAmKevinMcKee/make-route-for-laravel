<?php

namespace Intellow\MakeRouteForLaravel\Traits;

use Illuminate\Support\Str;

trait ValidatesMakeRouteConsoleInput
{

    private function isValidResourcefulAction()
    {
        $valid = collect(['index', 'show', 'edit', 'update', 'create', 'store', 'destroy']);

        if ( !$valid->contains($this->resourcefulAction)) {
            $this->error('You did not enter a valid resourceful action');

            return false;
        }

        return true;
    }

    private function isValidModelInput()
    {
        if ($this->baseModel != ucfirst($this->baseModel)) {
            $this->error('Your model must start with a capital letter');

            return false;
        }

        return true;
    }

    private function isSlugKebabCase($slug)
    {
        return $slug == Str::kebab($slug);
    }
}
