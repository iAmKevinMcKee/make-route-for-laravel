<?php

namespace Intellow\MakeRouteForLaravel\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeModelRoute extends Command
{
    protected $signature = 'make:model-route {model : class name} {resourceful-action : index, show, edit, update, create, store, destroy}';

    protected $description = 'Create a new model route with controller method';

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

        $baseModel = $model;
        $baseModelPlural = Str::plural($model);
        $controllerRouteName = '\\App\\Http\\Controllers\\' . Str::studly($baseModel) . 'Controller';
        $controllerName = Str::studly($baseModel) . 'Controller';
        $this->appendRoute($baseModel, $baseModelPlural, $controllerRouteName, $resourcefulAction);
        $this->createOrUpdateController($baseModel, $baseModelPlural, $controllerName, $resourcefulAction);
//        $this->createView();
//        $this->generateTests();
    }

    private function appendRoute($baseModel, $baseModelPlural, $controllerName, $resourcefulAction)
    {
        $baseSlug = Str::slug(Str::snake($baseModelPlural));
        switch ($resourcefulAction) {
            case 'index':
                $slug = $baseSlug;
                $newRoute = $this->files->get(__DIR__ . '/../Stubs/ModelRoutes/index.stub');
                $newRoute = str_replace('|SLUG|', $slug, $newRoute);
                $newRoute = str_replace('|CONTROLLER_NAME|', $controllerName, $newRoute);
                break;
            case 'create':
                $slug = $baseSlug . '/create';
                $newRoute = $this->files->get(__DIR__ . '/../Stubs/ModelRoutes/create.stub');
                $newRoute = str_replace('|SLUG|', $slug, $newRoute);
                $newRoute = str_replace('|CONTROLLER_NAME|', $controllerName, $newRoute);
                break;
            case 'store':
                $slug = $baseSlug;
                $newRoute = $this->files->get(__DIR__ . '/../Stubs/ModelRoutes/store.stub');
                $newRoute = str_replace('|SLUG|', $slug, $newRoute);
                $newRoute = str_replace('|CONTROLLER_NAME|', $controllerName, $newRoute);
                break;
            case 'show':
                $slug = $baseSlug . '/{' . Str::camel($baseModel) . '}';
                $newRoute = $this->files->get(__DIR__ . '/../Stubs/ModelRoutes/show.stub');
                $newRoute = str_replace('|SLUG|', $slug, $newRoute);
                $newRoute = str_replace('|CONTROLLER_NAME|', $controllerName, $newRoute);
                break;
            case 'edit':
                $slug = $baseSlug . '/{' . Str::camel($baseModel) . '}/edit';
                $newRoute = $this->files->get(__DIR__ . '/../Stubs/ModelRoutes/edit.stub');
                $newRoute = str_replace('|SLUG|', $slug, $newRoute);
                $newRoute = str_replace('|CONTROLLER_NAME|', $controllerName, $newRoute);
                break;
            case 'update':
                $slug = $baseSlug . '/{' . Str::camel($baseModel) . '}';
                $newRoute = $this->files->get(__DIR__ . '/../Stubs/ModelRoutes/update.stub');
                $newRoute = str_replace('|SLUG|', $slug, $newRoute);
                $newRoute = str_replace('|CONTROLLER_NAME|', $controllerName, $newRoute);
                break;
            case 'destroy':
                $slug = $baseSlug . '/{' . Str::camel($baseModel) . '}';
                $newRoute = $this->files->get(__DIR__ . '/../Stubs/ModelRoutes/destroy.stub');
                $newRoute = str_replace('|SLUG|', $slug, $newRoute);
                $newRoute = str_replace('|CONTROLLER_NAME|', $controllerName, $newRoute);
                break;
        }
        $this->files->append(
            base_path('routes/web.php'),
            $newRoute
        );
        $this->info(Str::studly($baseModel) . ' - ' . $resourcefulAction . ' route written to web.php');
    }

    private function createOrUpdateController($baseModel, $baseModelPlural, $controllerName, $resourcefulAction)
    {
        // if controller doesn't exist, create
        // if it does exist, check for method
        // if method exists, do nothing. If not, create method
        $this->info('controller name: ' . $controllerName);
        $controllerPath = app_path('Http/Controllers/' . $controllerName . '.php');
        if($this->files->exists($controllerPath)) {
            // check for the individual method and if it doesn't exist add it
            // otherwise, do nothing
            $this->info('Controller already exists');
        } else {
            Artisan::call('make:controller ' . $controllerName);
            $this->info('Controller ' . $controllerName . ' created');
        }

        $controller = File::get($controllerPath);
        $methodName = 'public function ' . $resourcefulAction . '(';
        if (strpos($controller, $methodName) == false) {
            $controller = $this->addUseStatementIfDoesNotExist($baseModel, $controller);
            switch ($resourcefulAction) {
                case 'index':
                    $newMethod = $this->files->get(__DIR__.'/../Stubs/ControllerMethods/index.stub');
                    break;
                case 'create':
                    $newMethod = $this->files->get(__DIR__.'/../Stubs/ControllerMethods/create.stub');
                    break;
                case 'store':
                    $newMethod = $this->files->get(__DIR__.'/../Stubs/ControllerMethods/store.stub');
                    break;
                case 'show':
                    $newMethod = $this->files->get(__DIR__.'/../Stubs/ControllerMethods/show.stub');
                    $newMethod = str_replace('|PASCAL|', Str::studly($baseModel), $newMethod);
                    $newMethod = str_replace('|CAMEL|', Str::camel($baseModel), $newMethod);
                    break;
                case 'edit':
                    $newMethod = $this->files->get(__DIR__.'/../Stubs/ControllerMethods/edit.stub');
                    $newMethod = str_replace('|PASCAL|', Str::studly($baseModel), $newMethod);
                    $newMethod = str_replace('|CAMEL|', Str::camel($baseModel), $newMethod);
                    break;
                case 'update':
                    $newMethod = $this->files->get(__DIR__.'/../Stubs/ControllerMethods/update.stub');
                    $newMethod = str_replace('|PASCAL|', Str::studly($baseModel), $newMethod);
                    $newMethod = str_replace('|CAMEL|', Str::camel($baseModel), $newMethod);
                    break;
                case 'destroy':
                    $newMethod = $this->files->get(__DIR__.'/../Stubs/ControllerMethods/destroy.stub');
                    $newMethod = str_replace('|PASCAL|', Str::studly($baseModel), $newMethod);
                    $newMethod = str_replace('|CAMEL|', Str::camel($baseModel), $newMethod);
                    break;
            }
            // remove the last two characters of the controller
            $controller = substr($controller, 0, -2);
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

    private function generateFormRequest($baseModel, $resourcefulAction)
    {
        // If this is a store or update method, check if a form request exists
        // If not, create one with the artisan command and then update the controller to inject this
        // If it does exist, do nothing
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

    private function addUseStatementIfDoesNotExist($baseModel, $controller)
    {
        $useStatement = 'use App\\' . $baseModel . ';' . "\n";
        if(!strpos($controller, $useStatement)) {
            $position = strpos($controller, 'use ');
            if($position) {
                return substr_replace($controller, $useStatement, $position, 0);
            } else {
                $useStatement .= "\n";
                return substr_replace($controller, $useStatement, strpos($controller, 'class '), 0);
            }
        }
        return $controller;
    }
}
