<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class StartQueueWorker extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scraper:start-queue-worker {--timeout=3600 : Worker timeout in seconds} {--memory=512 : Memory limit in MB}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start queue worker for scraping jobs with optimal settings';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $timeout = $this->option('timeout');
        $memory = $this->option('memory');

        $this->info('Starting queue worker for scraping jobs...');
        $this->info("Timeout: {$timeout} seconds");
        $this->info("Memory limit: {$memory} MB");
        $this->newLine();

        // Start the queue worker with optimal settings for scraping
        Artisan::call('queue:work', [
            '--timeout' => $timeout,
            '--memory' => $memory,
            '--tries' => 3,
            '--delay' => 10,
            '--sleep' => 3,
            '--max-jobs' => 10,
            '--max-time' => 3600,
            '--verbose' => true,
        ]);

        $this->info('Queue worker started successfully!');
        $this->info('You can now run scraping jobs and they will be processed in the background.');
        $this->newLine();
        $this->comment('To monitor jobs, you can:');
        $this->comment('- Check the jobs table in your database');
        $this->comment('- Use the web interface progress tracking');
        $this->comment('- Monitor logs in storage/logs/');
    }
}
