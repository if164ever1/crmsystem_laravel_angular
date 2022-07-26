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
                    "App\\Modules\\".trim($this->argument('name')) . "\\Controllers",
                    $this->laravel->getNamespace(),
                    $controller.'Controller',
                    "App\\Modules\\".trim($this->argument('name'))."\\Modules\\{$modelName}",
                    $modelName,
                    lcfirst(($modelName))
                ],
                $stub
            );
            $this->files->put($path, $stub);
            $this->info('Controller created successfull');
            //$this->updateModularConfig();
        }
    }

    private function createVueComponent(){}
    private function createView(){}

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
                    "App\\Modules\\".trim($this->argument('name')) . "\\Controllers",
                    $this->laravel->getNamespace(),
                    $controller.'Controller',
                    "App\\Modules\\".trim($this->argument('name'))."\\Modules\\{$modelName}",
                    $modelName,
                    lcfirst(($modelName))
                ],
                $stub
            );
            $this->files->put($path, $stub);
            $this->info('Controller created successfull');
            //$this->updateModularConfig();
        }
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

}
