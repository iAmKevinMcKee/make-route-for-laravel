<?php

namespace Intellow\MakeRouteForLaravel\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Artisan;

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
    private $baseModel;
    private $resourcefulAction;

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
        $this->baseModel = trim($this->argument('model'));
        if ( !$this->isValidModelInput()) {
            return;
        }
        $this->resourcefulAction = trim($this->argument('resourceful-action'));
        if ( !$this->isValidResourcefulAction()) {
            return;
        }
        $this->appendRoute();
        $this->createOrUpdateController();
        $this->createModel();
        $this->createView();
        $this->generateTests();
    }

    private function appendRoute()
    {
        $controllerName = '\\App\\Http\\Controllers\\'.Str::studly($this->baseModel).'Controller';
        $baseSlug = Str::slug(Str::snake(Str::plural($this->baseModel)));
        switch ($this->resourcefulAction) {
            case 'index':
                $slug = $baseSlug;
                $newRoute = $this->files->get(__DIR__.'/../Stubs/ModelRoutes/index.stub');
                $newRoute = str_replace('|SLUG|', $slug, $newRoute);
                $newRoute = str_replace('|CONTROLLER_NAME|', $controllerName, $newRoute);
                break;
            case 'create':
                $slug = $baseSlug.'/create';
                $newRoute = $this->files->get(__DIR__.'/../Stubs/ModelRoutes/create.stub');
                $newRoute = str_replace('|SLUG|', $slug, $newRoute);
                $newRoute = str_replace('|CONTROLLER_NAME|', $controllerName, $newRoute);
                break;
            case 'store':
                $slug = $baseSlug;
                $newRoute = $this->files->get(__DIR__.'/../Stubs/ModelRoutes/store.stub');
                $newRoute = str_replace('|SLUG|', $slug, $newRoute);
                $newRoute = str_replace('|CONTROLLER_NAME|', $controllerName, $newRoute);
                break;
            case 'show':
                $slug = $baseSlug.'/{'.Str::camel($this->baseModel).'}';
                $newRoute = $this->files->get(__DIR__.'/../Stubs/ModelRoutes/show.stub');
                $newRoute = str_replace('|SLUG|', $slug, $newRoute);
                $newRoute = str_replace('|CONTROLLER_NAME|', $controllerName, $newRoute);
                break;
            case 'edit':
                $slug = $baseSlug.'/{'.Str::camel($this->baseModel).'}/edit';
                $newRoute = $this->files->get(__DIR__.'/../Stubs/ModelRoutes/edit.stub');
                $newRoute = str_replace('|SLUG|', $slug, $newRoute);
                $newRoute = str_replace('|CONTROLLER_NAME|', $controllerName, $newRoute);
                break;
            case 'update':
                $slug = $baseSlug.'/{'.Str::camel($this->baseModel).'}';
                $newRoute = $this->files->get(__DIR__.'/../Stubs/ModelRoutes/update.stub');
                $newRoute = str_replace('|SLUG|', $slug, $newRoute);
                $newRoute = str_replace('|CONTROLLER_NAME|', $controllerName, $newRoute);
                break;
            case 'destroy':
                $slug = $baseSlug.'/{'.Str::camel($this->baseModel).'}';
                $newRoute = $this->files->get(__DIR__.'/../Stubs/ModelRoutes/destroy.stub');
                $newRoute = str_replace('|SLUG|', $slug, $newRoute);
                $newRoute = str_replace('|CONTROLLER_NAME|', $controllerName, $newRoute);
                break;
        }
        $web = $this->files->get('routes/web.php');
        if (strpos($web, $newRoute) == false) {
            $this->files->append(
                base_path('routes/web.php'),
                $newRoute
            );
            $this->info(Str::studly($this->baseModel).' - '.$this->resourcefulAction.' route written to web.php');
        } else {
            $this->line('Route already exists. Nothing added to web.php file');
        }
    }

    private function createOrUpdateController()
    {
        $controllerName = Str::studly($this->baseModel).'Controller';
        // if controller doesn't exist, create
        // if it does exist, check for method
        // if method exists, do nothing. If not, create method
        $controllerPath = app_path('Http/Controllers/'.$controllerName.'.php');
        if ($this->files->exists($controllerPath)) {
            // check for the individual method and if it doesn't exist add it
            // otherwise, do nothing
            $this->line('Controller '.$controllerName.' already exists');
        } else {
            Artisan::call('make:controller '.$controllerName);
            $this->info('Controller '.$controllerName.' created');
        }

        $controller = $this->files->get($controllerPath);
        $methodName = 'public function '.$this->resourcefulAction.'(';
        if (strpos($controller, $methodName) == false) {
            $controller = $this->addUseStatementIfDoesNotExist($this->baseModel, $controller);
            switch ($this->resourcefulAction) {
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
                    $newMethod = str_replace('|PASCAL|', Str::studly($this->baseModel), $newMethod);
                    $newMethod = str_replace('|CAMEL|', Str::camel($this->baseModel), $newMethod);
                    break;
                case 'edit':
                    $newMethod = $this->files->get(__DIR__.'/../Stubs/ControllerMethods/edit.stub');
                    $newMethod = str_replace('|PASCAL|', Str::studly($this->baseModel), $newMethod);
                    $newMethod = str_replace('|CAMEL|', Str::camel($this->baseModel), $newMethod);
                    break;
                case 'update':
                    $newMethod = $this->files->get(__DIR__.'/../Stubs/ControllerMethods/update.stub');
                    $newMethod = str_replace('|PASCAL|', Str::studly($this->baseModel), $newMethod);
                    $newMethod = str_replace('|CAMEL|', Str::camel($this->baseModel), $newMethod);
                    break;
                case 'destroy':
                    $newMethod = $this->files->get(__DIR__.'/../Stubs/ControllerMethods/destroy.stub');
                    $newMethod = str_replace('|PASCAL|', Str::studly($this->baseModel), $newMethod);
                    $newMethod = str_replace('|CAMEL|', Str::camel($this->baseModel), $newMethod);
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
            $this->info('Controller method "'.$this->resourcefulAction.'" added');
        } else {
            $this->line('Controller method "'.$this->resourcefulAction.'" already exists, no changes in controller');
        }
    }

    private function createModel()
    {
        $modelPath = app_path($this->baseModel.'.php');
        if ($this->files->exists($modelPath)) {
            $this->line('Model '.$this->baseModel.' already exists');
        } else {
            if ($this->confirm('This model, '.$this->baseModel.', does not exist. Do you want to create it?')) {
                $command = 'make:model '.$this->baseModel;
                if ($withModel = $this->confirm('Do you want to generate a migration for this model?')) {
                    $command .= ' -m';
                }
                Artisan::call($command);
                if ($withModel) {
                    $this->info($this->baseModel.' Model created with migration.');
                } else {
                    $this->info($this->baseModel.' Model created without migration.');
                }
            } else {
                $this->line('Model '.$this->baseModel.' not created.');
            }
        }
    }

    private function createView()
    {
        $needsView = collect(['index', 'show', 'edit', 'create']);
        if ($needsView->contains($this->resourcefulAction)) {
            $path = 'resources/views/models/'.Str::snake($this->baseModel).'/'.$this->resourcefulAction.'.blade.php';
            if ( !$this->files->exists($path)) {
                if ( !$this->files->exists('resources/views/models')) {
                    $this->files->makeDirectory('resources/views/models');
                }
                if ( !$this->files->exists('resources/views/models/'.Str::snake($this->baseModel))) {
                    $this->files->makeDirectory('resources/views/models/'.Str::snake($this->baseModel));
                }
                $this->files->put(
                    base_path('resources/views/models/'.Str::snake($this->baseModel).'/'.$this->resourcefulAction.'.blade.php'),
                    $this->files->get(__DIR__.'/../Stubs/empty_view.stub')
                );
                $this->info('Empty view created at '.$path);
            } else {
                $this->line('The view already exists, no view was created.');
            }
        }
    }

    private function generateFormRequest()
    {
        // If this is a store or update method, check if a form request exists
        // If not, create one with the artisan command and then update the controller to inject this
        // If it does exist, do nothing
    }

    private function generateTests()
    {
        if($this->resourcefulAction == 'index' || $this->resourcefulAction == 'create') {
            $testClassPath = base_path('tests/Feature/AutomatedRouteTests.php');
            $this->createTestClassIfDoesNotExist($testClassPath);
            $newTest = $this->files->get(__DIR__.'/../Stubs/Tests/model_test_case.stub');
            $newTest = str_replace('|MODEL|', Str::studly($this->baseModel), $newTest);
            $newTest = str_replace('|ACTION|', Str::studly($this->resourcefulAction), $newTest);
            $slug = Str::slug(Str::snake(Str::plural($this->baseModel)));
            if($this->resourcefulAction == 'create') {
                $slug .= '/create';
            }
            $newTest = str_replace('|SLUG|', $slug, $newTest);
            $testClass = $this->files->get($testClassPath);
            // remove the last two characters of the controller
            $testClass = substr($testClass, 0, -2);
            // add new method to bottom of controller
            $testClass .= $newTest;
            // update the controller file on the server
            $this->files->replace(
                $testClassPath,
                $testClass
            );
            $this->info('New Test Added');
        }
    }

    private function isValidResourcefulAction()
    {
        $valid = collect(['index', 'show', 'edit', 'update', 'create', 'store', 'destroy']);

        if ( !$valid->contains($this->resourcefulAction)) {
            $this->error('You did not enter a valid resourceful action');

            return false;
        }

        return true;
    }

    private function addUseStatementIfDoesNotExist($baseModel, $controller)
    {
        $useStatement = 'use App\\'.$baseModel.';'."\n";
        if ( !strpos($controller, $useStatement)) {
            $position = strpos($controller, 'use ');
            if ($position) {
                return substr_replace($controller, $useStatement, $position, 0);
            } else {
                $useStatement .= "\n";

                return substr_replace($controller, $useStatement, strpos($controller, 'class '), 0);
            }
        }

        return $controller;
    }

    private function isValidModelInput()
    {
        if ($this->baseModel != ucfirst($this->baseModel)) {
            $this->error('Your model must start with a capital letter');

            return false;
        }

        return true;
    }

    private function createTestClassIfDoesNotExist($testClassPath)
    {
        if ( !$this->files->exists($testClassPath)) {
            Artisan::call('make:test AutomatedRouteTests');
            $this->info('Created AutomatedRouteTests at /tests/Feature/AutomatedRouteTests.php');
        }
    }
}
