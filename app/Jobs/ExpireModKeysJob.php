<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Key;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ExpireModKeysJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Key::where('type', 0)
            ->whereNull('user_id')
            ->where('created_at', '<', Carbon::now('UTC')->subDays(3))
            ->delete();
    }
}