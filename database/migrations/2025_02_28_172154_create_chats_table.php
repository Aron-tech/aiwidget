<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Site;
use App\Enums\ChatStatusEnum;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('chats', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Site::class)->constrained()->cascadeOnDelete();
            $table->string('visitor_name');
            $table->string('visitor_email');
            $table->enum('status', array_column(ChatStatusEnum::cases(), 'value'))->default(ChatStatusEnum::OPEN->value);
            $table->index(['visitor_name', 'visitor_email']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chats');
    }
};
