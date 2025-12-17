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
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->date('reminder_sent_7days')->nullable()->after('last_payment_failed_at'); // 7일전 알림 전송일
            $table->date('reminder_sent_3days')->nullable()->after('reminder_sent_7days'); // 3일전 알림 전송일
            $table->date('reminder_sent_1day')->nullable()->after('reminder_sent_3days'); // 1일전 알림 전송일
            $table->date('failure_notification_sent_at')->nullable()->after('reminder_sent_1day'); // 결제 실패 알림 전송일
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn([
                'reminder_sent_7days',
                'reminder_sent_3days',
                'reminder_sent_1day',
                'failure_notification_sent_at',
            ]);
        });
    }
};



