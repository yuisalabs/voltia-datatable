<?php

namespace Yuisa\VoltiaDatatable\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class VoltiaDatatableCommand extends Command
{
    public $signature = 'make:datatable {name} {--model=}';

    public $description = 'Generate a new Voltia DataTable class';

    public function handle(): int
    {
        $name = $this->argument('name');
        $model = $this->option('model') ?? Str::studly(Str::singular($name));
        
        $className = Str::studly($name);
        if (!Str::endsWith($className, 'DataTable')) {
            $className .= 'DataTable';
        }

        $namespace = 'App\\Tables';
        $rootNamespace = 'App\\';
        
        $stub = file_get_contents(__DIR__ . '/../../resources/stubs/datatable.stub');
        
        $stub = str_replace(
            ['{{ namespace }}', '{{ class }}', '{{ model }}', '{{ rootNamespace }}'],
            [$namespace, $className, $model, $rootNamespace],
            $stub
        );

        $directory = app_path('Tables');
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $path = $directory . '/' . $className . '.php';
        
        if (file_exists($path)) {
            $this->error("DataTable {$className} already exists!");
            return self::FAILURE;
        }

        file_put_contents($path, $stub);

        $this->info("DataTable [{$path}] created successfully.");
        $this->newLine();
        $this->comment("Next steps:");
        $this->line("1. Update the query() method to customize your query");
        $this->line("2. Add or modify columns in the columns() method");
        $this->line("3. Add filters in the filters() method if needed");

        return self::SUCCESS;
    }
}
