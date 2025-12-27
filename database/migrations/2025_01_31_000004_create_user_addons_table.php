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
        Schema::create('user_addons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('site_id')->constrained('sites')->onDelete('cascade');
            $table->foreignId('addon_product_id')->constrained('addon_products')->onDelete('cascade');
            $table->foreignId('subscription_id')->nullable()->constrained('subscriptions')->onDelete('set null');
            $table->bigInteger('amount_mb'); // 구매한 용량/트래픽 (MB)
            $table->decimal('price', 10, 2); // 구매 가격
            $table->enum('status', ['active', 'expired', 'canceled'])->default('active'); // 상태
            $table->date('expires_at')->nullable(); // 만료일 (월간 구매인 경우)
            $table->timestamps();
            
            $table->index(['site_id', 'status']);
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_addons');
    }
};




