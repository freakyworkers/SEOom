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
        Schema::create('mobile_menus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('sites')->onDelete('cascade');
            $table->string('icon_type')->default('default'); // 'image' or 'default'
            $table->string('icon_path')->nullable(); // 이미지 경로 또는 기본 아이콘 이름
                    $table->string('name')->nullable();
            $table->string('link_type'); // board, external_link, attendance, point_exchange, event_application
            $table->string('link_target')->nullable(); // 게시판 ID 또는 외부 링크 URL
            $table->integer('order')->default(0);
            $table->timestamps();
            
            $table->index(['site_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mobile_menus');
    }
};

