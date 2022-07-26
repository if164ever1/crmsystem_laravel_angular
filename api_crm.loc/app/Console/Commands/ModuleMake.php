<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Filesystem\Filesystem;

class ModuleMake extends Command
{
    private $files;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:module {name} {--all} {--migration} {--vue} {--view} {--controller} {--model} {--api}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Filesystem $filesystem)
    {
        parent::__construct();
        $this->files = $filesystem;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        if($this->option('all')){
            $this->input->setOption('migration', true);
            $this->input->setOption('vue', true);
            $this->input->setOption('view', true);
            $this->input->setOption('controller', true);
            $this->input->setOption('model', true);
            $this->input->setOption('api', true);

        }
        if($this->option('migration')){
            $this->createMigration();
        }
        if($this->option('vue')){
            $this->createVueComponent();
        }
        if($this->option('view')){
            $this->createView();
        }
        if($this->option('controller')){
            $this->createController();
        }
        if($this->option('model')){
            $this->createModel();
        }
        if($this->option('api')){
            $this->createApiController();
        }
    }

    private function createModel(){
        $model = Str::singular(Str::studly(class_basename($this->argument('name'))));

        $this->call('make:model', ['name' => 'App//Modules//'. trim($this->argument('name')) . "//Models//" . $model]);
    }

    private function createMigration(){
        $table = Str::plural(Str::snake(class_basename($this->argument('name'))));

        try{
            $this->call('make:migration', [
                'name' => "create_{$table}_table",
                '--create' => $table,
                '--path' =>  'App//Modules//'. trim($this->argument('name')) . '//Migrations'
            ]);
        }catch(\Exception $e){
            $this->error($e->getMessage());
        }
    }

    private function createController(){

        $controller = Str::studly(class_basename($this->argument('name')));
        $modelName = Str::singular(Str::studly(class_basename($this->argument('name'))));

        $path = $this->getControllerPath($this->argument('name'));

        if($this->alreadyExists($path)){
            $this->error('Controller already exists!');
        }
        else {
            $this->makeDirectory($path);

            $stub = $this->files->get(base_path('resources/stubs/controller.model.api.stub'));

            $stub = str_replace(
                [
                    'DummyNamespace',
                    'DummyRootNamespace',
                    'DummyClass'.
                    'DummyFullModelClass',
                    'DummyModelClass',
                    'DummyModelVariable',
                ],
                [
                    "App\Modules\\".trim($this->argument('name')) . "\Controllers",
                    $this->laravel->getNamespace(),
                    $controller.'Controller',
                    "App\Modules\\".trim($this->argument('name'))."\Modules\{$modelName}",
                    $modelName,
                    lcfirst(($modelName))
                ],

                $stub
            );

            $this->files->put($path, $stub);
            $this->info('Controller created successfull');
            //$this->updateModularConfig();
        }

        $this->createRoutes($controller, $modelName);
    }

    private function createVueComponent(){
        $path = $this->getVueComponentPath($this->argument('name'));
        $component = Str::studly(class_basename($this->argument('name')));

        if($this->alreadyExists($path)){
            $this->error('Vue component already exists');
        }
        else {
            $this->makeDirectory($path);

            $stub = $this->files->get(base_path('resources/stubs/vue.component.stub'));

            $stub = str_replace([
                'DummyClass',
            ],
            [
                $component
            ],
            $stub
        );
            $this->files->put($path, $stub);
            $this->info('Vue component created successfully');
        }
    }
    private function createView(){
        $paths = $this->getViewPath($this->argument('name'));


        foreach($paths as $path){
            $view = Str::studly(class_basename($this->argument('name')));

            if($this->alreadyExists($path)){
                $this->error('View already exists');
            }
            else {
                $this->makeDirectory($path);

                $stub = $this->files->get(base_path('resources/stubs/view.stub'));

                $stub = str_replace([
                    '',
                ],
                [

                ],
                $stub
            );
            $this->files->put($path, $stub);
            $this->info('View component created successfully');
        }
        }
    }

    private function createApiController(){
        $controller = Str::studly(class_basename($this->argument('name')));
        $modelName = Str::singular(Str::studly(class_basename($this->argument('name'))));

        $path = $this->getControllerPath($this->argument('name'));

        if($this->alreadyExists($path)){
            $this->error('Controller already exists!');
        }
        else {
            $this->makeDirectory($path);

            $stub = $this->get(base_path('sources/stubs/controller.model.api.stub'));

            $stub = str_replace(
                [
                    'DummyNamespace',
                    'DummyRootNamespace',
                    'DummyClass'.
                    'DummyFullModelClass',
                    'DummyModelClass',
                    'DummyModelVariable',
                ],
                [
                    "App\Modules\\".trim($this->argument('name')) . "\Controllers",
                    $this->laravel->getNamespace(),
                    $controller.'Controller',
                    "App\Modules\\".trim($this->argument('name'))."\Modules\{$modelName}",
                    $modelName,
                    lcfirst(($modelName))
                ],
                $stub
            );
            $this->files->put($path, $stub);
            $this->info('Controller created successfull');
            //$this->updateModularConfig();
        }

        $this->createApiRoutes($controller, $modelName);
    }

    private function getControllerPath($argument){
        $controller = Str::studly(class_basename($argument));
        return $this->laravel['path'] . '/Modules/' . str_replace('\\', '/', $argument) . "/Controllers/" . "{$controller}Controller.php";
    }

    private function getApiControllerPath($argument){
        $controller = Str::studly(class_basename($argument));
        return $this->laravel['path'] . '/Modules/' . str_replace('\\', '/', $argument) . "/Controllers/Api/" . "{$controller}Controller.php";
    }

    private function makeDirectory($path){
        if(! $this->files->isDirectory(dirname($path))){
            $this->files->makeDirectory(dirname($path), 0777, true, true );
        }
        return $path;
    }

    private function createRoutes($controller, $modelName){
        $routePath = $this->getRoutesPath($this->argument('name'));

        if($this->alreadyExists($routePath)){
            $this->error('Routes already exists');
        }
        else {
            $this->makeDirectory($routePath);

            $stub = $this->files->get(base_path('resources/stubs/routes.web.stub'));

            $stub = str_replace([
                'DummyClass',
                'DummyRoutePrefix',
                'DummyModelVariable'
            ], [
                $controller.'Controller',
                Str::plural(Str::snake(lcfirst($modelName), '-')),
                lcfirsT($modelName)
            ],
            $stub
        );

        $this->files->put($routePath, $stub);
        $this->info('Routes created succsessfully');
        }
    }

    private function createApiRoutes($controller, $modelName){
        $routePath = $this->getApiRoutesPath($this->argument('name'));

        if($this->alreadyExists($routePath)){
            $this->error('Routes already exists');
        }
        else {
            $this->makeDirectory($routePath);

            $stub = $this->files->get(base_path('resources/stubs/routes.web.stub'));

            $stub = str_replace([
                'DummyClass',
                'DummyRoutePrefix',
                'DummyModelVariable'
            ], [
                $controller.'Controller',
                Str::plural(Str::snake(lcfirst($modelName), '-')),
                lcfirsT($modelName)
            ],
            $stub
        );

        $this->files->put($routePath, $stub);
        $this->info('Routes created succsessfully');
        }
    }

    private function getApiRoutesPath($name) : string {
        return $this->laravel['path'] . '/Modules/' . str_replace('\\', '/', $name) . "/Routes/api.php";
    }

    private function getRoutesPath($name) : string {
        return $this->laravel['path'] . '/Modules/' . str_replace('\\', '/', $name) . "/Routes/web.php";
    }

    private function alreadyExists($path) : bool {
        return $this->files->exists($path);
    }

    private function getVueComponentPath($name) : string{
        return base_path('resources/js/components' . str_replace('\\', '/', $name).".vue");
    }

    private function getViewPath($name) {
        $arrFiles = collect([
            'create', 'edit', 'index', 'show'
        ]);

        $paths = $arrFiles->map(function($item) use ($name){
            return base_path('resources/views/'.str_replace('\\', '/', $name). '/' . $item. ".blade.php");
        });

        return $paths;
    }

}
