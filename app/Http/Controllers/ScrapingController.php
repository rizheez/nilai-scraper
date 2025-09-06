<?php

namespace App\Http\Controllers;

use App\Models\Jurusan;
use App\Models\Semester;
use App\Models\MataKuliah;
use App\Models\Mahasiswa;
use App\Models\Nilai;
use App\Models\Bobot;
use App\Services\SiakadScraperService;
use App\Jobs\ScrapeNilaiJob;
use App\Jobs\ScrapeMahasiswaJob;
use App\Jobs\ProcessScrapingBatchJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class ScrapingController extends Controller
{
    protected SiakadScraperService $scraperService;

    public function __construct(SiakadScraperService $scraperService)
    {
        $this->scraperService = $scraperService;
    }

    public function status()
    {
        $activeJobs = $this->getActiveJobsData();

        return view('scraping.status', compact('activeJobs'));
    }

    public function getActiveJobs()
    {
        $activeJobs = $this->getActiveJobsData();

        return response()->json([
            'jobs' => $activeJobs['jobs'],
            'batches' => $activeJobs['batches'],
            'total_active' => count($activeJobs['jobs']) + count($activeJobs['batches'])
        ]);
    }

    protected function getActiveJobsData()
    {
        $jobs = [];
        $batches = [];

        // Get the latest progress entries which contain active job information
        $latestProgress = Cache::get('scraping_progress_latest');
        $latestBatchProgress = Cache::get('batch_progress_latest');

        // Check if the latest individual job is still active
        if (
            $latestProgress &&
            isset($latestProgress['status']) &&
            !in_array($latestProgress['status'], ['completed', 'failed'])
        ) {

            $jobs[] = [
                'id' => $latestProgress['job_id'] ?? 'unknown',
                'type' => $this->mapJobType($latestProgress['type'] ?? 'unknown'),
                'status' => $latestProgress['status'],
                'progress' => $latestProgress['progress'] ?? 0,
                'message' => $latestProgress['message'] ?? 'Processing...',
                'started_at' => $latestProgress['updated_at'] ?? now()->format('Y-m-d H:i:s'),
                'processed' => $latestProgress['processed'] ?? 0,
                'total' => $latestProgress['total'] ?? 0,
            ];
        }

        // Check if the latest batch job is still active
        if (
            $latestBatchProgress &&
            isset($latestBatchProgress['status']) &&
            !in_array($latestBatchProgress['status'], ['completed', 'failed'])
        ) {

            $batches[] = [
                'id' => $latestBatchProgress['batch_id'] ?? 'unknown',
                'status' => $latestBatchProgress['status'],
                'progress' => $latestBatchProgress['progress'] ?? 0,
                'message' => $latestBatchProgress['message'] ?? 'Processing...',
                'started_at' => $latestBatchProgress['updated_at'] ?? now()->format('Y-m-d H:i:s'),
                'types' => $latestBatchProgress['scraping_types'] ?? $latestBatchProgress['types'] ?? [],
            ];
        }

        // Additionally, check active Laravel queue jobs from database
        $this->addDatabaseQueueJobs($jobs, $batches);

        return [
            'jobs' => $jobs,
            'batches' => $batches
        ];
    }

    protected function addDatabaseQueueJobs(&$jobs, &$batches)
    {
        try {
            // Get active jobs from the jobs table
            $queueJobs = \DB::table('jobs')
                ->where('attempts', '<=', 3) // Only jobs that haven't exceeded max attempts
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            foreach ($queueJobs as $queueJob) {
                $payload = json_decode($queueJob->payload, true);

                if (isset($payload['displayName'])) {
                    $jobClass = $payload['displayName'];

                    // Check if it's one of our scraping jobs
                    if (str_contains($jobClass, 'ScrapeNilaiJob') || str_contains($jobClass, 'ScrapeMahasiswaJob')) {
                        $jobId = 'queue_' . substr(md5($queueJob->payload), 0, 8);

                        // Check if we already have this job in our list
                        $exists = collect($jobs)->contains('id', $jobId);

                        if (!$exists) {
                            $jobs[] = [
                                'id' => $jobId,
                                'type' => str_contains($jobClass, 'ScrapeNilaiJob') ? 'nilai' : 'mahasiswa',
                                'status' => 'queued',
                                'progress' => 0,
                                'message' => 'Dalam antrian...',
                                'started_at' => date('Y-m-d H:i:s', $queueJob->created_at),
                                'processed' => 0,
                                'total' => 0,
                            ];
                        }
                    } elseif (str_contains($jobClass, 'ProcessScrapingBatchJob')) {
                        $batchId = 'queue_batch_' . substr(md5($queueJob->payload), 0, 8);

                        $exists = collect($batches)->contains('id', $batchId);

                        if (!$exists) {
                            $batches[] = [
                                'id' => $batchId,
                                'status' => 'queued',
                                'progress' => 0,
                                'message' => 'Dalam antrian...',
                                'started_at' => date('Y-m-d H:i:s', $queueJob->created_at),
                                'types' => [],
                            ];
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to get database queue jobs: ' . $e->getMessage());
        }
    }

    protected function mapJobType($type)
    {
        $typeMap = [
            'scrape_nilai' => 'nilai',
            'scrape_mahasiswa' => 'mahasiswa',
        ];

        return $typeMap[$type] ?? $type;
    }

    public function index()
    {
        $jurusan = Jurusan::all();
        $semester = Semester::all();

        return view('scraping.index', compact('jurusan', 'semester'));
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $success = $this->scraperService->login($request->username, $request->password);

        if ($success) {
            // Store cookie in session for later use
            session(['siakad_cookie' => $this->scraperService->getCookie()]);
            session(['siakad_username' => $request->username]);

            return response()->json([
                'success' => true,
                'message' => 'Login berhasil!',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Login gagal. Periksa username dan password.',
        ], 401);
    }

    public function checkSession()
    {
        $cookie = session('siakad_cookie');
        if (!$cookie) {
            return response()->json(['valid' => false]);
        }

        $username = session('siakad_username') ?? '';

        $this->scraperService->setCookie($cookie);
        $isValid = $this->scraperService->isSessionValid();

        return response()->json(['valid' => $isValid, 'username' => $username]);
    }

    public function getSemesters()
    {
        $cookie = session('siakad_cookie');

        if (!$cookie) {
            return response()->json(['error' => 'Not authenticated'], 401);
        }

        $this->scraperService->setCookie($cookie);
        $semesters = $this->scraperService->getSemesters();

        // Store semesters in database
        foreach ($semesters as $semesterData) {
            Semester::updateOrCreate(
                ['smtthnakd' => $semesterData['smtthnakd']],
                ['keterangan' => $semesterData['keterangan']]
            );
        }

        return response()->json(['semesters' => $semesters]);
    }

    public function scrapeNilai(Request $request)
    {
        $request->validate([
            'jurusan_id' => 'required|integer',
            'semester' => 'required|string',
        ]);

        $cookie = session('siakad_cookie');

        if (!$cookie) {
            return response()->json(['error' => 'Not authenticated'], 401);
        }

        // Dispatch queue job
        $jobId = uniqid('scrape_nilai_');
        $job = new ScrapeNilaiJob($request->jurusan_id, $request->semester, $cookie, $jobId);
        dispatch($job);

        return response()->json([
            'success' => true,
            'message' => 'Scraping job telah dimulai',
            'job_id' => $jobId,
            'queue' => true
        ]);
    }

    public function scrapeMahasiswa(Request $request)
    {
        $request->validate([
            'jurusan_id' => 'required|integer',
            'semester' => 'required|string',
        ]);

        $cookie = session('siakad_cookie');

        if (!$cookie) {
            return response()->json(['error' => 'Not authenticated'], 401);
        }

        // Dispatch queue job
        $jobId = uniqid('scrape_mahasiswa_');
        $job = new ScrapeMahasiswaJob($request->jurusan_id, $request->semester, $cookie, $jobId);
        dispatch($job);

        return response()->json([
            'success' => true,
            'message' => 'Scraping job telah dimulai',
            'job_id' => $jobId,
            'queue' => true
        ]);
    }

    public function getScrapingProgress()
    {
        // Get the latest progress from cache
        $progress = Cache::get('scraping_progress_latest');

        if (!$progress) {
            return response()->json(['status' => 'no_active_job']);
        }

        return response()->json($progress);
    }

    public function getJobProgress(Request $request)
    {
        $request->validate([
            'job_id' => 'required|string',
        ]);

        $jobId = $request->job_id;
        $progress = Cache::get("scraping_progress_{$jobId}");

        if (!$progress) {
            // Try to check if this job exists in the queue database
            $queueJob = $this->findJobInQueue($jobId);

            if ($queueJob) {
                // Job exists in queue but hasn't started yet
                return response()->json([
                    'job_id' => $jobId,
                    'status' => 'queued',
                    'progress' => 0,
                    'message' => 'Job dalam antrian...',
                    'processed' => 0,
                    'total' => 0,
                    'queue_position' => $queueJob['position'] ?? null,
                    'created_at' => $queueJob['created_at'] ?? null
                ]);
            }

            // Check if job might have completed and check latest cache
            $latestProgress = Cache::get('scraping_progress_latest');
            if ($latestProgress && isset($latestProgress['job_id']) && $latestProgress['job_id'] === $jobId) {
                return response()->json($latestProgress);
            }

            // Job not found anywhere - might be completed, failed, or never existed
            return response()->json([
                'job_id' => $jobId,
                'status' => 'not_found',
                'message' => 'Job tidak ditemukan. Mungkin sudah selesai atau tidak pernah ada.',
                'progress' => 0,
                'error' => 'Job progress not available'
            ], 404);
        }

        return response()->json($progress);
    }

    protected function findJobInQueue($jobId)
    {
        try {
            $queueJobs = \DB::table('jobs')
                ->orderBy('created_at', 'asc')
                ->get();

            $position = 1;
            foreach ($queueJobs as $queueJob) {
                $payload = json_decode($queueJob->payload, true);

                // Try to extract job ID from the payload
                if (isset($payload['data']['command'])) {
                    $commandData = $payload['data']['command'];

                    // Check if this payload contains our job ID
                    if (str_contains($commandData, $jobId)) {
                        return [
                            'id' => $queueJob->id,
                            'position' => $position,
                            'created_at' => date('Y-m-d H:i:s', $queueJob->created_at),
                            'attempts' => $queueJob->attempts
                        ];
                    }
                }
                $position++;
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to find job in queue: ' . $e->getMessage());
        }

        return null;
    }

    public function getBatchProgress(Request $request)
    {
        $request->validate([
            'batch_id' => 'required|string',
        ]);

        $batchProgress = Cache::get("batch_progress_{$request->batch_id}");
        $batchData = Cache::get("batch_scraping_{$request->batch_id}");

        if (!$batchProgress || !$batchData) {
            return response()->json(['status' => 'batch_not_found'], 404);
        }

        // Get individual job progress
        $jobsProgress = [];
        if (isset($batchData['jobs'])) {
            foreach ($batchData['jobs'] as $jobId) {
                $jobProgress = Cache::get("scraping_progress_{$jobId}");
                if ($jobProgress) {
                    $jobsProgress[$jobId] = $jobProgress;
                }
            }
        }

        return response()->json([
            'batch' => $batchProgress,
            'jobs' => $jobsProgress,
            'metadata' => $batchData
        ]);
    }

    public function startBatchScraping(Request $request)
    {
        $request->validate([
            'jurusan_id' => 'required|integer',
            'semester' => 'required|string',
            'types' => 'required|array',
            'types.*' => 'in:nilai,mahasiswa',
        ]);

        $cookie = session('siakad_cookie');

        if (!$cookie) {
            return response()->json(['error' => 'Not authenticated'], 401);
        }

        // Dispatch batch job
        $batchId = uniqid('batch_');
        $job = new ProcessScrapingBatchJob(
            $request->jurusan_id,
            $request->semester,
            $cookie,
            $request->types,
            $batchId
        );
        dispatch($job);

        return response()->json([
            'success' => true,
            'message' => 'Batch scraping job telah dimulai',
            'batch_id' => $batchId,
            'types' => $request->types,
            'queue' => true
        ]);
    }

    protected function parseDate($dateString)
    {
        if (!$dateString) {
            return null;
        }

        try {
            return \Carbon\Carbon::parse($dateString)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }
}
