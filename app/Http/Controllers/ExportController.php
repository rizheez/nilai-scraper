<?php

namespace App\Http\Controllers;

use App\Models\Nilai;
use App\Models\Jurusan;
use App\Models\Semester;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Exports\NilaiExport;
use Illuminate\Http\Request;
use App\Exports\MahasiswaExport;
use App\Exports\MataKuliahExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Response;

class ExportController extends Controller
{
    public function mahasiswa(Request $request, $format)
    {
        $query = Mahasiswa::with('jurusan');
        $jur = 'Semua Jurusan';
        $tahun = 'Semua Tahun';
        // Apply filters
        if ($request->filled('jurusan')) {
            $query->where('kodejrs', $request->jurusan);
            $jur = Jurusan::where('kode_jrs', $request->jurusan)->first()->nama_jrs;
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
            $tahun = $request->tahun_masuk;
            $query->whereYear('tgl_masuk', $year)->whereMonth('tgl_masuk', '>=', 7)->whereMonth('tgl_masuk', '<=', 12);
        }

        $mahasiswa = $query->get();
        if ($format === 'json') {
            return $this->exportMahasiswaJson($mahasiswa);
        } elseif ($format === 'excel') {
            return $this->exportMahasiswaExcel($mahasiswa, $jur, $tahun);
        }

        return redirect()->back()->with('error', 'Format tidak didukung');
    }

    public function mataKuliah(Request $request, $format)
    {
        $query = MataKuliah::with(['jurusan', 'semester', 'bobot']);

        // Apply filters
        if ($request->filled('jurusan')) {
            $query->where('kode_jrs', $request->jurusan);
        }

        if ($request->filled('semester')) {
            $query->where('smtthnakd', $request->semester);
        }

        $mataKuliah = $query->get();

        if ($format === 'json') {
            return $this->exportMataKuliahJson($mataKuliah);
        } elseif ($format === 'excel') {
            return $this->exportMataKuliahExcel($mataKuliah);
        }

        return redirect()->back()->with('error', 'Format tidak didukung');
    }

    public function nilai(Request $request, $format)
    {
        $query = Nilai::with(['mataKuliah', 'mahasiswa']);
        $jur = '';
        $mk = 'Semua mk';
        $kelas = '';
        $tahun = '';
        // Apply filters
        if ($request->filled('mata_kuliah')) {
            $query->where('mata_kuliah_id', $request->mata_kuliah);
            $mataKuliah = MataKuliah::with('jurusan')->find($request->mata_kuliah);
            if ($mataKuliah) {
                $mk = $mataKuliah->nama_mk;
                $kelas = $mataKuliah->kelas;
                $jur = $mataKuliah->jurusan->nama_jrs;
                $tahun = Semester::where('smtthnakd', $mataKuliah->smtthnakd)->first()->keterangan;
                $tahun = str_replace('/', '-', $tahun);
            }
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

        $nilai = $query->get();

        if ($format === 'json') {
            return $this->exportNilaiJson($nilai);
        } elseif ($format === 'excel') {
            return $this->exportNilaiExcel($nilai, $mk, $jur, $kelas, $tahun);
        }

        return redirect()->back()->with('error', 'Format tidak didukung');
    }

    protected function exportMahasiswaJson($mahasiswa)
    {
        $filename = 'mahasiswa_' . date('Y-m-d_H-i-s') . '.json';

        $headers = [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        return Response::make($mahasiswa->toJson(JSON_PRETTY_PRINT), 200, $headers);
    }

    protected function exportMahasiswaExcel($mahasiswa, $jurusan = null, $tahun = null)
    {
        $jrs = $jurusan ? $jurusan : '';
        $thn = $tahun ? $tahun : date('Y-m-d_s');
        $filename = "Mahasiswa_{$jrs}_{$thn}.xlsx";

        return Excel::download(new MahasiswaExport($mahasiswa), $filename);
    }

    protected function exportMataKuliahJson($mataKuliah)
    {
        $filename = 'mata_kuliah_' . date('Y-m-d_H-i-s') . '.json';

        $headers = [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        return Response::make($mataKuliah->toJson(JSON_PRETTY_PRINT), 200, $headers);
    }

    protected function exportMataKuliahExcel($mataKuliah)
    {
        $filename = 'mata_kuliah_' . date('Y-m-d_H-i-s') . '.xlsx';

        return Excel::download(new MataKuliahExport($mataKuliah), $filename);
    }

    protected function exportNilaiJson($nilai)
    {
        $filename = 'nilai_' . date('Y-m-d_H-i-s') . '.json';

        $headers = [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        return Response::make($nilai->toJson(JSON_PRETTY_PRINT), 200, $headers);
    }

    protected function exportNilaiExcel($nilai, $mk = null, $jurusan = null, $kelas = null, $tahun = null)
    {
        $mataKuliah = $mk ? $mk : '';
        $jrs = $jurusan ? $jurusan : '';
        $thn = $tahun ? $tahun : date('Y-m-d_s');
        $kls = $kelas ? $kelas : '';


        $filename = "Nilai_{$mataKuliah}_R-{$kls}_{$jrs}_{$thn}.xlsx";

        return Excel::download(new NilaiExport($nilai), $filename);
    }
}
