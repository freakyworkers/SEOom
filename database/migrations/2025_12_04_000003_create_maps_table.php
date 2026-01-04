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
        Schema::create('maps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('sites')->onDelete('cascade');
            $table->string('name'); // 지도 이름
            $table->enum('map_type', ['google', 'kakao', 'naver'])->default('google'); // 지도 타입
            $table->string('address'); // 주소
            $table->decimal('latitude', 10, 8)->nullable(); // 위도
            $table->decimal('longitude', 11, 8)->nullable(); // 경도
            $table->integer('zoom')->default(15); // 줌 레벨
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maps');
    }
};







