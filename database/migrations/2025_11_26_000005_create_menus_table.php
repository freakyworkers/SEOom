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
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('sites')->onDelete('cascade');
            $table->string('name'); // 메뉴 표시 이름
            $table->string('link_type'); // board, external_link, attendance, point_exchange, event_application
            $table->string('link_target')->nullable(); // 게시판 ID 또는 외부 링크 URL
            $table->foreignId('parent_id')->nullable()->constrained('menus')->onDelete('cascade');
            $table->integer('order')->default(0);
            $table->timestamps();
            
            $table->index(['site_id', 'parent_id']);
            $table->index('order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};

