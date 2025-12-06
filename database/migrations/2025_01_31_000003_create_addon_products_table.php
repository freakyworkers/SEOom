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
        Schema::create('addon_products', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // 상품명 (예: "추가 저장 용량 10GB")
            $table->string('slug')->unique(); // 상품 슬러그
            $table->text('description')->nullable(); // 상품 설명
            $table->string('type', 50); // 상품 타입 (저장 용량, 트래픽, 기능 등)
            $table->bigInteger('amount_mb'); // 제공 용량/트래픽 (MB)
            $table->decimal('price', 10, 2); // 가격
            $table->enum('billing_cycle', ['one_time', 'monthly'])->default('one_time'); // 결제 주기 (일회성 또는 월간)
            $table->boolean('is_active')->default(true); // 활성화 여부
            $table->integer('sort_order')->default(0); // 정렬 순서
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addon_products');
    }
};

