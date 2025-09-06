<?php

namespace App\Jobs;

use App\Models\Jurusan;
use App\Models\Mahasiswa;
use App\Services\SiakadScraperService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ScrapeMahasiswaJob implements ShouldQueue
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
        $this->jobId = $jobId ?? uniqid('scrape_mahasiswa_');
    }

    public function handle(SiakadScraperService $scraperService): void
    {
        Log::info("Starting scrape mahasiswa job for jurusan: {$this->jurusanId}, semester: {$this->semester}");

        $this->updateProgress(0, 'Memulai scraping mahasiswa...');

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

            $this->updateProgress(20, 'Mengambil daftar mahasiswa...');

            // Get mahasiswa data
            $mahasiswaList = $scraperService->getRekapMahasiswa();

            if (empty($mahasiswaList)) {
                throw new \Exception('No mahasiswa data found');
            }

            $total = count($mahasiswaList);
            $processed = 0;
            $errors = [];

            $this->updateProgress(30, "Memproses {$total} mahasiswa...");

            foreach ($mahasiswaList as $index => $mhsData) {
                try {
                    $mhsData = array_filter($mhsData, 'is_string', ARRAY_FILTER_USE_KEY);

                    Mahasiswa::updateOrCreate([
                        'nim' => $mhsData['nim'],
                    ], [
                        'idmhs' => $mhsData['idmhs'] ?? null,
                        'fakid' => $mhsData['fakid'] ?? null,
                        'jrsid' => $mhsData['jrsid'] ?? null,
                        'pkid' => $mhsData['pkid'] ?? null,
                        'mnid' => $mhsData['mnid'] ?? null,
                        'kodefak' => $mhsData['kodefak'] ?? null,
                        'kodejrs' => $mhsData['kodejrs'] ?? null,
                        'kodepk' => $mhsData['kodepk'] ?? null,
                        'kodemn' => $mhsData['kodemn'] ?? null,
                        'kodepa' => $mhsData['kodepa'] ?? null,
                        'kurikulum' => $mhsData['kurikulum'] ?? null,
                        'nosel' => $mhsData['nosel'] ?? null,
                        'nim' => $mhsData['nim'],
                        'no_transkrip' => $mhsData['no_transkrip'] ?? null,
                        'no_pin' => $mhsData['no_pin'] ?? null,
                        'nama' => $mhsData['nama'],
                        'tem_lahir' => $mhsData['tem_lahir'] ?? null,
                        'tgl_lahir' => $this->parseDate($mhsData['tgl_lahir'] ?? null),
                        'gender' => $mhsData['gender'] ?? null,
                        'agama' => $mhsData['agama'] ?? null,
                        'marital' => $mhsData['marital'] ?? null,
                        'no_ktp' => $mhsData['no_ktp'] ?? null,
                        'alm1_surat' => $mhsData['alm1_surat'] ?? null,
                        'alm2_surat' => $mhsData['alm2_surat'] ?? null,
                        'rtrw_surat' => $mhsData['rtrw_surat'] ?? null,
                        'kot_surat' => $mhsData['kot_surat'] ?? null,
                        'kdp_surat' => $mhsData['kdp_surat'] ?? null,
                        'telepon' => $mhsData['telepon'] ?? null,
                        'hp1' => $mhsData['hp1'] ?? null,
                        'hp2' => $mhsData['hp2'] ?? null,
                        'email' => $mhsData['email'] ?? null,
                        'tinggal' => $mhsData['tinggal'] ?? null,
                        'nama_ayah' => $mhsData['nama_ayah'] ?? null,
                        'nama_ibu' => $mhsData['nama_ibu'] ?? null,
                        'kerja_ayah' => $mhsData['kerja_ayah'] ?? null,
                        'kerja_ibu' => $mhsData['kerja_ibu'] ?? null,
                        'didik_ayah' => $mhsData['didik_ayah'] ?? null,
                        'didik_ibu' => $mhsData['didik_ibu'] ?? null,
                        'nik_ayah' => $mhsData['nik_ayah'] ?? null,
                        'nik_ibu' => $mhsData['nik_ibu'] ?? null,
                        'tanggal_lahir_ayah' => $this->parseDate($mhsData['tanggal_lahir_ayah']) ?? null,
                        'tanggal_lahir_ibu' => $this->parseDate($mhsData['tanggal_lahir_ibu']) ?? null,
                        'id_wilayah' => $mhsData['id_wilayah'] ?? null,
                        'id_didik_ayah' => $mhsData['id_didik_ayah'] ?? null,
                        'id_didik_ibu' => $mhsData['id_didik_ibu'] ?? null,
                        'id_penghasilan_ayah' => $mhsData['id_penghasilan_ayah'] ?? null,
                        'id_penghasilan_ibu' => $mhsData['id_penghasilan_ibu'] ?? null,
                        'id_kerja_ayah' => $mhsData['id_kerja_ayah'] ?? null,
                        'id_kerja_ibu' => $mhsData['id_kerja_ibu'] ?? null,
                        'id_npwp_mhs' => $mhsData['id_npwp_mhs'] ?? null,
                        'alamat_ortu' => $mhsData['alamat_ortu'] ?? null,
                        'kota_ortu' => $mhsData['kota_ortu'] ?? null,
                        'kodepos_ortu' => $mhsData['kodepos_ortu'] ?? null,
                        'telp_ortu' => $mhsData['telp_ortu'] ?? null,
                        'hp_ortu' => $mhsData['hp_ortu'] ?? null,
                        'nama_sklh' => $mhsData['nama_sklh'] ?? null,
                        'alam_sklh' => $mhsData['alam_sklh'] ?? null,
                        'jj_sklh' => $mhsData['jj_sklh'] ?? null,
                        'perusahaan' => $mhsData['perusahaan'] ?? null,
                        'alm_perush' => $mhsData['alm_perush'] ?? null,
                        'kot_perush' => $mhsData['kot_perush'] ?? null,
                        'kdp_perush' => $mhsData['kdp_perush'] ?? null,
                        'tlp_perush' => $mhsData['tlp_perush'] ?? null,
                        'fax_perush' => $mhsData['fax_perush'] ?? null,
                        'kdptimsmhs' => $mhsData['kdptimsmhs'] ?? null,
                        'kdjenmsmhs' => $mhsData['kdjenmsmhs'] ?? null,
                        'kdpstmsmhs' => $mhsData['kdpstmsmhs'] ?? null,
                        'nimhsmsmhs' => $mhsData['nimhsmsmhs'] ?? null,
                        'nmmhsmsmhs' => $mhsData['nmmhsmsmhs'] ?? null,
                        'shiftmsmhs' => $mhsData['shiftmsmhs'] ?? null,
                        'tplhrmsmhs' => $mhsData['tplhrmsmhs'] ?? null,
                        'tglhrmsmhs' => $this->parseDate($mhsData['tglhrmsmhs']) ?? null,
                        'kdjekmsmhs' => $mhsData['kdjekmsmhs'] ?? null,
                        'tahunmsmhs' => $mhsData['tahunmsmhs'] ?? null,
                        'smawlmsmhs' => $mhsData['smawlmsmhs'] ?? null,
                        'btstumsmhs' => $mhsData['btstumsmhs'] ?? null,
                        'assmamsmhs' => $mhsData['assmamsmhs'] ?? null,
                        'tgmskmsmhs' => $this->parseDate($mhsData['tgmskmsmhs']) ?? null,
                        'tgllsmsmhs' => $this->parseDate($mhsData['tgllsmsmhs']) ?? null,
                        'stmhsmsmhs' => $mhsData['smthnlulus'] ?? null,
                        'stpidmsmhs' => $mhsData['stpidmsmhs'] ?? null,
                        'sksdimsmhs' => $mhsData['sksdimsmhs'] ?? null,
                        'asnimmsmhs' => $mhsData['asnimmsmhs'] ?? null,
                        'asptimsmhs' => $mhsData['asptimsmhs'] ?? null,
                        'asjenmsmhs' => $mhsData['asjenmsmhs'] ?? null,
                        'aspstmsmhs' => $mhsData['aspstmsmhs'] ?? null,
                        'smthnlulus' => $mhsData['smthnlulus'] ?? null,
                        'nosklulus' => $mhsData['nosklulus'] ?? null,
                        'id_perguruan_tinggi_asal' => $mhsData['id_perguruan_tinggi_asal'] ?? null,
                        'nama_perguruan_tinggi_asal' => $mhsData['nama_perguruan_tinggi_asal'] ?? null,
                        'id_prodi_asal' => $mhsData['id_prodi_asal'] ?? null,
                        'nama_program_studi_asal' => $mhsData['nama_program_studi_asal'] ?? null,
                        'foto' => $mhsData['foto'] ?? null,
                        'asnmpti' => $mhsData['asnmpti'] ?? null,
                        'asnmpst' => $mhsData['asnmpst'] ?? null,
                        'jummk' => $mhsData['jummk'] ?? null,
                        'jumsks' => $mhsData['jumsks'] ?? null,
                        'jumutu' => $mhsData['jumutu'] ?? null,
                        'ipk' => $mhsData['ipk'] ?? null,
                        'logika' => $mhsData['logika'] ?? null,
                        'createdate' => $mhsData['createdate'] ?? null,
                        'moddate' => $mhsData['moddate'] ?? null,
                        'rt' => $mhsData['rt'] ?? null,
                        'rw' => $mhsData['rw'] ?? null,
                        'jalan' => $mhsData['jalan'] ?? null,
                        'dusun' => $mhsData['dusun'] ?? null,
                        'kode_pos' => $mhsData['kode_pos'] ?? null,
                        'kelurahan' => $mhsData['kelurahan'] ?? null,
                        'nama_wilayah' => $mhsData['nama_wilayah'] ?? null,
                        'biaya_masuk' => $mhsData['biaya_masuk'] ?? null,
                        'kd_daftar' => $mhsData['kd_daftar'] ?? null,
                        'kode_agama' => $mhsData['kode_agama'] ?? null,
                        'nama_agama' => $mhsData['nama_agama'] ?? null,
                        'id_reg_pd' => $mhsData['id_reg_pd'] ?? null,
                        'id_mahasiswa' => $mhsData['id_mahasiswa'] ?? null,
                        'id_jalur_masuk' => $mhsData['id_jalur_masuk'] ?? null,
                        'id_jns_tinggal' => $mhsData['id_jns_tinggal'] ?? null,
                        'id_jns_keluar' => $mhsData['id_jns_keluar'] ?? null,
                        'id_jenj_didik' => $mhsData['id_jenj_didik'] ?? null,
                        'id_jns_daftar' => $mhsData['id_jns_daftar'] ?? null,
                        'id_alat_transport' => $mhsData['id_alat_transport'] ?? null,
                        'id_penghasilan' => $mhsData['id_penghasilan'] ?? null,
                        'id_pembiayaan' => $mhsData['id_pembiayaan'] ?? null,
                        'id_kps' => $mhsData['id_kps'] ?? null,
                        'total_tagihan' => $mhsData['total_tagihan'] ?? null,
                        'namafak' => $mhsData['namafak'] ?? null,
                        'namajrs' => $mhsData['namajrs'] ?? null,
                        'jenjang' => $mhsData['jenjang'] ?? null,
                        'nama_jjg' => $mhsData['nama_jjg'] ?? null,
                        'kdjen' => $mhsData['kdjen'] ?? null,
                        'kdpst' => $mhsData['kdpst'] ?? null,
                        'batastudi' => $mhsData['batastudi'] ?? null,
                        'namapk' => $mhsData['namapk'] ?? null,
                        'namamn' => $mhsData['namamn'] ?? null,
                        'group' => $mhsData['group'] ?? null,
                        'tgl_masuk' => $this->parseDate($mhsData['tgl_masuk']) ?? null,
                        'status_mhs_ket' => $mhsData['status_mhs_ket'] ?? null,
                        'temtgl_lahir' => $mhsData['temtgl_lahir'] ?? null,
                        'sks_total' => $mhsData['sks_total'] ?? null,
                    ]);

                    $processed++;

                    // Update progress
                    $progressPercent = 30 + (($index + 1) / $total) * 60;
                    $this->updateProgress($progressPercent, "Memproses mahasiswa: {$mhsData['nama']} ({$processed}/{$total})");
                } catch (\Exception $e) {
                    $error = "Error processing student {$mhsData['nama']}: " . $e->getMessage();
                    $errors[] = $error;
                    Log::error("Scraping error for student {$mhsData['nama']}: " . $e->getMessage());
                }
            }

            $this->updateProgress(100, "Selesai! Berhasil memproses {$processed} dari {$total} mahasiswa.", [
                'processed' => $processed,
                'total' => $total,
                'errors' => $errors,
                'status' => 'completed'
            ]);

            Log::info("Scrape mahasiswa job completed. Processed: {$processed}, Total: {$total}");
        } catch (\Exception $e) {
            $errorMessage = "Scraping gagal: " . $e->getMessage();
            $this->updateProgress(0, $errorMessage, [
                'status' => 'failed',
                'error' => $e->getMessage()
            ]);

            Log::error("Scrape mahasiswa job failed: " . $e->getMessage());
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

        Log::error("ScrapeMahasiswaJob failed permanently: " . $exception->getMessage());
    }

    protected function updateProgress(float $percentage, string $message, array $additionalData = []): void
    {
        $data = array_merge([
            'job_id' => $this->jobId,
            'type' => 'scrape_mahasiswa',
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

    public function getJobId(): string
    {
        return $this->jobId;
    }
}
