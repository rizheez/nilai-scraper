<?php

namespace App\Exports;

use App\Models\Mahasiswa;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MahasiswaExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $mahasiswa;

    public function __construct($mahasiswa)
    {
        $this->mahasiswa = $mahasiswa;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->mahasiswa;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'NIM',
            'Nama',
            'Tempat Lahir',
            'Tanggal Lahir',
            'Jenis Kelamin',
            'NIK',
            'Agama',
            'NISN',
            'Jalur Pendaftaran',
            'NPWP',
            'Kewarganegaraan',
            'Jenis Pendaftaran',
            'Tanggal Masuk Kuliah',
            'Mulai Semester',
            'Jalan',
            'RT',
            'RW',
            'Nama Dusun',
            'Kelurahan',
            'Kecamatan',
            'Kode Pos',
            'Jenis Tinggal',
            'Alat Transportasi',
            'Telp Rumah',
            'No HP',
            'Email',
            'Terima KPS',
            'No KPS',
            'NIK Ayah',
            'Nama Ayah',
            'Tanggal Lahir Ayah',
            'Pendidikan Ayah',
            'Pekerjaan Ayah',
            'Penghasilan Ayah',
            'NIK Ibu',
            'Nama Ibu',
            'Tanggal Lahir Ibu',
            'Pendidikan Ibu',
            'Pekerjaan Ibu',
            'Penghasilan Ibu',
            'Nama Wali',
            'Tanggal Lahir Wali',
            'Pendidikan Wali',
            'Pekerjaan Wali',
            'Penghasilan Wali',
            'Kode Prodi',
            'Nama Prodi',
            'SKS Diakui',
            'Kode PT Asal',
            'Nama PT Asal',
            'Kode Prodi Asal',
            'Nama Prodi Asal',
            'Jenis Pembiayaan',
            'Jumlah Biaya Masuk',
        ];
    }

    /**
     * @param mixed $mahasiswa
     * @return array
     */
    public function map($mahasiswa): array
    {
        $tahunMasuk = '';
        if ($mahasiswa->tgl_masuk) {
            try {
                $tahunMasuk = \Carbon\Carbon::parse($mahasiswa->tgl_masuk)->format('Y');
            } catch (\Exception $e) {
                $tahunMasuk = '';
            }
        }

        return [
            $mahasiswa->nim,
            $mahasiswa->nama,
            $mahasiswa->tem_lahir ?? '',
            $mahasiswa->tgl_lahir ? \Carbon\Carbon::parse($mahasiswa->tgl_lahir)->format('Y-m-d') : '',
            $mahasiswa->gender ?? '',
            $mahasiswa->no_ktp ?? '',           // NIK
            $mahasiswa->nama_agama ?? '',       // Agama
            $mahasiswa->nisn ?? '',             // NISN (kalau ada di tabel)
            $mahasiswa->id_jalur_masuk ?? '',   // Jalur Pendaftaran
            $mahasiswa->id_npwp_mhs ?? '',      // NPWP
            'ID',                   // Kewarganegaraan (kalau ada)
            $mahasiswa->id_jns_daftar ?? '',    // Jenis Pendaftaran
            $mahasiswa->tgl_masuk ?? '',        // Tanggal Masuk Kuliah
            $mahasiswa->smawlmsmhs ?? '',       // Mulai Semester
            $mahasiswa->alm1_surat ?? '',
            $mahasiswa->rt ?? '',
            $mahasiswa->rw ?? '',
            $mahasiswa->dusun ?? '',
            $mahasiswa->kelurahan ?? '',
            $mahasiswa->id_wilayah ?? '',     // Kecamatan
            $mahasiswa->kode_pos ?? '',
            $mahasiswa->id_jns_tinggal ?? '',   // Jenis Tinggal
            $mahasiswa->id_alat_transport ?? '', // Alat Transportasi
            $mahasiswa->telp_ortu ?? '',        // Telp Rumah
            $mahasiswa->hp1 ?? '',              // No HP
            $mahasiswa->email ?? '',
            $mahasiswa->id_kps, // Terima KPS
            $mahasiswa->no_kps ?? '',           // No KPS
            $mahasiswa->nik_ayah ?? '',
            $mahasiswa->nama_ayah ?? '',
            $mahasiswa->tanggal_lahir_ayah ?? '',
            $mahasiswa->didik_ayah ?? '',
            $mahasiswa->kerja_ayah ?? '',
            $mahasiswa->id_penghasilan_ayah ?? '',
            $mahasiswa->nik_ibu ?? '',
            $mahasiswa->nama_ibu ?? '',
            $mahasiswa->tanggal_lahir_ibu ?? '',
            $mahasiswa->didik_ibu ?? '',
            $mahasiswa->kerja_ibu ?? '',
            $mahasiswa->id_penghasilan_ibu ?? '',
            $mahasiswa->nama_wali ?? '',
            $mahasiswa->tanggal_lahir_wali ?? '',
            $mahasiswa->didik_wali ?? '',
            $mahasiswa->kerja_wali ?? '',
            $mahasiswa->id_penghasilan_wali ?? '',
            $mahasiswa->kdpst ?? '',            // Kode Prodi
            $mahasiswa->namajrs ?? '',          // Nama Prodi
            $mahasiswa->sksdimsmhs ?? '',       // SKS Diakui
            $mahasiswa->id_perguruan_tinggi_asal ?? '',
            $mahasiswa->nama_perguruan_tinggi_asal ?? '',
            $mahasiswa->id_prodi_asal ?? '',
            $mahasiswa->nama_program_studi_asal ?? '',
            $mahasiswa->id_pembiayaan ?? '',    // Jenis Pembiayaan
            $mahasiswa->biaya_masuk ?? '',      // Jumlah Biaya Masuk
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

    public function columnFormats(): array
    {
        $formats = [];

        // misalnya jumlah kolom kamu 56 (A sampai BD)
        $lastColumn = 56;

        for ($i = 0; $i < $lastColumn; $i++) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
            $formats[$col] = NumberFormat::FORMAT_TEXT;
        }

        return $formats;
    }
}
