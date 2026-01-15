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
        Schema::table('boards', function (Blueprint $table) {
            // 상단 이미지 가로 100%
            $table->boolean('header_image_full_width')->default(false)->after('header_image_path');
            
            // 이미지 위 텍스트 활성화
            $table->boolean('header_image_text_enabled')->default(false)->after('header_image_full_width');
            
            // 이미지 위 텍스트 제목
            $table->string('header_image_text_title')->nullable()->after('header_image_text_enabled');
            
            // 이미지 위 텍스트 내용
            $table->text('header_image_text_content')->nullable()->after('header_image_text_title');
            
            // 이미지 위 텍스트 가로 정렬 (left, center, right)
            $table->string('header_image_text_align', 10)->default('center')->after('header_image_text_content');
            
            // 이미지 위 텍스트 세로 정렬 (top, center, bottom)
            $table->string('header_image_text_valign', 10)->default('center')->after('header_image_text_align');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('boards', function (Blueprint $table) {
            $table->dropColumn([
                'header_image_full_width',
                'header_image_text_enabled',
                'header_image_text_title',
                'header_image_text_content',
                'header_image_text_align',
                'header_image_text_valign',
            ]);
        });
    }
};
