<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manga_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('manga_id')->constrained()->cascadeOnDelete();
            
            $table->string('status')->default('reading');
            $table->integer('chapters_read')->default(0);
            $table->integer('volumes_owned')->default(0);
            $table->integer('score')->default(0);
            $table->integer('pantheon_rank')->nullable(); 
            $table->boolean('is_stu')->default(false);
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manga_user');
    }
};