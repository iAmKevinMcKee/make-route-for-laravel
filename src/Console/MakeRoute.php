<?php

namespace Intellow\MakeRouteForLaravel\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\File;

class MakeRoute extends Command
{
    protected $signature = 'make:route {slug : kebab case relative path} {resourceful-action : index, show, edit, update, create, store, destroy} {attributes?}';

    protected $description = 'Create a new route with controller and basic test';

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

    // Direct code and/or inspiration came from https://github.com/amochohan/laravel-make-resource/
    public function handle()
    {
        $slug = trim($this->input->getArgument('slug'));
        $resourcefulAction = trim($this->input->getArgument('resourceful-action'));

        if(!$this->ensureValidResourcefulAction($resourcefulAction)) {
            $this->error('You did not enter a valid resourceful action');
            return;
        }

        $pascalCase = str_replace('-', '', ucwords($slug, '-'));

        $this->appendRoute($slug, $pascalCase, $resourcefulAction);
        $this->createControllerMethod($slug, $pascalCase, $resourcefulAction);
    }

    private function appendRoute($slug, $pascalCase, $resourcefulAction)
    {
        $controllerName = $pascalCase . 'Controller';
        switch ($resourcefulAction) {
            case 'index':
                $newRoutes = $this->files->get(__DIR__ . '/../Stubs/Routes/index.stub');
                $newRoutes = str_replace('|SLUG|', $slug, $newRoutes);
                $newRoutes = str_replace('|CONTROLLER_NAME|', $controllerName, $newRoutes);

                $this->files->append(
                    base_path('routes/web.php'),
                    $newRoutes
                );
            case 'store':
                $newRoutes = $this->files->get(__DIR__ . '/../Stubs/Routes/store.stub');
                $newRoutes = str_replace('|SLUG|', $slug, $newRoutes);
                $newRoutes = str_replace('|CONTROLLER_NAME|', $controllerName, $newRoutes);

                $this->files->append(
                    base_path('routes/web.php'),
                    $newRoutes
                );

        }

        $this->info('Added route for ' . $slug . ' in web.php');
    }

    private function createControllerMethod($slug, $pascalCase, $resourcefulAction)
    {
        // if controller doesn't exist, create
        // if it does exist, check for method
        // if method exists, do nothing. If not, create method
        $controllerName = $pascalCase . 'Controller';
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

    }

    private function ensureValidResourcefulAction($resourcefulAction)
    {
        $valid = collect(['index', 'show', 'edit', 'update', 'create', 'store', 'destroy']);
        return $valid->contains($resourcefulAction);
    }
}
