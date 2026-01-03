<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ChatMessage;
use App\Models\ChatSetting;
use Carbon\Carbon;

class DeleteOldChatMessages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chat:delete-old-messages';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete chat messages older than 24 hours if auto_delete_24h is enabled';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to delete old chat messages...');

        // Get all sites with auto_delete_24h enabled
        $sites = ChatSetting::where('auto_delete_24h', true)
            ->with('site')
            ->get();

        $totalDeleted = 0;

        foreach ($sites as $chatSetting) {
            $site = $chatSetting->site;
            if (!$site) {
                continue;
            }

            // Delete messages older than 24 hours
            $deleted = ChatMessage::where('site_id', $site->id)
                ->where('created_at', '<', Carbon::now()->subHours(24))
                ->delete();

            $totalDeleted += $deleted;

            if ($deleted > 0) {
                $this->info("Deleted {$deleted} messages from site: {$site->name}");
            }
        }

        $this->info("Total deleted: {$totalDeleted} messages");
        $this->info('Finished deleting old chat messages.');

        return 0;
    }
}





