<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('animes', function (Blueprint $table) {
            $table->text('synopsis')->nullable();
            $table->string('type')->nullable();
            $table->string('source')->nullable();
            $table->integer('year')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('animes', function (Blueprint $table) {
            //
        });
    }
};
