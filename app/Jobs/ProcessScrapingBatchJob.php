<?php

namespace App\Jobs;

use App\Jobs\ScrapeNilaiJob;
use App\Jobs\ScrapeMahasiswaJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ProcessScrapingBatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 60; // 1 minute timeout for coordination
    public $tries = 3;

    protected int $jurusanId;
    protected string $semester;
    protected string $cookie;
    protected array $scrapingTypes;
    protected string $batchId;

    public function __construct(int $jurusanId, string $semester, string $cookie, array $scrapingTypes, string $batchId = null)
    {
        $this->jurusanId = $jurusanId;
        $this->semester = $semester;
        $this->cookie = $cookie;
        $this->scrapingTypes = $scrapingTypes;
        $this->batchId = $batchId ?? uniqid('batch_');
    }

    public function handle(): void
    {
        Log::info("Starting batch scraping job for jurusan: {$this->jurusanId}, semester: {$this->semester}, types: " . implode(',', $this->scrapingTypes));

        $this->updateBatchProgress(0, 'Memulai batch scraping...');

        try {
            $dispatchedJobs = [];
            $totalJobs = count($this->scrapingTypes);
            $jobsDispatched = 0;

            foreach ($this->scrapingTypes as $type) {
                $jobId = uniqid("{$type}_");

                switch ($type) {
                    case 'nilai':
                        $job = new ScrapeNilaiJob($this->jurusanId, $this->semester, $this->cookie, $jobId);
                        dispatch($job);
                        $dispatchedJobs[$jobId] = ['type' => 'nilai', 'job' => $job];
                        break;

                    case 'mahasiswa':
                        $job = new ScrapeMahasiswaJob($this->jurusanId, $this->semester, $this->cookie, $jobId);
                        dispatch($job);
                        $dispatchedJobs[$jobId] = ['type' => 'mahasiswa', 'job' => $job];
                        break;

                    default:
                        Log::warning("Unknown scraping type: {$type}");
                        continue 2;
                }

                $jobsDispatched++;
                $progress = ($jobsDispatched / $totalJobs) * 100;
                $this->updateBatchProgress($progress, "Dispatched {$type} job ({$jobsDispatched}/{$totalJobs})");
            }

            // Store batch information
            $batchData = [
                'batch_id' => $this->batchId,
                'jurusan_id' => $this->jurusanId,
                'semester' => $this->semester,
                'types' => $this->scrapingTypes,
                'jobs' => array_keys($dispatchedJobs),
                'total_jobs' => count($dispatchedJobs),
                'dispatched_at' => now()->toISOString(),
                'status' => 'dispatched'
            ];

            Cache::put("batch_scraping_{$this->batchId}", $batchData, 3600);

            $this->updateBatchProgress(100, "Berhasil men-dispatch {$jobsDispatched} jobs", $batchData);

            Log::info("Batch scraping jobs dispatched successfully. Batch ID: {$this->batchId}, Jobs: " . count($dispatchedJobs));
        } catch (\Exception $e) {
            $errorMessage = "Batch scraping gagal: " . $e->getMessage();
            $this->updateBatchProgress(0, $errorMessage, [
                'status' => 'failed',
                'error' => $e->getMessage()
            ]);

            Log::error("Batch scraping job failed: " . $e->getMessage());
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        $errorMessage = "Batch job gagal: " . $exception->getMessage();
        $this->updateBatchProgress(0, $errorMessage, [
            'status' => 'failed',
            'error' => $exception->getMessage()
        ]);

        Log::error("ProcessScrapingBatchJob failed: " . $exception->getMessage());
    }

    protected function updateBatchProgress(float $percentage, string $message, array $additionalData = []): void
    {
        $data = array_merge([
            'batch_id' => $this->batchId,
            'type' => 'batch',
            'jurusan_id' => $this->jurusanId,
            'semester' => $this->semester,
            'scraping_types' => $this->scrapingTypes,
            'progress' => $percentage,
            'message' => $message,
            'updated_at' => now()->toISOString(),
        ], $additionalData);

        Cache::put("batch_progress_{$this->batchId}", $data, 3600);

        // Also store in a general key for UI polling
        Cache::put("batch_progress_latest", $data, 3600);
    }

    public function getBatchId(): string
    {
        return $this->batchId;
    }
}
