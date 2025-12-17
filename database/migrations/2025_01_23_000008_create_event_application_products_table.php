<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_application_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('sites')->onDelete('cascade');
            $table->string('thumbnail_path')->nullable();
            $table->string('item_name'); // 항목명 (ex: 사이트명)
            $table->string('item_content'); // 항목내용 (ex: 에그벳)
            $table->text('notice')->nullable(); // 공지
            $table->integer('pending_count')->default(0); // 대기
            $table->integer('completed_count')->default(0); // 완료
            $table->integer('rejected_count')->default(0); // 거절
            $table->integer('total_count')->default(0); // 총합
            $table->integer('order')->default(0);
            $table->timestamps();
            
            $table->index('site_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_application_products');
    }
};





