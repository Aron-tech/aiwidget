<?php

use App\Jobs\ExpireModKeysJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

use Illuminate\Support\Facades\Schedule;
use App\Console\Commands\RunModKeysJob;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('modkey:delete')->daily();

//Schedule::job(new ExpireModKeysJob)->everyMinute();