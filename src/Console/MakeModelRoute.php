<?php

namespace Intellow\MakeRouteForLaravel\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Intellow\MakeRouteForLaravel\CaseConverter\Convert;

class MakeModelRoute extends Command
{
    protected $signature = 'make:model-route {model : class name} {resourceful-action : index, show, edit, update, create, store, destroy} {attributes?}';

    protected $description = 'Create a new model route with controller and basic test';

    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    private $files;

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

    public function handle()
    {
        $model = trim($this->input->getArgument('model'));
        $resourcefulAction = trim($this->input->getArgument('resourceful-action'));

        if(!$this->ensureValidResourcefulAction($resourcefulAction)) {
            $this->error('You did not enter a valid resourceful action');
            return;
        }

        $baseModel = new Convert($model);
        $baseModelPlural = new Convert(Str::plural($model));
        $controllerName = '\\App\\Http\\Controllers\\' . $baseModel->toPascal() . 'Controller';
        $this->appendRoute($baseModel, $baseModelPlural, $controllerName, $resourcefulAction);
        $this->createOrUpdateController($baseModel, $baseModelPlural, $controllerName, $resourcefulAction);
        $this->createView();
        $this->generateTests();
    }

    private function appendRoute($baseModel, $baseModelPlural, $controllerName, $resourcefulAction)
    {
        switch ($resourcefulAction) {
            case 'index':
                $slug = $baseModelPlural->toKebab();
                $newRoute = $this->files->get(__DIR__ . '/../Stubs/ModelRoutes/index.stub');
                $newRoute = str_replace('|SLUG|', $slug, $newRoute);
                $newRoute = str_replace('|CONTROLLER_NAME|', $controllerName, $newRoute);
                break;
            case 'create':
                $slug = $baseModelPlural->toKebab() . '/create';
                $newRoute = $this->files->get(__DIR__ . '/../Stubs/ModelRoutes/create.stub');
                $newRoute = str_replace('|SLUG|', $slug, $newRoute);
                $newRoute = str_replace('|CONTROLLER_NAME|', $controllerName, $newRoute);
                break;
            case 'store':
                $slug = $baseModelPlural->toKebab();
                $newRoute = $this->files->get(__DIR__ . '/../Stubs/ModelRoutes/store.stub');
                $newRoute = str_replace('|SLUG|', $slug, $newRoute);
                $newRoute = str_replace('|CONTROLLER_NAME|', $controllerName, $newRoute);
                break;
            case 'show':
                $slug = $baseModelPlural->toKebab() . '/{' . $baseModel->toCamel() . '}';
                $newRoute = $this->files->get(__DIR__ . '/../Stubs/ModelRoutes/show.stub');
                $newRoute = str_replace('|SLUG|', $slug, $newRoute);
                $newRoute = str_replace('|CONTROLLER_NAME|', $controllerName, $newRoute);
                break;
            case 'edit':
                $slug = $baseModelPlural->toKebab() . '/{' . $baseModel->toCamel() . '}/edit';
                $newRoute = $this->files->get(__DIR__ . '/../Stubs/ModelRoutes/edit.stub');
                $newRoute = str_replace('|SLUG|', $slug, $newRoute);
                $newRoute = str_replace('|CONTROLLER_NAME|', $controllerName, $newRoute);
                break;
            case 'update':
                $slug = $baseModelPlural->toKebab() . '/{' . $baseModel->toCamel() . '}';
                $newRoute = $this->files->get(__DIR__ . '/../Stubs/ModelRoutes/update.stub');
                $newRoute = str_replace('|SLUG|', $slug, $newRoute);
                $newRoute = str_replace('|CONTROLLER_NAME|', $controllerName, $newRoute);
                break;
            case 'destroy':
                $slug = $baseModelPlural->toKebab() . '/{' . $baseModel->toCamel() . '}';
                $newRoute = $this->files->get(__DIR__ . '/../Stubs/ModelRoutes/destroy.stub');
                $newRoute = str_replace('|SLUG|', $slug, $newRoute);
                $newRoute = str_replace('|CONTROLLER_NAME|', $controllerName, $newRoute);
                break;
        }
        $this->files->append(
            base_path('routes/web.php'),
            $newRoute
        );
        $this->info($baseModel->toPascal() . ' - ' . $resourcefulAction . ' route written to web.php');
    }

