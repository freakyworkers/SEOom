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
        Schema::create('event_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('posts')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('event_option_id')->nullable()->constrained('event_options')->onDelete('set null');
            $table->integer('points_awarded')->default(0)->comment('지급된 포인트');
            $table->boolean('is_correct')->default(false)->comment('정답 여부');
            $table->timestamps();
            
            $table->unique(['post_id', 'user_id']);
            $table->index('post_id');
            $table->index('user_id');
            $table->index('event_option_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_participants');
    }
};











