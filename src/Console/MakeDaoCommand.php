<?php

namespace Peter\LaravelBuilder\Console;

use Illuminate\Console\GeneratorCommand;

class MakeDaoCommand extends GeneratorCommand
{
    use MakeCommandTrait;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'code:dao
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
    protected $description = 'Make admin dao';

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

        parent::handle();
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
                'DummyModelNamespace',
                'DummyDaoNamespace',
                'DummyModel',
                'DummyDao',
            ],
            [
                $this->modelSpace,
                $this->daoSpace,
                $this->modelName,
                $this->daoName,
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

        return __DIR__ . "/stubs/dao.stub";
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
        return $this->daoSpace;
    }

    /**
     * Get the desired class name from the input.
     *
     * @return string
     */
    protected function getNameInput()
    {
        $name = $this->daoName;

        $this->type = $this->qualifyClass($name);

        return $name;
    }
}
