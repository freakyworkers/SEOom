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
        Schema::create('addon_product_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('addon_product_id')->constrained('addon_products')->onDelete('cascade');
            $table->string('name'); // 옵션명 (예: "10000MB", "20000MB")
            $table->bigInteger('amount_mb')->nullable(); // 용량/트래픽 (MB) - 리소스 타입일 때만
            $table->decimal('price', 10, 2); // 가격
            $table->integer('sort_order')->default(0); // 정렬 순서
            $table->boolean('is_active')->default(true); // 활성화 여부
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addon_product_options');
    }
};


