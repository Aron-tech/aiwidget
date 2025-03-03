<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Site;

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
            $table->smallInteger('status')->default(1); // 0 = inactive, 1 = open, 2 = waiting, 3 = closed
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
