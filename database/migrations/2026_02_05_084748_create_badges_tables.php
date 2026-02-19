<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->string('description');
            $table->string('icon')->nullable();

            $table->string('color')->default('gray');
            $table->string('condition_type')->default('manual');
            $table->integer('condition_value')->default(0);
            $table->json('metadata')->nullable();

            $table->integer('xp_bonus')->default(0);
            $table->timestamps();
        });

        Schema::create('badge_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('badge_id')->constrained()->cascadeOnDelete();
            $table->timestamp('unlocked_at')->useCurrent();
            $table->unique(['user_id', 'badge_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('badge_user');
        Schema::dropIfExists('badges');
    }
};
