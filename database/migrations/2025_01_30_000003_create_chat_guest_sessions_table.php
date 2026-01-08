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
        Schema::create('chat_guest_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('sites')->onDelete('cascade');
            $table->string('session_id')->unique(); // 세션 ID
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->integer('guest_number'); // 게스트 번호 (1, 2, 3...)
            $table->timestamps();
            
            $table->index(['site_id', 'session_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_guest_sessions');
    }
};







