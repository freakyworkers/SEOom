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
        Schema::table('posts', function (Blueprint $table) {
            if (!Schema::hasColumn('posts', 'event_start_date')) {
                $table->date('event_start_date')->nullable()->after('reply_to');
            }
            if (!Schema::hasColumn('posts', 'event_end_date')) {
                $table->date('event_end_date')->nullable()->after('event_start_date');
            }
            if (!Schema::hasColumn('posts', 'event_end_undecided')) {
                $table->boolean('event_end_undecided')->default(false)->after('event_end_date');
            }
            if (!Schema::hasColumn('posts', 'event_type')) {
                $table->string('event_type')->nullable()->after('event_end_undecided')->comment('이벤트 유형: general, quiz');
            }
            if (!Schema::hasColumn('posts', 'event_is_ended')) {
                $table->boolean('event_is_ended')->default(false)->after('event_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            if (Schema::hasColumn('posts', 'event_is_ended')) {
                $table->dropColumn('event_is_ended');
            }
            if (Schema::hasColumn('posts', 'event_type')) {
                $table->dropColumn('event_type');
            }
            if (Schema::hasColumn('posts', 'event_end_undecided')) {
                $table->dropColumn('event_end_undecided');
            }
            if (Schema::hasColumn('posts', 'event_end_date')) {
                $table->dropColumn('event_end_date');
            }
            if (Schema::hasColumn('posts', 'event_start_date')) {
                $table->dropColumn('event_start_date');
            }
        });
    }
};






