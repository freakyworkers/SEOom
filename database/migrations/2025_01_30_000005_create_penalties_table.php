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
        Schema::create('penalties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('sites')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade'); // 패널티 대상 사용자
            $table->string('guest_session_id')->nullable(); // 게스트 세션 ID
            $table->string('nickname'); // 패널티 대상 닉네임
            $table->string('type'); // 'chat_ban' (채팅금지), 'post_ban' (게시글작성차단)
            $table->foreignId('report_id')->nullable()->constrained('reports')->onDelete('set null'); // 관련 신고
            $table->text('reason')->nullable(); // 패널티 사유
            $table->timestamp('expires_at')->nullable(); // 만료 시간 (null이면 영구)
            $table->boolean('is_active')->default(true); // 활성 상태
            $table->foreignId('issued_by')->nullable()->constrained('users')->onDelete('set null'); // 패널티 부여한 관리자
            $table->timestamps();
            
            $table->index(['site_id', 'user_id', 'is_active']);
            $table->index(['site_id', 'guest_session_id', 'is_active']);
            $table->index(['type', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penalties');
    }
};


