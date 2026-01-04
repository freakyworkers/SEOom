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
        Schema::create('blocked_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('sites')->onDelete('cascade');
            $table->foreignId('blocker_id')->constrained('users')->onDelete('cascade'); // 차단한 사용자
            $table->foreignId('blocked_user_id')->nullable()->constrained('users')->onDelete('cascade'); // 차단당한 사용자
            $table->string('blocked_guest_session_id')->nullable(); // 차단당한 게스트 세션 ID
            $table->string('blocked_nickname'); // 차단당한 사용자 닉네임
            $table->timestamps();
            
            $table->unique(['site_id', 'blocker_id', 'blocked_user_id', 'blocked_guest_session_id'], 'unique_block');
            $table->index(['site_id', 'blocker_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blocked_users');
    }
};






