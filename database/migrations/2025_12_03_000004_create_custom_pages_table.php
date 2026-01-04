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
        Schema::create('custom_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('sites')->onDelete('cascade');
            $table->string('name'); // 페이지 이름
            $table->string('slug')->unique(); // 연결 주소 (슬러그)
            $table->text('description')->nullable(); // 설명
            $table->boolean('is_active')->default(true); // 활성화 여부
            $table->timestamps();
            
            $table->index(['site_id', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_pages');
    }
};







