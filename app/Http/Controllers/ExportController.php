<?php

namespace App\Http\Controllers;

use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Models\Nilai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MahasiswaExport;
use App\Exports\MataKuliahExport;
use App\Exports\NilaiExport;

class ExportController extends Controller
{
    public function mahasiswa(Request $request, $format)
    {
        $query = Mahasiswa::with('jurusan');

        // Apply filters
        if ($request->filled('jurusan')) {
            $query->where('kode_jrs', $request->jurusan);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nim', 'like', "%{$search}%")
                    ->orWhere('nama', 'like', "%{$search}%");
            });
        }

        $mahasiswa = $query->get();

        if ($format === 'json') {
            return $this->exportMahasiswaJson($mahasiswa);
        } elseif ($format === 'excel') {
            return $this->exportMahasiswaExcel($mahasiswa);
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

        // Apply filters
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

        $nilai = $query->get();

        if ($format === 'json') {
            return $this->exportNilaiJson($nilai);
        } elseif ($format === 'excel') {
            return $this->exportNilaiExcel($nilai);
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

    protected function exportMahasiswaExcel($mahasiswa)
    {
        $filename = 'mahasiswa_' . date('Y-m-d_H-i-s') . '.xlsx';

        // Create a simple export without using Laravel Excel for now
        $csvData = $this->convertMahasiswaToCSV($mahasiswa);

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        return Response::make($csvData, 200, $headers);
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
        $filename = 'mata_kuliah_' . date('Y-m-d_H-i-s') . '.csv';

        $csvData = $this->convertMataKuliahToCSV($mataKuliah);

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        return Response::make($csvData, 200, $headers);
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

    protected function exportNilaiExcel($nilai)
    {
        $filename = 'nilai_' . date('Y-m-d_H-i-s') . '.csv';

        $csvData = $this->convertNilaiToCSV($nilai);

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        return Response::make($csvData, 200, $headers);
    }

    protected function convertMahasiswaToCSV($mahasiswa)
    {
        $csvData = [];

        // Headers
        $headers = [
            'NIM',
            'Nama',
            'Tempat Lahir',
            'Tanggal Lahir',
            'Jenis Kelamin',
            'Agama',
            'No KTP',
            'Email',
            'Telepon',
            'HP',
            'Nama Ayah',
            'Nama Ibu',
            'Jurusan',
            'IPK'
        ];
        $csvData[] = implode(',', $headers);

        // Data
        foreach ($mahasiswa as $mhs) {
            $row = [
                $mhs->nim,
                '"' . $mhs->nama . '"',
                '"' . ($mhs->tempat_lahir ?? '') . '"',
                $mhs->tanggal_lahir ?? '',
                $mhs->gender ?? '',
                $mhs->agama ?? '',
                $mhs->no_ktp ?? '',
                $mhs->email ?? '',
                $mhs->telepon ?? '',
                $mhs->hp1 ?? '',
                '"' . ($mhs->nama_ayah ?? '') . '"',
                '"' . ($mhs->nama_ibu ?? '') . '"',
                '"' . ($mhs->nama_jrs ?? '') . '"',
                $mhs->ipk ?? '',
            ];
            $csvData[] = implode(',', $row);
        }

        return implode("\n", $csvData);
    }

    protected function convertMataKuliahToCSV($mataKuliah)
    {
        $csvData = [];

        // Headers
        $headers = [
            'Kode MK',
            'Nama Mata Kuliah',
            'Kelas',
            'Dosen',
            'Semester',
            'Jurusan'
        ];
        $csvData[] = implode(',', $headers);

        // Data
        foreach ($mataKuliah as $mk) {
            $row = [
                $mk->kode_mk,
                '"' . $mk->nama_mk . '"',
                $mk->kelas,
                '"' . $mk->nama_dosen . '"',
                $mk->smtthnakd,
                '"' . $mk->nama_jrs . '"',
            ];
            $csvData[] = implode(',', $row);
        }

        return implode("\n", $csvData);
    }

    protected function convertNilaiToCSV($nilai)
    {
        $csvData = [];

        // Headers
        $headers = [
            'NIM',
            'Nama',
            'Mata Kuliah',
            'Kelas',
            'Dosen',
            'Nilai Angka',
            'Nilai Huruf',
            'Kehadiran',
            'Projek',
            'Quiz',
            'Tugas',
            'UTS',
            'UAS'
        ];
        $csvData[] = implode(',', $headers);

        // Data
        foreach ($nilai as $n) {
            $row = [
                $n->nim,
                '"' . $n->nama . '"',
                '"' . ($n->mataKuliah->nama_mk ?? '') . '"',
                $n->mataKuliah->kelas ?? '',
                '"' . ($n->mataKuliah->nama_dosen ?? '') . '"',
                $n->nil_angka ?? '',
                $n->nil_huruf ?? '',
                $n->hadir ?? '',
                $n->projek ?? '',
                $n->quiz ?? '',
                $n->tugas ?? '',
                $n->uts ?? '',
                $n->uas ?? '',
            ];
            $csvData[] = implode(',', $row);
        }

        return implode("\n", $csvData);
    }
}
