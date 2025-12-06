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
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // 플랜 이름 (예: "랜딩페이지 플랜")
            $table->string('slug')->unique(); // 플랜 슬러그 (예: "landing", "brand", "community")
            $table->text('description')->nullable(); // 플랜 설명
            $table->enum('type', ['landing', 'brand', 'community'])->default('landing'); // 플랜 타입
            $table->decimal('price', 10, 2)->default(0); // 월간 가격
            $table->json('features')->nullable(); // 포함된 기능 목록 (JSON)
            $table->json('limits')->nullable(); // 제한 사항 (JSON)
            $table->integer('sort_order')->default(0); // 정렬 순서
            $table->boolean('is_active')->default(true); // 활성화 여부
            $table->boolean('is_default')->default(false); // 기본 플랜 여부
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
