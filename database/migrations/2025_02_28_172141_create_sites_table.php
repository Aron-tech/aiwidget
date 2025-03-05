<?php

use App\Models\Key;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sites', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique(); // Véletlenszerűen generált UUID amit ID helyett használunk azonosításra.
            $table->foreignIdFor(Key::class)->nullable(); // A kulcs amivel az oldalhoz hozzáférnek a felhasználók.
            $table->string('name');
            $table->string('domain')->unique();
            $table->json('settings')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sites');
    }
};
