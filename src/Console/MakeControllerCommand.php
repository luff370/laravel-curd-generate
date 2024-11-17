<?php

namespace Peter\LaravelBuilder\Console;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Console\GeneratorCommand;

class MakeControllerCommand extends GeneratorCommand
{
    use MakeCommandTrait;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'code:controller
        {--name=}
        {--model=}
        {--snp=}
        {--title=}
        {--stub= : Path to the custom stub file. }
        {--O|output}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make admin controller';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        if (!$this->initOptions()) {
            return;
        }

        // parent::handle();
        if (parent::handle() !== false) {
            // add route
            $this->addRoute($this->modelName, $this->controllerName);
            // generate lang
            $this->generatorLang($this->generator->generateLang());
        }
    }

    /**
     * 生成对应的语言文件
     *
     * @param string $content
     */
    protected function generatorLang(string $content)
    {
        $path = resource_path('lang/' . config('app.locale') . '/' . $this->generator->getModelName() . '.php');
        if (!$this->files->exists($path)) {
            $this->files->put($path, $content);
        }
    }

    protected function addRoute($modelName, $controllerName)
    {
        // $path = Str::plural(Str::kebab($modelName));
        $path = $this->generator->getModelName();

        $this->line('');
        $this->comment("Add the following route to app/routes/admin/{$this->moduleName}.php:");
        $this->line('');
        $this->info("    // {$this->title}管理");
        $this->info("    Route::resource('{$path}', '{$controllerName}');");
        $this->info("    Route::put('{$path}/set_field_value/{id}/{value}/{field}', '{$controllerName}::class@setFieldValue');");
        $this->line('');
    }

    /**
     * Replace the class name for the given stub.
     *
     * @param string $stub
     * @param string $name
     *
     * @return string
     */
    protected function replaceClass($stub, $name)
    {
        $stub = parent::replaceClass($stub, $name);

        return str_replace(
            [
                'DummyControllerNamespace',
                'DummyServiceNamespace',
                'DummyService',
                'DummyController',
                'DummySaveFields',
                'DummyFilterFields',
            ],
            [
                $this->controllerSpace,
                $this->serviceSpace,
                $this->serviceName,
                $this->controllerName,
                $this->indentCodes($this->generator->generateSaveFields(), 12),
                $this->indentCodes($this->generator->generateFilterFields(), 12),
            ],
            $stub
        );
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        if ($stub = $this->option('stub')) {
            return $stub;
        }

        return __DIR__ . "/stubs/controller.stub";
    }

    /**
     * Get the default namespace for the class.
     *
     * @param string $rootNamespace
     *
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $this->controllerSpace;
    }

    /**
     * Get the desired class name from the input.
     *
     * @return string
     */
    protected function getNameInput()
    {
        $name = trim($this->controllerName);

        $this->type = $this->qualifyClass($name);

        return $name;
    }
}
