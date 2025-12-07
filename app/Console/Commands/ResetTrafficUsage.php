<?php

namespace App\Console\Commands;

use App\Models\Site;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ResetTrafficUsage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sites:reset-traffic';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset traffic usage for all sites at the start of each month';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Resetting traffic usage for all sites...');
        
        $currentMonth = now()->startOfMonth()->toDateString();
        
        $sites = Site::where('is_master_site', false)
            ->where(function($query) use ($currentMonth) {
                $query->whereNull('traffic_reset_date')
                    ->orWhere('traffic_reset_date', '!=', $currentMonth);
            })
            ->get();
        
        $total = $sites->count();
        $bar = $this->output->createProgressBar($total);
        $bar->start();
        
        $reset = 0;
        
        foreach ($sites as $site) {
            try {
                $site->update([
                    'traffic_used_mb' => 0,
                    'traffic_reset_date' => $currentMonth,
                ]);
                
                $reset++;
            } catch (\Exception $e) {
                Log::error('Error resetting traffic for site ' . $site->id . ': ' . $e->getMessage());
                $this->error("\nError for site {$site->id}: " . $e->getMessage());
            }
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
        $this->info("Successfully reset traffic for {$reset} out of {$total} sites.");
        
        return Command::SUCCESS;
    }
}

