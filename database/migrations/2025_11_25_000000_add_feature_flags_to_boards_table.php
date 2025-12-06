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
        Schema::table('boards', function (Blueprint $table) {
            // enable_likes 컬럼이 있는지 확인
            $hasEnableLikes = Schema::hasColumn('boards', 'enable_likes');
            
            if (!Schema::hasColumn('boards', 'enable_anonymous')) {
                if ($hasEnableLikes) {
                    $table->boolean('enable_anonymous')->default(false)->after('enable_likes');
                } else {
                    $table->boolean('enable_anonymous')->default(false);
                }
            }
            if (!Schema::hasColumn('boards', 'enable_secret')) {
                if (Schema::hasColumn('boards', 'enable_anonymous')) {
                    $table->boolean('enable_secret')->default(false)->after('enable_anonymous');
                } else {
                    $table->boolean('enable_secret')->default(false);
                }
            }
            if (!Schema::hasColumn('boards', 'enable_reply')) {
                if (Schema::hasColumn('boards', 'enable_secret')) {
                    $table->boolean('enable_reply')->default(false)->after('enable_secret');
                } else {
                    $table->boolean('enable_reply')->default(false);
                }
            }
            if (!Schema::hasColumn('boards', 'exclude_from_rss')) {
                if (Schema::hasColumn('boards', 'enable_reply')) {
                    $table->boolean('exclude_from_rss')->default(false)->after('enable_reply');
                } else {
                    $table->boolean('exclude_from_rss')->default(false);
                }
            }
            if (!Schema::hasColumn('boards', 'prevent_drag')) {
                if (Schema::hasColumn('boards', 'exclude_from_rss')) {
                    $table->boolean('prevent_drag')->default(false)->after('exclude_from_rss');
                } else {
                    $table->boolean('prevent_drag')->default(false);
                }
            }
            if (!Schema::hasColumn('boards', 'enable_attachments')) {
                if (Schema::hasColumn('boards', 'prevent_drag')) {
                    $table->boolean('enable_attachments')->default(true)->after('prevent_drag');
                } else {
                    $table->boolean('enable_attachments')->default(true);
                }
            }
            if (!Schema::hasColumn('boards', 'enable_author_comment_adopt')) {
                if (Schema::hasColumn('boards', 'enable_attachments')) {
                    $table->boolean('enable_author_comment_adopt')->default(false)->after('enable_attachments');
                } else {
                    $table->boolean('enable_author_comment_adopt')->default(false);
                }
            }
            if (!Schema::hasColumn('boards', 'enable_admin_comment_adopt')) {
                if (Schema::hasColumn('boards', 'enable_author_comment_adopt')) {
                    $table->boolean('enable_admin_comment_adopt')->default(false)->after('enable_author_comment_adopt');
                } else {
                    $table->boolean('enable_admin_comment_adopt')->default(false);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('boards', function (Blueprint $table) {
            $table->dropColumn([
                'enable_anonymous',
                'enable_secret',
                'enable_reply',
                'exclude_from_rss',
                'prevent_drag',
                'enable_attachments',
                'enable_author_comment_adopt',
                'enable_admin_comment_adopt',
            ]);
        });
    }
};

