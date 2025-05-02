<?php

namespace App\Jobs;


use App\Enums\KeyTypesEnum;
use App\Models\Key;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ExpireModKeysJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public function handle(): void
    {
        $query = Key::where('type', KeyTypesEnum::MODERATOR)
            ->whereNull('user_id')
            ->where('created_at', '<', Carbon::now('UTC')->subDays(3));

        $key_ids = $query->pluck('id')->toArray();

        DB::table('keys_permissions')->whereIn('key_id', $key_ids)->delete();

        $query->delete();
    }
}
