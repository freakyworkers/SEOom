<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Crawler;
use App\Http\Controllers\AdminController;

class RunCrawlers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crawlers:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run all active crawlers';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $crawlers = Crawler::where('is_active', true)->get();
        
        if ($crawlers->isEmpty()) {
            $this->info('활성화된 크롤러가 없습니다.');
            return 0;
        }

        $this->info("총 {$crawlers->count()}개의 크롤러를 실행합니다...");
        
        $adminController = app(AdminController::class);
        $totalCount = 0;
        
        foreach ($crawlers as $crawler) {
            try {
                $this->line("크롤러 실행 중: {$crawler->name} ({$crawler->url})");
                
                // Use reflection to call private method
                $reflection = new \ReflectionClass($adminController);
                $method = $reflection->getMethod('runCrawler');
                $method->setAccessible(true);
                $count = $method->invoke($adminController, $crawler);
                
                $totalCount += $count;
                $this->info("✓ {$crawler->name}: {$count}개 게시글 크롤링 완료");
            } catch (\Exception $e) {
                $this->error("✗ {$crawler->name}: 오류 발생 - " . $e->getMessage());
                \Log::error('Crawler 실행 오류', [
                    'crawler_id' => $crawler->id,
                    'crawler_name' => $crawler->name,
                    'error' => $e->getMessage(),
                ]);
            }
        }
        
        $this->info("총 {$totalCount}개의 게시글이 크롤링되었습니다.");
        return 0;
    }
}
