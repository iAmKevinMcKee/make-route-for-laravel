<?php

namespace Intellow\MakeRouteForLaravel\Traits;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Artisan;

trait CreatesAndUpdatesFiles
{

    private function createOrUpdateController($controllerName = null)
    {
        if (is_null($controllerName)) {
            $controllerName = Str::studly($this->baseModel).'Controller';
        }
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
        $methodName = 'public function '.$this->resourcefulAction;
        if (strpos($controller, $methodName) == false) {
            if ($this->baseModel) {
                $controller = $this->addUseStatementIfDoesNotExist($this->baseModel, $controller);
                $controllerStubLocation = '/../Stubs/ModelControllerMethods/'.$this->resourcefulAction.'.stub';
            }
            else {
                $controllerStubLocation = '/../Stubs/ControllerMethods/'.$this->resourcefulAction.'.stub';
            }
            switch ($this->resourcefulAction) {
                case 'index':
                case 'create':
                case 'store':
                    $newMethod = $this->files->get(__DIR__.$controllerStubLocation);
                    break;
                case 'show':
                case 'edit':
                case 'update':
                case 'destroy':
                    $newMethod = $this->files->get(__DIR__.$controllerStubLocation);
                    if ($this->baseModel) {
                        $newMethod = str_replace('|PASCAL|', Str::studly($this->baseModel), $newMethod);
                        $newMethod = str_replace('|CAMEL|', Str::camel($this->baseModel), $newMethod);
                    } else {
                        $newMethod = str_replace('|PASCAL| $|CAMEL|', '', $newMethod);
                    }
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

            return true;
        } else {
            $this->line('Controller method "'.$this->resourcefulAction.'" already exists, no changes in controller');

            return false;
        }
    }

    private function createModelIfDoesNotExist()
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

    private function createView($controllerPath)
    {
        $needsView = collect(['index', 'show', 'edit', 'create']);
        if ($needsView->contains($this->resourcefulAction)) {
            $directory = str_replace('-', '_', $this->slug);
            $path = 'resources/views/other/'.$directory.'/'.$this->resourcefulAction.'.blade.php';
            if ( !$this->files->exists($path)) {
                if ( !$this->files->exists('resources/views/other')) {
                    $this->files->makeDirectory('resources/views/other');
                }
                if ( !$this->files->exists('resources/views/other/'.$directory)) {
                    $this->files->makeDirectory('resources/views/other/'.$directory);
                }
                $this->files->put(
                    base_path('resources/views/other/'.$directory.'/'.$this->resourcefulAction.'.blade.php'),
                    $this->files->get(__DIR__.'/../Stubs/empty_view.stub')
                );
                $this->info('Empty view created at '.$path);
                $this->addViewNameToController($controllerPath, 'other', $directory);
            } else {
                $this->line('The view already exists, no view was created.');
            }
        }
    }

    private function createModelView($controllerPath)
    {
        $needsView = collect(['index', 'show', 'edit', 'create']);
        if ($needsView->contains($this->resourcefulAction)) {
            $viewName = Str::snake($this->baseModel);
            $path = 'resources/views/models/'.$viewName.'/'.$this->resourcefulAction.'.blade.php';
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
                $this->addViewNameToController($controllerPath, 'models', $viewName);
            } else {
                $this->line('The view already exists, no view was created.');
            }
        }
    }

    private function generateTests()
    {
        if ($this->resourcefulAction == 'index' || $this->resourcefulAction == 'create') {
            $testClassPath = base_path('tests/Feature/AutomatedRouteTests.php');
            $this->createTestClassIfDoesNotExist($testClassPath);
            $newTest = $this->files->get(__DIR__.'/../Stubs/Tests/model_test_case.stub');
            $newTest = str_replace('|MODEL|', Str::studly($this->baseModel), $newTest);
            $newTest = str_replace('|ACTION|', Str::studly($this->resourcefulAction), $newTest);
            $slug = Str::slug(Str::snake(Str::plural($this->baseModel)));
            if ($this->resourcefulAction == 'create') {
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

    private function addUseStatementIfDoesNotExist($baseModel, $controller)
    {
        $useStatement = 'use App\\'.$baseModel.';'."\n";
        if ( !strpos($controller, $useStatement)) {
            $position = strpos($controller, 'use ');
            if ($position) {
                return substr_replace($controller, $useStatement, $position, 0);
            } else {
                $useStatement .= "\n";

                return substr_replace($controller, $useStatement,
                    strpos($controller, 'class '.Str::studly($this->baseModel).'Controller'), 0);
            }
        }

        return $controller;
    }

    private function createTestClassIfDoesNotExist($testClassPath)
    {
        if ( !$this->files->exists($testClassPath)) {
            Artisan::call('make:test AutomatedRouteTests');
            $this->info('Created AutomatedRouteTests at /tests/Feature/AutomatedRouteTests.php');
        }
    }

    private function generateFormRequest()
    {
        // If this is a store or update method, check if a form request exists
        // If not, create one with the artisan command and then update the controller to inject this
        // If it does exist, do nothing
    }

    private function appendRouteToRoutesFile($slug, $controllerName)
    {
        $newRoute = $this->files->get(__DIR__.'/../Stubs/ModelRoutes/'.$this->resourcefulAction.'.stub');
        $newRoute = str_replace('|SLUG|', $slug, $newRoute);
        $newRoute = str_replace('|CONTROLLER_NAME|', $controllerName, $newRoute);
        $web = $this->files->get('routes/web.php');
        if (strpos($web, $newRoute) == false) {
            $this->files->append(
                base_path('routes/web.php'),
                $newRoute
            );
            $this->info('Route written to the bottom of web.php');

            return true;
        } else {
            $this->error('Route already exists. No changes were made.');

            return false;
        }
    }

    private function addViewNameToController($controllerPath, $baseDirectory, $viewName)
    {
        $controller = $this->files->get($controllerPath);
        $newController = str_replace('|DIRECTORY|', $baseDirectory, $controller);
        $newController = str_replace('|VIEWNAME|', $viewName.'.'.$this->resourcefulAction, $newController);
        $this->files->replace(
            $controllerPath,
            $newController
        );
    }
}
