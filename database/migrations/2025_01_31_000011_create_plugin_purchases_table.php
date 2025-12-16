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
        Schema::create('plugin_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plugin_id')->constrained()->onDelete('cascade');
            $table->foreignId('site_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['active', 'inactive', 'expired', 'cancelled'])->default('active');
            $table->timestamp('purchased_at');
            $table->timestamp('expires_at')->nullable(); // 월간 구독인 경우 만료일
            $table->timestamps();
            
            $table->unique(['plugin_id', 'site_id']); // 한 사이트당 같은 플러그인은 하나만 구매 가능
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plugin_purchases');
    }
};

