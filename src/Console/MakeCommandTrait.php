<?php

namespace Peter\LaravelBuilder\Console;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

trait MakeCommandTrait
{
    /**
     * @var ResourceGenerator
     */
    protected $generator;

    protected $controllerName;

    protected $serviceName;

    protected $daoName;

    protected $modelName;

    protected $name;

    protected $title;

    protected $model;

    protected $modelUrl = '/admin';

    protected $namespace;

    protected $moduleName;

    protected $modelSpace = 'App\\Models';

    protected $daoSpace = 'App\\Dao';

    protected $controllerSpace = 'App\\Http\\Controllers\\Admin';

    protected $serviceSpace = 'App\\Services';

    protected function initOptions()
    {
        if (!$this->modelExists()) {
            return false;
        }

        $stub = $this->option('stub');
        if ($stub and !is_file($stub)) {
            $this->error('The stub file dose not exist.');

            return false;
        }

        $this->namespace = $this->option('snp');
        if (empty($this->namespace)) {
            $this->error('The namespace is required!');

            return false;
        }

        $this->title = $this->option('title') ?? '';
        $this->modelName = $modelName = $this->option('model');
        $this->name = $name = $this->option('name') ?? $modelName;

        $this->model = $this->modelSpace . '\\' . $this->modelName;
        if (!class_exists($this->model)) {
            $this->model = $this->modelSpace . '\\' . $this->namespace . '\\' . $this->modelName;
        }
        if (!(class_exists($this->model) && is_subclass_of($this->model, Model::class))) {
            $model = $this->modelSpace . '\\' . $modelName;
            $this->info('Model: ' . $model);
            $this->error('Model does not exists !');
        }

        $this->controllerName = $name . 'Controller';
        $this->serviceName = $name . 'Service';
        $this->daoName = $name . 'Dao';

        $this->controllerSpace = $this->controllerSpace . '\\' . $this->namespace;
        $this->serviceSpace = $this->serviceSpace . '\\' . $this->namespace;
        $this->daoSpace = $this->daoSpace . '\\' . $this->namespace;
        $this->modelUrl .= '/' . Str::snake($this->namespace);
        $this->moduleName = strtolower($this->namespace);
        $this->modelUrl .= '/' . Str::snake($name);

        $this->generator = new ResourceGenerator($this->model);

        return true;
    }

    /**
     * Determine if the model is exists.
     *
     * @return bool
     */
    protected function modelExists()
    {
        $modelName = $this->option('model');
        if (empty($modelName)) {
            $this->error('Please specify the model name.');

            return false;
        }

        return true;

        // $model = $this->modelSpace . '\\' . $modelName;
        // $this->info('Model: ' . $model);
        //
        // return class_exists($model) && is_subclass_of($model, Model::class);
    }

    /**
     * @param string $code
     *
     * @return string
     */
    protected function indentCodes($code, $indent = 8)
    {
        $indent = str_repeat(' ', $indent);

        return rtrim($indent . preg_replace("/\r\n/", "\r\n{$indent}", $code));
    }
}