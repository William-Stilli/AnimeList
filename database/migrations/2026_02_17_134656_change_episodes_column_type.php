<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('animes', function (Blueprint $table) {
            $table->integer('episodes')->nullable()->change();
        });

        Schema::table('anime_user', function (Blueprint $table) {
            $table->integer('progress')->default(0)->change();
        });
    }

    public function down(): void
    {
    }
};