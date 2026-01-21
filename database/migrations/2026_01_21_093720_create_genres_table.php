<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('genres', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('mal_id')->unique();
            $table->timestamps();
        });

        Schema::create('anime_genre', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anime_id')->constrained()->onDelete('cascade');
            $table->foreignId('genre_id')->constrained()->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anime_genre');
        Schema::dropIfExists('genres');
    }
};
