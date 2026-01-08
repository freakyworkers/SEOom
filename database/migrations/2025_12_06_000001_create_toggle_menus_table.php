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
        Schema::create('toggle_menus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('sites')->onDelete('cascade');
            $table->string('title'); // 토글 제목
            $table->text('content'); // 토글 내용
            $table->integer('order')->default(0); // 정렬 순서
            $table->boolean('is_active')->default(true); // 활성화 여부
            $table->timestamps();
            
            $table->index(['site_id', 'order']);
            $table->index(['site_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('toggle_menus');
    }
};







