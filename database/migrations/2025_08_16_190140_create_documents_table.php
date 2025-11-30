<?php

use App\Enums\FileStatusEnum;
use App\Enums\FileTypeEnum;
use App\Models\Site;
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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Site::class)->constrained();
            $table->string('title');
            $table->string('path')->unique();
            $table->enum('type', array_column(FileTypeEnum::cases(), 'value'));
            $table->enum('status', array_column(FileStatusEnum::cases(), 'value'));
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
