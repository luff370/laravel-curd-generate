<?php

namespace Peter\LaravelBuilder\Console;

use Illuminate\Console\GeneratorCommand;

class MakeServiceCommand extends GeneratorCommand
{
    use MakeCommandTrait;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'code:service
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
    protected $description = 'Make admin service';

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
                'DummyServiceNamespace',
                'DummyDaoNamespace',
                'DummyDao',
                'DummyService',
                'DummyUrl',
                'DummyTidyList',
                'DummyForm',
            ],
            [
                $this->serviceSpace,
                $this->daoSpace,
                $this->daoName,
                $this->serviceName,
                $this->modelUrl,
                $this->indentCodes($this->generator->generateTidyList(), 12),
                $this->indentCodes($this->generator->generateForm()),
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

        return __DIR__ . "/stubs/service.stub";
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
        return $this->serviceSpace;
    }

    /**
     * Get the desired class name from the input.
     *
     * @return string
     */
    protected function getNameInput()
    {
        $name = $this->serviceName;

        $this->type = $this->qualifyClass($name);

        return $name;
    }
}
