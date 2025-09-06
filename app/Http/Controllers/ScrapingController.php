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