    private function createOrUpdateController($baseModel, $baseModelPlural, $controllerName, $resourcefulAction)
    {
        // if controller doesn't exist, create
        // if it does exist, check for method
        // if method exists, do nothing. If not, create method
        $controllerPath = app_path('Http/Controllers/' . $controllerName . '.php');
        if($this->files->exists($controllerPath)) {
            // check for the individual method and if it doesn't exist add it
            // otherwise, do nothing
            $this->info('Controller already exists');
        } else {
            $controller = $this->files->get(__DIR__ . '/../Stubs/new_controller.stub');
            $controller = str_replace('|RESOURCEFUL_ACTION|', $resourcefulAction, $controller);
            $controller = str_replace('|CONTROLLER_NAME|', $controllerName, $controller);

            $this->files->put($controllerPath, $controller);
            $this->info('Controller ' . $controllerName . ' created');
        }

        $controller = $this->files->get($controllerPath);
        $methodName = 'public function ' . $resourcefulAction . '(';
        if (strpos($controller, $methodName) == false) {
            // remove the last two characters of the controller
            $controller = substr($controller, 0, -2);
            switch ($resourcefulAction) {
                case 'index':
                    $newMethod = $this->files->get(__DIR__.'/../Stubs/ControllerMethods/index.stub');
                    break;
//                case 'create':
//                    $slug = $baseModelPlural->toKebab().'/create';
//                    $newRoute = $this->files->get(__DIR__.'/../Stubs/ModelRoutes/create.stub');
//                    $newRoute = str_replace('|SLUG|', $slug, $newRoute);
//                    $newRoute = str_replace('|CONTROLLER_NAME|', $controllerName, $newRoute);
//                    break;
//                case 'store':
//                    $slug = $baseModelPlural->toKebab();
//                    $newRoute = $this->files->get(__DIR__.'/../Stubs/ModelRoutes/store.stub');
//                    $newRoute = str_replace('|SLUG|', $slug, $newRoute);
//                    $newRoute = str_replace('|CONTROLLER_NAME|', $controllerName, $newRoute);
//                    break;
//                case 'show':
//                    $slug = $baseModelPlural->toKebab().'/{'.$baseModel->toCamel().'}';
//                    $newRoute = $this->files->get(__DIR__.'/../Stubs/ModelRoutes/show.stub');
//                    $newRoute = str_replace('|SLUG|', $slug, $newRoute);
//                    $newRoute = str_replace('|CONTROLLER_NAME|', $controllerName, $newRoute);
//                    break;
//                case 'edit':
//                    $slug = $baseModelPlural->toKebab().'/{'.$baseModel->toCamel().'}/edit';
//                    $newRoute = $this->files->get(__DIR__.'/../Stubs/ModelRoutes/edit.stub');
//                    $newRoute = str_replace('|SLUG|', $slug, $newRoute);
//                    $newRoute = str_replace('|CONTROLLER_NAME|', $controllerName, $newRoute);
//                    break;
//                case 'update':
//                    $slug = $baseModelPlural->toKebab().'/{'.$baseModel->toCamel().'}';
//                    $newRoute = $this->files->get(__DIR__.'/../Stubs/ModelRoutes/update.stub');
//                    $newRoute = str_replace('|SLUG|', $slug, $newRoute);
//                    $newRoute = str_replace('|CONTROLLER_NAME|', $controllerName, $newRoute);
//                    break;
//                case 'destroy':
//                    $slug = $baseModelPlural->toKebab().'/{'.$baseModel->toCamel().'}';
//                    $newRoute = $this->files->get(__DIR__.'/../Stubs/ModelRoutes/destroy.stub');
//                    $newRoute = str_replace('|SLUG|', $slug, $newRoute);
//                    $newRoute = str_replace('|CONTROLLER_NAME|', $controllerName, $newRoute);
//                    break;
            }
            // add new method to bottom of controller
            $controller .= $newMethod;
            // update the controller file on the server
            $this->files->replace(
                $controllerPath,
                $controller
            );
            $this->info('Controller method added');
        } else {
            $this->info('Controller Method already exists, no changes in controller');
        }
    }

    private function createView()
    {
        // Create standard convention for views
    }

    private function generateTests($url, $baseModel, $resourcefulAction)
    {
        // generate basic tests for get routes
        // check for AutomatedRouteTests stub, create if doesn't exist
        // Append basic test to the bottom of this file
    }

    private function ensureValidResourcefulAction($resourcefulAction)
    {
        $valid = collect(['index', 'show', 'edit', 'update', 'create', 'store', 'destroy']);
        return $valid->contains($resourcefulAction);
    }
}
