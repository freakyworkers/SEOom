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
        Schema::create('contact_form_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_form_id')->constrained()->onDelete('cascade');
            $table->foreignId('site_id')->constrained()->onDelete('cascade');
            $table->json('data'); // 제출된 데이터 (항목별 값들)
            $table->text('inquiry_content')->nullable(); // 문의내용
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_form_submissions');
    }
};








