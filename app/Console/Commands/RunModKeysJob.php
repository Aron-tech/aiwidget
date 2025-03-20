<?php

namespace App\Console\Commands;

use App\Jobs\ExpireModKeysJob;
use Illuminate\Console\Command;

class RunModKeysJob extends Command
{
    protected $signature = 'modkey:delete';
    protected $description = 'Törli a 3 napnál régebbi, inaktív moderátor kulcsokat';

    public function handle()
    {
        ExpireModKeysJob::dispatch();
        $this->info('ExpireModKeysJob job sikeresen elindítva.');
    }
}
