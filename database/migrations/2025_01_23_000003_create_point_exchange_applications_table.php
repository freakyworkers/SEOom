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
        Schema::create('point_exchange_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('sites')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('point_exchange_products')->onDelete('cascade');
            $table->integer('points'); // 신청 포인트
            $table->string('status')->default('pending'); // pending, completed, rejected, cancelled
            $table->text('rejection_reason')->nullable(); // 거절 사유
            $table->json('form_data')->nullable(); // 신청 폼 데이터 (은행명, 계좌번호 등)
            $table->timestamps();
            
            $table->index(['site_id', 'user_id']);
            $table->index(['site_id', 'product_id']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('point_exchange_applications');
    }
};








