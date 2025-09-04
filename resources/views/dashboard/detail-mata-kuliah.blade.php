@extends('layouts.app')

@section('title', 'Detail Mata Kuliah - Nilai Scraper')
@section('page-title', 'Detail Mata Kuliah')

@section('content')
    <div class="row mb-3">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.mata-kuliah') }}">Mata Kuliah</a></li>
                    <li class="breadcrumb-item active">{{ $mataKuliah->nama_mk }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-book me-2"></i>
                        Informasi Mata Kuliah
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-2"
                            style="width: 80px; height: 80px;">
                            <i class="bi bi-book fs-1 text-white"></i>
                        </div>
                        <h5 class="card-title">{{ $mataKuliah->nama_mk }}</h5>
                        <p class="text-muted">{{ $mataKuliah->kode_mk }}</p>
                    </div>

                    <table class="table table-sm">
                        <tr>
                            <td><strong>Kode MK</strong></td>
                            <td>{{ $mataKuliah->kode_mk }}</td>
                        </tr>
                        <tr>
                            <td><strong>Nama Mata Kuliah</strong></td>
                            <td>{{ $mataKuliah->nama_mk }}</td>
                        </tr>
                        <tr>
                            <td><strong>Kelas</strong></td>
                            <td><span class="badge bg-secondary">{{ $mataKuliah->kelas }}</span></td>
                        </tr>
                        <tr>
                            <td><strong>Dosen Pengampu</strong></td>
                            <td>{{ $mataKuliah->nama_dosen }}</td>
                        </tr>
                        <tr>
                            <td><strong>Semester</strong></td>
                            <td>
                                @if ($mataKuliah->semester)
                                    <span class="badge bg-info">{{ $mataKuliah->semester->keterangan }}</span>
                                @else
                                    <span class="badge bg-warning">{{ $mataKuliah->smtthnakd }}</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Jurusan</strong></td>
                            <td><span class="badge bg-primary">{{ $mataKuliah->nama_jrs }}</span></td>
                        </tr>
                        <tr>
                            <td><strong>Total Mahasiswa</strong></td>
                            <td>
                                <span class="badge bg-success">{{ $mataKuliah->nilai->count() }}</span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            @if ($mataKuliah->bobot)
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-speedometer2 me-2"></i>
                            Bobot Penilaian
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6 mb-3">
                                <div class="bg-light p-3 rounded">
                                    <div class="fs-4 fw-bold text-primary">{{ $mataKuliah->bobot->hadir ?? '0' }}%</div>
                                    <small class="text-muted">Kehadiran</small>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="bg-light p-3 rounded">
                                    <div class="fs-4 fw-bold text-success">{{ $mataKuliah->bobot->projek ?? '0' }}%</div>
                                    <small class="text-muted">Projek</small>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="bg-light p-3 rounded">
                                    <div class="fs-4 fw-bold text-warning">{{ $mataKuliah->bobot->quiz ?? '0' }}%</div>
                                    <small class="text-muted">Quiz</small>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="bg-light p-3 rounded">
                                    <div class="fs-4 fw-bold text-info">{{ $mataKuliah->bobot->tugas ?? '0' }}%</div>
                                    <small class="text-muted">Tugas</small>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="bg-light p-3 rounded">
                                    <div class="fs-4 fw-bold text-secondary">{{ $mataKuliah->bobot->uts ?? '0' }}%</div>
                                    <small class="text-muted">UTS</small>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="bg-light p-3 rounded">
                                    <div class="fs-4 fw-bold text-dark">{{ $mataKuliah->bobot->uas ?? '0' }}%</div>
                                    <small class="text-muted">UAS</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-people me-2"></i>
                                Daftar Nilai Mahasiswa
                            </h5>
                        </div>
                        <div class="col-auto">
                            <div class="btn-group" role="group">
                                <a href="{{ route('export.nilai', 'json') }}?mata_kuliah={{ $mataKuliah->id }}"
                                    class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-file-earmark-code me-1"></i>
                                    Export JSON
                                </a>
                                <a href="{{ route('export.nilai', 'excel') }}?mata_kuliah={{ $mataKuliah->id }}"
                                    class="btn btn-outline-success btn-sm">
                                    <i class="bi bi-file-earmark-excel me-1"></i>
                                    Export CSV
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if ($mataKuliah->nilai->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>NIM</th>
                                        <th>Nama Mahasiswa</th>
                                        <th>Nilai Akhir</th>
                                        <th>Grade</th>
                                        <th>Kehadiran</th>
                                        <th>Projek</th>
                                        <th>Quiz</th>
                                        <th>Tugas</th>
                                        <th>UTS</th>
                                        <th>UAS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($mataKuliah->nilai as $nilai)
                                        <tr>
                                            <td>
                                                <code>{{ $nilai->nim }}</code>
                                            </td>
                                            <td>
                                                <div class="fw-semibold">{{ $nilai->nama }}</div>
                                                @if ($nilai->mahasiswa)
                                                    <small class="text-muted">{{ $nilai->mahasiswa->nama_jrs }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($nilai->nil_angka)
                                                    <span
                                                        class="badge {{ intval($nilai->nil_angka) >= 80 ? 'bg-success' : (intval($nilai->nil_angka) >= 70 ? 'bg-warning' : 'bg-danger') }}">
                                                        {{ $nilai->nil_angka }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($nilai->nil_huruf)
                                                    <span class="badge bg-primary">{{ $nilai->nil_huruf }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($nilai->hadir)
                                                    <span class="badge bg-info">{{ $nilai->hadir }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($nilai->projek)
                                                    <span class="badge bg-success">{{ $nilai->projek }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($nilai->quiz)
                                                    <span class="badge bg-warning">{{ $nilai->quiz }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($nilai->tugas)
                                                    <span class="badge bg-info">{{ $nilai->tugas }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($nilai->uts)
                                                    <span class="badge bg-secondary">{{ $nilai->uts }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($nilai->uas)
                                                    <span class="badge bg-dark">{{ $nilai->uas }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Statistics -->
                        <div class="row mt-4">
                            <div class="col-md-3">
                                <div class="text-center p-3 bg-light rounded">
                                    <div class="fs-4 fw-bold text-success">
                                        {{ $mataKuliah->nilai->where('nil_angka', '>=', 80)->count() }}
                                    </div>
                                    <small class="text-muted">A (≥80)</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center p-3 bg-light rounded">
                                    <div class="fs-4 fw-bold text-warning">
                                        {{ $mataKuliah->nilai->whereBetween('nil_angka', [70, 79])->count() }}
                                    </div>
                                    <small class="text-muted">B (70-79)</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center p-3 bg-light rounded">
                                    <div class="fs-4 fw-bold text-info">
                                        {{ $mataKuliah->nilai->whereBetween('nil_angka', [60, 69])->count() }}
                                    </div>
                                    <small class="text-muted">C (60-69)</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center p-3 bg-light rounded">
                                    <div class="fs-4 fw-bold text-danger">
                                        {{ $mataKuliah->nilai->where('nil_angka', '<', 60)->count() }}
                                    </div>
                                    <small class="text-muted">D (<60)< /small>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-clipboard-x fs-1 d-block mb-3"></i>
                            <h5>Belum ada data nilai</h5>
                            <p>Belum ada data nilai untuk mata kuliah ini.</p>
                            <a href="{{ route('scraping.index') }}" class="btn btn-primary">
                                <i class="bi bi-cloud-download me-2"></i>
                                Mulai Scraping Data
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between">
                <a href="{{ route('dashboard.mata-kuliah') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-2"></i>
                    Kembali ke Daftar Mata Kuliah
                </a>
            </div>
        </div>
    </div>
@endsection
