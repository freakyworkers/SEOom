<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_ranks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('sites')->onDelete('cascade');
            $table->integer('rank')->default(1); // 등급 번호
            $table->string('name'); // 등급 이름
            $table->enum('criteria_type', ['current_points', 'max_points', 'post_count'])->default('current_points'); // 기준 타입
            $table->integer('criteria_value')->default(0); // 기준 값
            $table->enum('display_type', ['icon', 'color'])->default('icon'); // 표시 타입
            $table->string('icon_path')->nullable(); // 아이콘 경로
            $table->string('color')->nullable(); // 색상 코드
            $table->integer('order')->default(0); // 순서
            $table->timestamps();

            $table->index(['site_id', 'rank']);
            $table->index('order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_ranks');
    }
};









