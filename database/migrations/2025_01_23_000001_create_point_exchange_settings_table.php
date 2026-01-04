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
        Schema::create('point_exchange_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('sites')->onDelete('cascade');
            $table->string('page_title')->default('포인트교환');
            $table->string('notice_title')->default('포인트 교환 필독 사항 안내');
            $table->json('notices')->nullable(); // 안내 항목들
            $table->integer('min_amount')->default(10000);
            $table->integer('max_amount')->default(100000);
            $table->json('form_fields')->nullable(); // 신청 폼 필드들
            $table->timestamps();
            
            $table->index('site_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('point_exchange_settings');
    }
};









