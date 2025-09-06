<?php

namespace App\Http\Controllers;

use App\Models\Jurusan;
use App\Models\Semester;
use App\Models\MataKuliah;
use App\Models\Mahasiswa;
use App\Models\Nilai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_jurusan' => Jurusan::count(),
            'total_semester' => Semester::count(),
            'total_mata_kuliah' => MataKuliah::count(),
            'total_mahasiswa' => Mahasiswa::count(),
            'total_nilai' => Nilai::count(),
        ];

        $recentActivities = $this->getRecentActivities();
        $jurusanStats = $this->getJurusanStats();

        return view('dashboard.index', compact('stats', 'recentActivities', 'jurusanStats'));
    }

    public function mahasiswa(Request $request)
    {
        $query = Mahasiswa::with('jurusan');

        if ($request->filled('jurusan')) {
            $query->where('kodejrs', $request->jurusan);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nim', 'like', "%{$search}%")
                    ->orWhere('nama', 'like', "%{$search}%");
            });
        }

        // Filter by year of tgl_masuk
        if ($request->filled('tahun_masuk')) {
            $year = $request->tahun_masuk;
            $query->whereYear('tgl_masuk', $year);
        }

        $mahasiswa = $query->orderBy('nama')->paginate(15);
        $jurusan = Jurusan::all();

        // Get available years from tgl_masuk for filter dropdown
        $availableYears = Mahasiswa::whereNotNull('tgl_masuk')
            ->selectRaw('YEAR(tgl_masuk) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        return view('dashboard.mahasiswa', compact('mahasiswa', 'jurusan', 'availableYears'));
    }

    public function mataKuliah(Request $request)
    {
        $query = MataKuliah::with(['jurusan', 'semester']);

        if ($request->filled('jurusan')) {
            $query->where('kode_jrs', $request->jurusan);
        }

        if ($request->filled('semester')) {
            $query->where('smtthnakd', $request->semester);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_mk', 'like', "%{$search}%")
                    ->orWhere('kode_mk', 'like', "%{$search}%")
                    ->orWhere('nama_dosen', 'like', "%{$search}%");
            });
        }

        $mataKuliah = $query->orderBy('nama_mk')->paginate(15);
        $jurusan = Jurusan::all();
        $semester = Semester::all();

        return view('dashboard.mata-kuliah', compact('mataKuliah', 'jurusan', 'semester'));
    }

    public function nilai(Request $request)
    {
        $query = Nilai::with(['mataKuliah', 'mahasiswa']);

        if ($request->filled('mata_kuliah')) {
            $query->where('mata_kuliah_id', $request->mata_kuliah);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nim', 'like', "%{$search}%")
                    ->orWhere('nama', 'like', "%{$search}%");
            });
        }

        // Filter by year of mahasiswa's tgl_masuk
        if ($request->filled('tahun_masuk')) {
            $year = $request->tahun_masuk;
            $query->whereHas('mahasiswa', function ($q) use ($year) {
                $q->whereYear('tgl_masuk', $year);
            });
        }

        $nilai = $query->orderBy('nama')->paginate(15);
        $mataKuliah = MataKuliah::all();

        // Get available years from mahasiswa tgl_masuk for filter dropdown
        $availableYears = Mahasiswa::whereNotNull('tgl_masuk')
            ->selectRaw('YEAR(tgl_masuk) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        return view('dashboard.nilai', compact('nilai', 'mataKuliah', 'availableYears'));
    }

    public function detailMahasiswa($id)
    {
        $mahasiswa = Mahasiswa::with(['nilai.mataKuliah'])->findOrFail($id);

        return view('dashboard.detail-mahasiswa', compact('mahasiswa'));
    }

    public function detailMataKuliah($id)
    {
        $mataKuliah = MataKuliah::with(['nilai.mahasiswa', 'bobot'])->findOrFail($id);

        return view('dashboard.detail-mata-kuliah', compact('mataKuliah'));
    }

    protected function getRecentActivities()
    {
        $activities = [];

        // Recent mata kuliah
        $recentMK = MataKuliah::latest()->take(3)->get();
        foreach ($recentMK as $mk) {
            $activities[] = [
                'type' => 'mata_kuliah',
                'message' => "Data mata kuliah '{$mk->nama_mk}' ditambahkan",
                'time' => $mk->created_at,
            ];
        }

        // Recent mahasiswa
        $recentMhs = Mahasiswa::latest()->take(3)->get();
        foreach ($recentMhs as $mhs) {
            $activities[] = [
                'type' => 'mahasiswa',
                'message' => "Data mahasiswa '{$mhs->nama}' ditambahkan",
                'time' => $mhs->created_at,
            ];
        }

        // Sort by time
        usort($activities, function ($a, $b) {
            return $b['time'] <=> $a['time'];
        });

        return array_slice($activities, 0, 5);
    }

    protected function getJurusanStats()
    {
        return DB::table('mahasiswa')
            ->select('namajrs', DB::raw('count(*) as total'))
            ->groupBy('namajrs')
            ->orderBy('total', 'desc')
            ->get();
    }
}
