<?php

namespace Yuisa\VoltiaDatatable\Commands;

use Illuminate\Console\Command;

class VoltiaDatatableCommand extends Command
{
    public $signature = 'voltia-datatable';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
