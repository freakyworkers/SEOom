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
            // 제목 폰트 사이즈 (px)
            $table->integer('header_image_text_title_size')->default(32)->after('header_image_text_valign');
            
            // 제목 폰트 컬러
            $table->string('header_image_text_title_color', 20)->default('#ffffff')->after('header_image_text_title_size');
            
            // 내용 폰트 사이즈 (px)
            $table->integer('header_image_text_content_size')->default(16)->after('header_image_text_title_color');
            
            // 내용 폰트 컬러
            $table->string('header_image_text_content_color', 20)->default('#ffffff')->after('header_image_text_content_size');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('boards', function (Blueprint $table) {
            $table->dropColumn([
                'header_image_text_title_size',
                'header_image_text_title_color',
                'header_image_text_content_size',
                'header_image_text_content_color',
            ]);
        });
    }
};
