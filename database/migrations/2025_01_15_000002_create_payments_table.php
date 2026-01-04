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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained('subscriptions')->onDelete('cascade');
            $table->foreignId('site_id')->constrained('sites')->onDelete('cascade');
            $table->string('toss_payment_key')->nullable(); // 토스 페이먼츠 결제 키
            $table->string('toss_order_id')->nullable(); // 토스 페이먼츠 주문 ID
            $table->decimal('amount', 10, 2); // 결제 금액
            $table->enum('status', ['pending', 'paid', 'failed', 'canceled', 'refunded'])->default('pending');
            $table->enum('payment_type', ['trial', 'subscription', 'retry'])->default('subscription');
            $table->date('paid_at')->nullable(); // 결제 완료일
            $table->date('failed_at')->nullable(); // 결제 실패일
            $table->text('failure_reason')->nullable(); // 실패 사유
            $table->json('toss_response')->nullable(); // 토스 페이먼츠 응답 데이터
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};






