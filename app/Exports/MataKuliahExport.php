<?php

namespace App\Exports;

use App\Models\MataKuliah;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MataKuliahExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $mataKuliah;

    public function __construct($mataKuliah)
    {
        $this->mataKuliah = $mataKuliah;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->mataKuliah;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Kode MK',
            'Nama Mata Kuliah',
            'Kelas',
            'Dosen',
            'Semester',
            'Jurusan'
        ];
    }

    /**
     * @param mixed $mataKuliah
     * @return array
     */
    public function map($mataKuliah): array
    {
        return [
            $mataKuliah->kode_mk,
            $mataKuliah->nama_mk,
            $mataKuliah->kelas,
            $mataKuliah->nama_dosen,
            $mataKuliah->smtthnakd,
            $mataKuliah->nama_jrs,
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
