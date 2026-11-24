<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sites', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('business_name');
            $table->string('city')->nullable();
            $table->string('status')->default('draft'); // draft | published
            $table->string('ai_model')->nullable();
            $table->json('profile')->nullable();   // structured facts extracted from sources
            $table->json('conflicts')->nullable(); // fields where sources disagreed
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sites');
    }
};
