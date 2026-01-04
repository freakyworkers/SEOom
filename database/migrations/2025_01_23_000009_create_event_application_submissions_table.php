<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_application_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('sites')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('event_application_products')->onDelete('cascade');
            $table->string('status')->default('pending'); // pending, completed, rejected, cancelled
            $table->text('rejection_reason')->nullable(); // 거절 사유
            $table->json('form_data')->nullable(); // 신청 폼 데이터
            $table->timestamps();
            
            $table->index(['site_id', 'user_id']);
            $table->index(['site_id', 'product_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_application_submissions');
    }
};








