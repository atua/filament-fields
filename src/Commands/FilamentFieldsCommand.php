<?php

namespace Atua\FilamentFields\Commands;

use Illuminate\Console\Command;

class FilamentFieldsCommand extends Command
{
    public $signature = 'filament-fields';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
