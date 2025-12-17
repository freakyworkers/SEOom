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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('sites')->onDelete('cascade');
            $table->foreignId('plan_id')->constrained('plans')->onDelete('cascade');
            $table->string('toss_payment_key')->nullable(); // 토스 페이먼츠 결제 키
            $table->string('toss_billing_key')->nullable(); // 토스 페이먼츠 빌링 키 (정기결제용)
            $table->enum('status', ['trial', 'active', 'past_due', 'canceled', 'suspended'])->default('trial');
            $table->date('trial_ends_at')->nullable(); // 무료 기간 종료일
            $table->date('current_period_start')->nullable(); // 현재 결제 기간 시작일
            $table->date('current_period_end')->nullable(); // 현재 결제 기간 종료일
            $table->date('canceled_at')->nullable(); // 구독 취소일
            $table->integer('retry_count')->default(0); // 재결제 시도 횟수
            $table->date('last_payment_failed_at')->nullable(); // 마지막 결제 실패일
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};



