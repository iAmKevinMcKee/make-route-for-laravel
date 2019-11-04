<?php

namespace Intellow\MakeRouteForLaravel\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\File;
use Intellow\MakeRouteForLaravel\Traits\CreatesAndUpdatesFiles;
use Intellow\MakeRouteForLaravel\Traits\ValidatesMakeRouteConsoleInput;

class MakeRoute extends Command
{

    use ValidatesMakeRouteConsoleInput;
    use CreatesAndUpdatesFiles;

    protected $signature = 'make:route {slug : kebab case relative path} {resourceful-action : index, show, edit, update, create, store, destroy} {controller-name? : If you do not define a controller path, one will be generated based on your slug}';

    protected $description = 'Create a new route with controller, view and basic test';

    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    private $files;
    private $slug;
    private $resourcefulAction;
    private $baseModel = null;
    private $controllerName;

    /**
     * Create a new command instance.
     *
     * @param  Filesystem  $files
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    // Direct code and/or inspiration came from https://github.com/amochohan/laravel-make-resource/
    public function handle()
    {
        $this->validateMakeRouteInputs();

        if ( !$this->appendRouteToRoutesFile($this->slug, '\\App\\Http\\Controllers\\' . $this->controllerName)) {
            return;
        }
        $controllerUpdate = $this->createOrUpdateController($this->controllerName);
        if($controllerUpdate) {
            $this->createView(app_path('Http/Controllers/'.$this->controllerName.'.php'));
        }
    }

    private function appendRoute($controllerName)
    {
        return $this->appendRouteToRoutesFile($this->slug, $controllerName);
    }

    private function createControllerMethod($slug, $pascalCase, $resourcefulAction)
    {
        // if controller doesn't exist, create
        // if it does exist, check for method
        // if method exists, do nothing. If not, create method
        $controllerName = $pascalCase.'Controller';
        $controllerPath = app_path('Http/Controllers/'.$controllerName.'.php');
        if ($this->files->exists($controllerPath)) {
            // check for the individual method and if it doesn't exist add it
            // otherwise, do nothing
            $this->info('Controller already exists');
        } else {
            $controller = $this->files->get(__DIR__.'/../Stubs/new_controller.stub');
            $controller = str_replace('|RESOURCEFUL_ACTION|', $resourcefulAction, $controller);
            $controller = str_replace('|CONTROLLER_NAME|', $controllerName, $controller);

            $this->files->put($controllerPath, $controller);
            $this->info('Controller '.$controllerName.' created');
        }

    }

    private function validateMakeRouteInputs()
    {
        $this->slug = ltrim(strtolower(trim($this->argument('slug'))), '/');
        if ( !$this->isSlugKebabCase($this->slug)) {
            $this->error('Your slug, "'.$this->slug.'" is not in kebab case. Please try again');

            return;
        }
        if($this->argument('controller-name')) {
            $this->controllerName = $this->argument('controller-name');
        } else {
            $controllerName = rtrim(str_replace("-", " ", preg_replace('/{(.*?)}/', '', $this->slug)), '/');
            $controllerName = str_replace("_", " ", $controllerName);
            $controllerName = ucwords($controllerName);
            $controllerName = str_replace(" ", "", $controllerName);
            $this->controllerName = $controllerName . 'Controller';
        }
        $this->resourcefulAction = trim($this->argument('resourceful-action'));
        if ( !$this->isValidResourcefulAction()) {
            $this->error('You did not enter a valid resourceful action');

            return;
        }
    }
}
