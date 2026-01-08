<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_application_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('sites')->onDelete('cascade');
            $table->string('page_title')->default('신청형 이벤트');
            $table->string('notice_title')->default('신청형 이벤트 필독 사항 안내');
            $table->json('notices')->nullable(); // 안내 항목들
            $table->json('form_fields')->nullable(); // 신청 폼 필드들
            $table->json('requirements')->nullable(); // 신청 조건
            $table->boolean('random_order')->default(false);
            $table->integer('products_per_page')->default(12);
            $table->integer('pc_columns')->default(4);
            $table->integer('mobile_columns')->default(2);
            $table->timestamps();
            
            $table->index('site_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_application_settings');
    }
};









