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
        Schema::create('custom_page_widget_containers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('custom_page_id')->constrained('custom_pages')->onDelete('cascade');
            $table->integer('columns')->default(1); // 가로 개수 (1, 2, 3, 4)
            $table->string('vertical_align')->default('top'); // 상단, 중앙, 하단
            $table->integer('order')->default(0); // 정렬 순서
            $table->timestamps();
            
            $table->index(['custom_page_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_page_widget_containers');
    }
};






