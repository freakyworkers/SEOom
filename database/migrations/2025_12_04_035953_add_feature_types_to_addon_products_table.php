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
        Schema::table('addon_products', function (Blueprint $table) {
            // type enum을 더 넓은 string으로 변경하여 새로운 타입들을 추가할 수 있도록 함
            $table->string('type', 50)->change();
        });
        
        // 기존 enum 값들을 유지하면서 새로운 타입들을 추가할 수 있도록 DB::statement 사용
        \DB::statement("ALTER TABLE addon_products MODIFY COLUMN type VARCHAR(50) NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('addon_products', function (Blueprint $table) {
            //
        });
    }
};
