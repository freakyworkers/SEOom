<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // MySQL에서 ENUM 타입을 수정하기 위해 ALTER TABLE 사용
        DB::statement("ALTER TABLE payments MODIFY COLUMN payment_type ENUM('trial', 'subscription', 'retry', 'plan_upgrade', 'plan_downgrade', 'plan_change', 'addon') DEFAULT 'subscription'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 원래 값으로 되돌리기
        DB::statement("ALTER TABLE payments MODIFY COLUMN payment_type ENUM('trial', 'subscription', 'retry') DEFAULT 'subscription'");
    }
};



