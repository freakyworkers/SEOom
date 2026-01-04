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
        Schema::create('main_widgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('sites')->onDelete('cascade');
            $table->foreignId('container_id')->constrained('main_widget_containers')->onDelete('cascade');
            $table->integer('column_index')->default(0); // 컨테이너 내 칸 인덱스 (0부터 시작)
            $table->string('type'); // 위젯 타입 (recent_posts, popular_posts, board_list, etc.)
            $table->string('title'); // 위젯 제목
            $table->text('settings')->nullable(); // 위젯 설정 (JSON)
            $table->integer('order')->default(0); // 칸 내 정렬 순서
            $table->boolean('is_active')->default(true); // 활성화 여부
            $table->timestamps();
            
            $table->index(['container_id', 'column_index', 'order']);
            $table->index(['site_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('main_widgets');
    }
};








