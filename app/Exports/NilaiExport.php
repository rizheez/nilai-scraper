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
            'Mata Kuliah',
            'Kelas',
            'Dosen',
            'Tahun Masuk',
            'Nilai Angka',
            'Nilai Huruf',
            'Kehadiran',
            'Projek',
            'Quiz',
            'Tugas',
            'UTS',
            'UAS'
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
            $nilai->mataKuliah->nama_mk ?? '',
            $nilai->mataKuliah->kelas ?? '',
            $nilai->mataKuliah->nama_dosen ?? '',
            $tahunMasuk,
            $nilai->nil_angka ?? '',
            $nilai->nil_huruf ?? '',
            $nilai->hadir ?? '',
            $nilai->projek ?? '',
            $nilai->quiz ?? '',
            $nilai->tugas ?? '',
            $nilai->uts ?? '',
            $nilai->uas ?? '',
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
