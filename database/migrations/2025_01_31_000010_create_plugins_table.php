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
        Schema::create('plugins', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // 플러그인 이름
            $table->string('slug')->unique(); // 플러그인 슬러그
            $table->text('description')->nullable(); // 플러그인 설명
            $table->decimal('price', 10, 2)->default(0); // 월간 가격
            $table->decimal('one_time_price', 10, 2)->default(0); // 일회성 가격
            $table->enum('billing_type', ['free', 'one_time', 'monthly'])->default('one_time'); // 결제 타입
            $table->json('features')->nullable(); // 포함된 기능 목록 (JSON)
            $table->string('image')->nullable(); // 플러그인 이미지
            $table->integer('sort_order')->default(0); // 정렬 순서
            $table->boolean('is_active')->default(true); // 활성화 여부
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plugins');
    }
};

