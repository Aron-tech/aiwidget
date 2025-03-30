<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Site;
use App\Models\User;
use App\Enums\KeyTypesEnum;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('keys', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Site::class)->nullable();
            $table->foreignIdFor(User::class)->nullable();
            $table->string('token')->unique();
            $table->enum('type', array_column(KeyTypesEnum::cases(), 'value'))->default(KeyTypesEnum::MODERATOR->value);
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
