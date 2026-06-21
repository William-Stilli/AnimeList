<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::create('rewatches', function (Blueprint $table) {
        $table->id();
        
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        
        $table->foreignId('anime_id')->constrained()->cascadeOnDelete();
        
        $table->unsignedInteger('start_episode');
        $table->unsignedInteger('end_episode');
        $table->timestamps(); 
    });
}

    public function down(): void
    {
        Schema::dropIfExists('rewatches');
    }
};
