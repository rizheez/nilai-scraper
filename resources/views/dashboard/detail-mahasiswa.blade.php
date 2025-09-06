@extends('layouts.app')

@section('title', 'Detail Mahasiswa - Nilai Scraper')
@section('page-title', 'Detail Mahasiswa')

@section('content')
    <div class="row mb-3">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.mahasiswa') }}">Data Mahasiswa</a></li>
                    <li class="breadcrumb-item active">{{ $mahasiswa->nama }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-person-badge me-2"></i>
                        Informasi Pribadi
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        @if ($mahasiswa->foto)
                            <img src="{{ $mahasiswa->foto }}" alt="Foto {{ $mahasiswa->nama }}" class="rounded-circle mb-2"
                                width="100" height="100">
                        @else
                            <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-2"
                                style="width: 100px; height: 100px;">
                                <i class="bi bi-person fs-1 text-white"></i>
                            </div>
                        @endif
                        <h5 class="card-title">{{ $mahasiswa->nama }}</h5>
                        <p class="text-muted">NIM: {{ $mahasiswa->nim }}</p>
                    </div>

                    <table class="table table-sm">
                        <tr>
                            <td><strong>Tempat, Tanggal Lahir</strong></td>
                            <td>
                                @if ($mahasiswa->tempat_lahir && $mahasiswa->tanggal_lahir)
                                    {{ $mahasiswa->tempat_lahir }},
                                    {{ \Carbon\Carbon::parse($mahasiswa->tanggal_lahir)->format('d F Y') }}
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Jenis Kelamin</strong></td>
                            <td>{{ $mahasiswa->gender ?: '-' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Agama</strong></td>
                            <td>{{ $mahasiswa->agama ?: '-' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Email</strong></td>
                            <td>
                                @if ($mahasiswa->email)
                                    <a href="mailto:{{ $mahasiswa->email }}">{{ $mahasiswa->email }}</a>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Telepon</strong></td>
                            <td>{{ $mahasiswa->hp1 ?: $mahasiswa->telepon ?: '-' }}</td>
                        </tr>
                        <tr>
                            <td><strong>IPK</strong></td>
                            <td>
                                @if ($mahasiswa->ipk)
                                    <span
                                        class="badge {{ floatval($mahasiswa->ipk) >= 3.5 ? 'bg-success' : (floatval($mahasiswa->ipk) >= 3.0 ? 'bg-warning' : 'bg-danger') }}">
                                        {{ $mahasiswa->ipk }}
                                    </span>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-bar-chart me-2"></i>
                        Riwayat Nilai
                    </h5>
                </div>
                <div class="card-body">
                    @if ($mahasiswa->nilai->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Mata Kuliah</th>
                                        <th>Kelas</th>
                                        <th>Dosen</th>
                                        <th>Nilai Akhir</th>
                                        <th>Grade</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($mahasiswa->nilai as $nilai)
                                        <tr>
                                            <td>
                                                @if ($nilai->mataKuliah)
                                                    <div class="fw-semibold">{{ $nilai->mataKuliah->nama_mk }}</div>
                                                    <small class="text-muted">{{ $nilai->mataKuliah->kode_mk }}</small>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                @if ($nilai->mataKuliah)
                                                    <span class="badge bg-secondary">{{ $nilai->mataKuliah->kelas }}</span>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                @if ($nilai->mataKuliah)
                                                    {{ $nilai->mataKuliah->nama_dosen }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                @if ($nilai->nil_angka)
                                                    <span
                                                        class="badge {{ intval($nilai->nil_angka) >= 80 ? 'bg-success' : (intval($nilai->nil_angka) >= 70 ? 'bg-warning' : 'bg-danger') }}">
                                                        {{ $nilai->nil_angka }}
                                                    </span>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                @if ($nilai->nil_huruf)
                                                    <span class="badge bg-primary">{{ $nilai->nil_huruf }}</span>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-clipboard-x fs-1 d-block mb-2"></i>
                            <p>Belum ada data nilai untuk mahasiswa ini</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-house me-2"></i>
                        Informasi Alamat
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <td><strong>Alamat</strong></td>
                            <td>{{ $mahasiswa->alamat_surat1 ?: '-' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Kota</strong></td>
                            <td>{{ $mahasiswa->kota_surat ?: '-' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Kode Pos</strong></td>
                            <td>{{ $mahasiswa->kode_pos_surat ?: '-' }}</td>
                        </tr>
                        <tr>
                            <td><strong>RT/RW</strong></td>
                            <td>{{ $mahasiswa->rt_rw_surat ?: '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-people me-2"></i>
                        Informasi Orang Tua
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <td><strong>Nama Ayah</strong></td>
                            <td>{{ $mahasiswa->nama_ayah ?: '-' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Pekerjaan Ayah</strong></td>
                            <td>{{ $mahasiswa->kerja_ayah ?: '-' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Nama Ibu</strong></td>
                            <td>{{ $mahasiswa->nama_ibu ?: '-' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Pekerjaan Ibu</strong></td>
                            <td>{{ $mahasiswa->kerja_ibu ?: '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between">
                <a href="{{ route('dashboard.mahasiswa') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-2"></i>
                    Kembali ke Daftar Mahasiswa
                </a>
            </div>
        </div>
    </div>
@endsection
