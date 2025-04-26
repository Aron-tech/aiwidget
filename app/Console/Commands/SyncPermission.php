<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Permission;

class SyncPermission extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync-permission';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronizes the permissions from the enum to the database.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $permissions = Permission::fromEnum();

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'value' => $permission->value,
            ]);
        }

        $this->info('Permissions synchronized successfully.');
    }
}
