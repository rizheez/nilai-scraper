<?php

namespace Database\Seeders;

use App\Models\Jurusan;
use Illuminate\Database\Seeder;

class JurusanSeeder extends Seeder
{
    public function run(): void
    {
        $jurusanData = [
            [
                'jrs_id' => '1',
                'kode_jrs' => '62201',
                'nama_jrs' => 'Akuntansi (S1)'
            ],
            [
                'jrs_id' => '7',
                'kode_jrs' => '23201',
                'nama_jrs' => 'Arsitektur (S1)'
            ],
            [
                'jrs_id' => '6',
                'kode_jrs' => '90221',
                'nama_jrs' => 'Desain Interior (S1)'
            ],
            [
                'jrs_id' => '2',
                'kode_jrs' => '48201',
                'nama_jrs' => 'Farmasi (S1)'
            ],
            [
                'jrs_id' => '5',
                'kode_jrs' => '64201',
                'nama_jrs' => 'Hubungan Internasional (S1)'
            ],
            [
                'jrs_id' => '4',
                'kode_jrs' => '70201',
                'nama_jrs' => 'Ilmu Komunikasi (S1)'
            ],
            [
                'jrs_id' => '3',
                'kode_jrs' => '86202',
                'nama_jrs' => 'Pendidikan Anak Usia Dini (S1)'
            ],
            [
                'jrs_id' => '8',
                'kode_jrs' => '26201',
                'nama_jrs' => 'Teknik Industri (S1)'
            ],
            [
                'jrs_id' => '9',
                'kode_jrs' => '55202',
                'nama_jrs' => 'Teknik Informatika (S1)'
            ],
            [
                'jrs_id' => '10',
                'kode_jrs' => '41211',
                'nama_jrs' => 'Teknologi Industri Pertanian (S1)'
            ]
        ];

        foreach ($jurusanData as $data) {
            Jurusan::updateOrCreate(
                ['kode_jrs' => $data['kode_jrs']],
                $data
            );
        }
    }
}
