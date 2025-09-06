<?php

namespace App\Jobs;

use App\Models\Jurusan;
use App\Models\MataKuliah;
use App\Models\Nilai;
use App\Models\Bobot;
use App\Services\SiakadScraperService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ScrapeNilaiJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300; // 5 minutes timeout
    public $tries = 3; // Maximum attempts
    public $backoff = 60; // Wait 60 seconds before retrying

    protected int $jurusanId;
    protected string $semester;
    protected string $cookie;
    protected string $jobId;

    public function __construct(int $jurusanId, string $semester, string $cookie, string $jobId = null)
    {
        $this->jurusanId = $jurusanId;
        $this->semester = $semester;
        $this->cookie = $cookie;
        $this->jobId = $jobId ?? uniqid('scrape_nilai_');
    }

    public function handle(SiakadScraperService $scraperService): void
    {
        Log::info("Starting scrape nilai job for jurusan: {$this->jurusanId}, semester: {$this->semester}");

        $this->updateProgress(0, 'Memulai scraping nilai...');

        try {
            // Set scraper cookie
            $scraperService->setCookie($this->cookie);

            // Get jurusan
            $jurusan = Jurusan::findOrFail($this->jurusanId);

            $this->updateProgress(10, 'Setting prodi...');

            // Set prodi
            $setProdiSuccess = $scraperService->setProdi($jurusan->kode_jrs, 'REG', $this->semester);

            if (!$setProdiSuccess) {
                throw new \Exception('Failed to set prodi');
            }

            $this->updateProgress(20, 'Mengambil daftar mata kuliah...');

            // Get mata kuliah data
            $mataKuliahList = $scraperService->getRekapMataKuliah();

            if (empty($mataKuliahList)) {
                throw new \Exception('No mata kuliah data found');
            }

            $total = count($mataKuliahList);
            $processed = 0;
            $errors = [];

            $this->updateProgress(30, "Memproses {$total} mata kuliah...");

            foreach ($mataKuliahList as $index => $mkData) {
                if ($mkData['cetak'] !== '1') {
                    continue;
                }

                try {
                    // Store mata kuliah
                    $mataKuliah = MataKuliah::updateOrCreate([
                        'jid' => $mkData['jid'],
                        'kode_mk' => $mkData['kodemk'],
                        'kelas' => $mkData['kelas'],
                        'smtthnakd' => $this->semester,
                    ], [
                        'nama_mk' => $mkData['namamk'],
                        'nama_dosen' => $mkData['namadosen'],
                        'cetak' => $mkData['cetak'],
                        'info_mk' => $mkData['infomk'],
                        'kode_jrs' => $mkData['kodejrs'],
                        'kode_pk' => $mkData['kodepk'],
                        'nama_jrs' => $mkData['namajrs'],
                    ]);

                    // Get nilai data
                    $nilaiList = $scraperService->getListNilai($mkData['infomk']);

                    foreach ($nilaiList as $nilaiData) {
                        Nilai::updateOrCreate([
                            'mata_kuliah_id' => $mataKuliah->id,
                            'nim' => $nilaiData['nim'],
                        ], [
                            'nama' => $nilaiData['nama'],
                            'nil_angka' => $nilaiData['nil_angka'] ?? null,
                            'nil_huruf' => $nilaiData['nil_huruf'] ?? null,
                            'hadir' => $nilaiData['hadir'] ?? null,
                            'projek' => $nilaiData['projek'] ?? null,
                            'quiz' => $nilaiData['quiz'] ?? null,
                            'tugas' => $nilaiData['tugas'] ?? null,
                            'uts' => $nilaiData['uts'] ?? null,
                            'uas' => $nilaiData['uas'] ?? null,
                        ]);
                    }

                    // Get bobot data
                    $infomkParts = explode('#', $mkData['infomk']);
                    $fak = $infomkParts[0] ?? '';

                    $bobotData = $scraperService->getBobotMataKuliah(
                        $fak,
                        $mkData['kodejrs'],
                        $mkData['kodepk'],
                        $mkData['kelas'],
                        $mkData['kodemk']
                    );

                    if (!empty($bobotData)) {
                        Bobot::updateOrCreate([
                            'mata_kuliah_id' => $mataKuliah->id,
                        ], [
                            'hadir' => $bobotData['hdr'] ?? null,
                            'projek' => $bobotData['projek'] ?? null,
                            'quiz' => $bobotData['quiz'] ?? null,
                            'tugas' => $bobotData['tgs'] ?? null,
                            'uts' => $bobotData['uts'] ?? null,
                            'uas' => $bobotData['uas'] ?? null,
                        ]);
                    }

                    $processed++;

                    // Update progress
                    $progressPercent = 30 + (($index + 1) / $total) * 60;
                    $this->updateProgress($progressPercent, "Memproses mata kuliah: {$mkData['namamk']} ({$processed}/{$total})");
                } catch (\Exception $e) {
                    $error = "Error processing {$mkData['namamk']}: " . $e->getMessage();
                    $errors[] = $error;
                    Log::error("Scraping error for MK {$mkData['namamk']}: " . $e->getMessage());
                }
            }

            $this->updateProgress(100, "Selesai! Berhasil memproses {$processed} dari {$total} mata kuliah.", [
                'processed' => $processed,
                'total' => $total,
                'errors' => $errors,
                'status' => 'completed'
            ]);

            Log::info("Scrape nilai job completed. Processed: {$processed}, Total: {$total}");
        } catch (\Exception $e) {
            $errorMessage = "Scraping gagal: " . $e->getMessage();
            $this->updateProgress(0, $errorMessage, [
                'status' => 'failed',
                'error' => $e->getMessage()
            ]);

            Log::error("Scrape nilai job failed: " . $e->getMessage());
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        $errorMessage = "Job gagal setelah {$this->tries} percobaan: " . $exception->getMessage();
        $this->updateProgress(0, $errorMessage, [
            'status' => 'failed',
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts()
        ]);

        Log::error("ScrapeNilaiJob failed permanently: " . $exception->getMessage());
    }

    protected function updateProgress(float $percentage, string $message, array $additionalData = []): void
    {
        $data = array_merge([
            'job_id' => $this->jobId,
            'type' => 'scrape_nilai',
            'jurusan_id' => $this->jurusanId,
            'semester' => $this->semester,
            'progress' => $percentage,
            'message' => $message,
            'updated_at' => now()->toISOString(),
        ], $additionalData);

        Cache::put("scraping_progress_{$this->jobId}", $data, 3600); // Cache for 1 hour

        // Also store in a general key for UI polling
        Cache::put("scraping_progress_latest", $data, 3600);
    }

    public function getJobId(): string
    {
        return $this->jobId;
    }
}
