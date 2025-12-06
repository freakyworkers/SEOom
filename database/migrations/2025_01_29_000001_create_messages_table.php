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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('sites')->onDelete('cascade');
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('receiver_id')->constrained('users')->onDelete('cascade');
            $table->text('content');
            $table->boolean('is_read')->default(false);
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->timestamps();
            
            $table->index(['site_id', 'receiver_id']);
            $table->index(['receiver_id', 'is_read']);
            $table->index('created_at');
        });
        
        // parent_id 외래 키는 테이블 생성 후에 추가
        Schema::table('messages', function (Blueprint $table) {
            $table->foreign('parent_id')->references('id')->on('messages')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
        });
        Schema::dropIfExists('messages');
    }
};

