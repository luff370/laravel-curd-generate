<?php

namespace Peter\LaravelBuilder;

use Illuminate\Support\ServiceProvider;

class CurdBuilderServiceProvider extends ServiceProvider
{
    /**
     * @var array
     */
    protected $commands = [
        Console\MakeCommand::class,
        Console\MakeControllerCommand::class,
        Console\MakeServiceCommand::class,
        Console\MakeDaoCommand::class,
        Console\MakeVueCommand::class,
    ];

    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot()
    {
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->commands($this->commands);
    }
}
