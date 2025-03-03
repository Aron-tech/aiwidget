<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Site;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('keys', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Site::class)->nullable();  // Akkor nincs Site megadva tehát null, ha owner és még nincs beállítva az oldal
            $table->foreignIdFor(User::class)->nullable();
            $table->string('token')->unique();
            $table->smallInteger('type')->default(0); /// 0 = moderator, 1 = owner, 2 = superadmin
            $table->datetime('expiration_time');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('keys');
    }
};
