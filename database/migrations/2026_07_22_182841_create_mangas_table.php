<?php

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
        Schema::create('mangas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            
            $table->unsignedBigInteger('mal_id');
            $table->string('title');
            $table->string('image_url')->nullable();
            
            $table->integer('chapters_read')->default(0);
            $table->integer('volumes_owned')->default(0); 
            $table->string('status')->default('Plan to Read'); 
            $table->integer('score')->nullable();
            
            $table->timestamps();

            $table->unique(['user_id', 'mal_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mangas');
    }
};
