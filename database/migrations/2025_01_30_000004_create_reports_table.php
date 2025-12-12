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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('sites')->onDelete('cascade');
            $table->foreignId('reporter_id')->nullable()->constrained('users')->onDelete('set null'); // 신고자 (로그인 사용자)
            $table->string('reporter_guest_session_id')->nullable(); // 신고자 게스트 세션 ID
            $table->string('reporter_nickname'); // 신고자 닉네임
            $table->foreignId('reported_user_id')->nullable()->constrained('users')->onDelete('cascade'); // 신고당한 사용자
            $table->string('reported_guest_session_id')->nullable(); // 신고당한 게스트 세션 ID
            $table->string('reported_nickname'); // 신고당한 사용자 닉네임
            $table->string('report_type'); // 'post', 'comment', 또는 'chat'
            $table->foreignId('post_id')->nullable()->constrained('posts')->onDelete('cascade'); // 신고된 게시글
            $table->foreignId('chat_message_id')->nullable()->constrained('chat_messages')->onDelete('cascade'); // 신고된 채팅 메시지
            $table->text('reason')->nullable(); // 신고 사유
            $table->string('status')->default('pending'); // pending, reviewed, resolved, dismissed
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null'); // 검토한 관리자
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            
            $table->index(['site_id', 'status']);
            $table->index(['report_type', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};


