<?php

namespace App\Http\Controllers;

use App\Models\Jurusan;
use App\Models\Semester;
use App\Models\MataKuliah;
use App\Models\Mahasiswa;
use App\Models\Nilai;
use App\Models\Bobot;
use App\Services\SiakadScraperService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ScrapingController extends Controller
{
    protected SiakadScraperService $scraperService;

    public function __construct(SiakadScraperService $scraperService)
    {
        $this->scraperService = $scraperService;
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

        $this->scraperService->setCookie($cookie);
        $isValid = $this->scraperService->isSessionValid();

        return response()->json(['valid' => $isValid]);
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

        $this->scraperService->setCookie($cookie);

        $jurusan = Jurusan::findOrFail($request->jurusan_id);

        // Set prodi
        $setProdiSuccess = $this->scraperService->setProdi($jurusan->kode_jrs, 'REG', $request->semester);

        if (!$setProdiSuccess) {
            return response()->json(['error' => 'Failed to set prodi'], 500);
        }

        // Get mata kuliah data
        $mataKuliahList = $this->scraperService->getRekapMataKuliah();

        $processed = 0;
        $errors = [];

        foreach ($mataKuliahList as $mkData) {
            if ($mkData['cetak'] !== '1') {
                continue;
            }

            try {
                // Store mata kuliah
                $mataKuliah = MataKuliah::updateOrCreate([
                    'jid' => $mkData['jid'],
                    'kode_mk' => $mkData['kodemk'],
                    'kelas' => $mkData['kelas'],
                    'smtthnakd' => $request->semester,
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
                $nilaiList = $this->scraperService->getListNilai($mkData['infomk']);

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

                $bobotData = $this->scraperService->getBobotMataKuliah(
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
            } catch (\Exception $e) {
                $errors[] = "Error processing {$mkData['namamk']}: " . $e->getMessage();
                Log::error("Scraping error for MK {$mkData['namamk']}: " . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'processed' => $processed,
            'total' => count($mataKuliahList),
            'errors' => $errors,
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

        $this->scraperService->setCookie($cookie);

        $jurusan = Jurusan::findOrFail($request->jurusan_id);

        // Set prodi
        $setProdiSuccess = $this->scraperService->setProdi($jurusan->kode_jrs, 'REG', $request->semester);

        if (!$setProdiSuccess) {
            return response()->json(['error' => 'Failed to set prodi'], 500);
        }

        // Get mahasiswa data
        $mahasiswaList = $this->scraperService->getRekapMahasiswa();

        $processed = 0;
        $errors = [];

        foreach ($mahasiswaList as $mhsData) {
            try {
                Mahasiswa::updateOrCreate([
                    'nim' => $mhsData['nim'],
                ], [
                    'id_mhs' => $mhsData['idmhs'] ?? null,
                    'fak_id' => $mhsData['fakid'] ?? null,
                    'jrs_id' => $mhsData['jrsid'] ?? null,
                    'pk_id' => $mhsData['pkid'] ?? null,
                    'mn_id' => $mhsData['mnid'] ?? null,
                    'kode_fak' => $mhsData['kodefak'] ?? null,
                    'kode_jrs' => $mhsData['kodejrs'] ?? null,
                    'kode_pk' => $mhsData['kodepk'] ?? null,
                    'kode_mn' => $mhsData['kodemn'] ?? null,
                    'kode_pa' => $mhsData['kodepa'] ?? null,
                    'kurikulum' => $mhsData['kurikulum'] ?? null,
                    'no_sel' => $mhsData['nosel'] ?? null,
                    'no_transkrip' => $mhsData['no_transkrip'] ?? null,
                    'no_pin' => $mhsData['no_pin'] ?? null,
                    'nama' => $mhsData['nama'],
                    'tempat_lahir' => $mhsData['tem_lahir'] ?? null,
                    'tanggal_lahir' => $this->parseDate($mhsData['tgl_lahir'] ?? null),
                    'gender' => $mhsData['gender'] ?? null,
                    'agama' => $mhsData['agama'] ?? null,
                    'marital' => $mhsData['marital'] ?? null,
                    'no_ktp' => $mhsData['no_ktp'] ?? null,
                    'alamat_surat1' => $mhsData['alm1_surat'] ?? null,
                    'alamat_surat2' => $mhsData['alm2_surat'] ?? null,
                    'rt_rw_surat' => $mhsData['rtrw_surat'] ?? null,
                    'kota_surat' => $mhsData['kot_surat'] ?? null,
                    'kode_pos_surat' => $mhsData['kdp_surat'] ?? null,
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
                    'tanggal_lahir_ayah' => $this->parseDate($mhsData['tanggal_lahir_ayah'] ?? null),
                    'tanggal_lahir_ibu' => $this->parseDate($mhsData['tanggal_lahir_ibu'] ?? null),
                    'nama_fak' => $mhsData['namafak'] ?? null,
                    'nama_jrs' => $mhsData['namajrs'] ?? null,
                    'ipk' => $mhsData['ipk'] ?? null,
                    'foto' => $mhsData['foto'] ?? null,
                ]);

                $processed++;
            } catch (\Exception $e) {
                $errors[] = "Error processing student {$mhsData['nama']}: " . $e->getMessage();
                Log::error("Scraping error for student {$mhsData['nama']}: " . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'processed' => $processed,
            'total' => count($mahasiswaList),
            'errors' => $errors,
        ]);
    }

    public function getScrapingProgress()
    {
        // This would be used for real-time progress updates
        // For now, return a simple status
        return response()->json(['status' => 'completed']);
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
