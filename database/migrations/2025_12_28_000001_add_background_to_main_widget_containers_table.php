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
        Schema::table('main_widget_containers', function (Blueprint $table) {
            $table->string('background_type')->nullable()->after('widget_spacing'); // 'none', 'color', 'gradient', 'image'
            $table->string('background_color')->nullable()->after('background_type'); // 단색 색상
            $table->string('background_gradient_start')->nullable()->after('background_color'); // 그라데이션 시작 색상
            $table->string('background_gradient_end')->nullable()->after('background_gradient_start'); // 그라데이션 끝 색상
            $table->string('background_gradient_direction')->nullable()->after('background_gradient_end'); // 그라데이션 방향 (to right, to bottom, 45deg 등)
            $table->string('background_image_url')->nullable()->after('background_gradient_direction'); // 배경 이미지 URL
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('main_widget_containers', function (Blueprint $table) {
            $table->dropColumn([
                'background_type',
                'background_color',
                'background_gradient_start',
                'background_gradient_end',
                'background_gradient_direction',
                'background_image_url',
            ]);
        });
    }
};

