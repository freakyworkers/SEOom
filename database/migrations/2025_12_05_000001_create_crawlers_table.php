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
        Schema::create('crawlers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('sites')->onDelete('cascade');
            $table->string('name'); // 크롤러 이름
            $table->text('url'); // 크롤링 목록 URL
            $table->string('list_title_selector'); // 리스트 제목 a태그 선택자
            $table->string('post_title_selector'); // 게시글 제목 선택자
            $table->string('post_content_selector'); // 게시글 본문 선택자
            $table->foreignId('board_id')->constrained('boards')->onDelete('cascade'); // 크롤링 대상 게시판
            $table->foreignId('topic_id')->nullable()->constrained('topics')->onDelete('set null'); // 주제
            $table->string('author_nickname')->nullable(); // 작성자 닉네임
            $table->boolean('use_random_user')->default(false); // 랜덤유저 사용 여부
            $table->boolean('is_active')->default(true); // 활성화 여부
            $table->integer('total_count')->default(0); // 총 수량
            $table->timestamp('last_crawled_at')->nullable(); // 최근 크롤링 시간
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crawlers');
    }
};








