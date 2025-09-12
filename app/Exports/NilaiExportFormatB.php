<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class NilaiExportFormatB implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $nilai;

    public function __construct($nilai)
    {
        $this->nilai = $nilai;
    }

    public function collection()
    {
        return $this->nilai;
    }

    public function headings(): array
    {
        return [
            'NIM',
            'Nama Mahasiswa',
            'Kode Mata Kuliah',
            'Nama Mata Kuliah',
            'Semester',
            'Nama Kelas',
            'Aktivitas Partisipatif',
            'Hasil Proyek',
            'QUIZ',
            'TUGAS',
            'UTS',
            'UAS',
            'Kode Prodi Mahasiswa',
            'Nama Prodi Mahasiswa',
            'Kode Prodi Kelas',
            'Nama Prodi Kelas',
        ];
    }

    public function map($nilai): array
    {
        return [
            $nilai->nim,
            $nilai->nama,
            $nilai->mataKuliah->kode_mk ?? '',
            $nilai->mataKuliah->nama_mk ?? '',
            $nilai->mataKuliah->smtthnakd ?? '',
            'R' . ($nilai->mataKuliah->kelas ?? ''),
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
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
