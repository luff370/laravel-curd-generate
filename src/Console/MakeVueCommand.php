<?php

namespace Peter\LaravelBuilder\Console;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Console\GeneratorCommand;

class MakeVueCommand extends GeneratorCommand
{
    use MakeCommandTrait;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'code:vue
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
    protected $description = 'Make Vue curd file';

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

        $path = $this->getBasePath().'pages/'.$this->moduleName . '/' . $this->generator->getModelName() . '/index.vue';
        if ($this->files->exists($path)) {
            $this->error("{$this->generator->getModelName()}/index.vue already exists!");
            return;
        }

        $this->makeDirectory($path);

        $stub = $this->files->get($this->getStub());
        $this->files->put($path, $this->replaceClass($stub, $this->modelName));

        $this->info($this->generator->getModelName() . '/index.vue created successfully.');

        // generate api
        $this->generatorApi();
        // add router
        $this->addRouter();
    }

    /**
     * 生成对应的API
     */
    protected function generatorApi()
    {
        $path = $this->getBasePath().'api/'  . $this->moduleName . '.js';
        $content = $this->generator->generateVueApi($this->title, $this->moduleName, $this->modelName, $this->generator->getModelName());

        if (!$this->files->exists($path)) {
            $content = "import request from '@/libs/request';\r\n" . $content;
        }

        $this->files->append($path, $content);
    }

    protected function addRouter()
    {
        $this->line('');
        $this->comment("Add the following route to src/router/{$this->moduleName}.js:");
        $this->line('');
        $this->info($this->generator->generateVueRouter($this->title, $this->moduleName, $this->generator->getModelName()));
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
        return str_replace(
            [
                'DummyTitle',
                'DummyNamespace',
                'DummyModelName',
                'DummyModelSnakeName',
                'DummyFilterFields',
                'DummyFilterForm',
                'DummyColumns',
            ],
            [
                $this->title,
                strtolower($this->namespace),
                $this->modelName,
                $this->generator->getModelName(),
                $this->indentCodes($this->generator->generateVueFilterField(), 8),
                $this->indentCodes($this->generator->generateVueFilterForm(), 12),
                $this->indentCodes($this->generator->generateVueColumn(), 8),
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

        return __DIR__ . "/stubs/index.vue.stub";
    }

    /**
     * 前端项目基础路径.
     *
     * @return string
     */
    protected function getBasePath()
    {
        return config('admin.view_path');
    }

}
