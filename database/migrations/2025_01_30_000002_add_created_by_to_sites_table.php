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
        Schema::table('sites', function (Blueprint $table) {
            // is_master_site 컬럼이 있으면 그 뒤에, 없으면 status 컬럼 뒤에 추가
            if (Schema::hasColumn('sites', 'is_master_site')) {
                $table->foreignId('created_by')->nullable()->after('is_master_site')->constrained('users')->onDelete('set null');
            } elseif (Schema::hasColumn('sites', 'status')) {
                $table->foreignId('created_by')->nullable()->after('status')->constrained('users')->onDelete('set null');
            } else {
                $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn('created_by');
        });
    }
};


