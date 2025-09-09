<?php

namespace App\Exports;

use App\Models\Nilai;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class NilaiExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $nilai;

    public function __construct($nilai)
    {
        $this->nilai = $nilai;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->nilai;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'NIM',
            'Nama',
            'Kode Mata Kuliah',
            'Mata Kuliah',
            'Semester',
            'Nama Kelas',
            'Kehadiran',
            'Projek',
            'Quiz',
            'Tugas',
            'UTS',
            'UAS',
            'Kode Prodi Mahasiswa',
            'Nama Prodi Mahasiswa',
            'Kode Prodi',
            'Nama Prodi Kelas',
            'Nilai Huruf',
            'Nilai Indeks',
            'Nilai Angka',
            'Dosen',
            'Tahun Masuk',
        ];
    }

    /**
     * @param mixed $nilai
     * @return array
     */
    public function map($nilai): array
    {
        $tahunMasuk = '';
        if ($nilai->mahasiswa && $nilai->mahasiswa->tgl_masuk) {
            try {
                $tahunMasuk = \Carbon\Carbon::parse($nilai->mahasiswa->tgl_masuk)->format('Y');
            } catch (\Exception $e) {
                $tahunMasuk = '';
            }
        }

        return [
            $nilai->nim,
            $nilai->nama,
            $nilai->mataKuliah->kode_mk ?? '',
            $nilai->mataKuliah->nama_mk ?? '',
            $nilai->mataKuliah->smtthnakd ?? '',
            'R' . $nilai->mataKuliah->kelas ?? '',
            $nilai->hadir ?? '',
            $nilai->projek ?? '',
            $nilai->quiz ?? '',
            $nilai->tugas ?? '',
            $nilai->uts ?? '',
            $nilai->uas ?? '',
            $nilai->mahasiswa->kodejrs ?? '',
            $nilai->mahasiswa->namajrs ?? '',
            $nilai->mahasiswa->kodejrs ?? '',
            $nilai->mahasiswa->namajrs ?? '',
            $nilai->nil_huruf ?? '',
            '',
            $nilai->nil_angka ?? '',
            $nilai->mataKuliah->nama_dosen ?? '',
            $tahunMasuk
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text
            1 => ['font' => ['bold' => true]],
        ];
    }
}
