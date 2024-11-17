<?php

namespace Peter\LaravelBuilder\Console;

use Illuminate\Console\Command;

class MakeCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'code:make
        {--name=}
        {--model=}
        {--snp=}
        {--title=}
        {--O|output}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make CURD code.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->call('code:controller', [
            '--name' => $this->option('name'), '--model' => $this->option('model'), '--snp'=>$this->option('snp')
        ]);
        $this->call('code:service', [
            '--name' => $this->option('name'), '--model' => $this->option('model'), '--snp'=>$this->option('snp')
        ]);
        $this->call('code:dao', [
            '--name' => $this->option('name'), '--model' => $this->option('model'), '--snp'=>$this->option('snp')
        ]);
        $this->call('code:vue', [
            '--name' => $this->option('name'), '--model' => $this->option('model'), '--snp'=>$this->option('snp'), '--title'=>$this->option('title')
        ]);
    }
}
