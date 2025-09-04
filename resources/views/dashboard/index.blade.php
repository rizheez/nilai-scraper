@extends('layouts.app')

@section('title', 'Dashboard - Nilai Scraper')
@section('page-title', 'Dashboard')

@section('content')
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stats-card text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="fs-2 fw-bold">{{ $stats['total_jurusan'] }}</div>
                            <div>Total Jurusan</div>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-building fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stats-card-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="fs-2 fw-bold">{{ $stats['total_mahasiswa'] }}</div>
                            <div>Total Mahasiswa</div>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-people fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stats-card-warning text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="fs-2 fw-bold">{{ $stats['total_mata_kuliah'] }}</div>
                            <div>Mata Kuliah</div>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-book fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stats-card-info text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="fs-2 fw-bold">{{ $stats['total_nilai'] }}</div>
                            <div>Data Nilai</div>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-bar-chart fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-activity me-2"></i>
                        Aktivitas Terbaru
                    </h5>
                </div>
                <div class="card-body">
                    @if (count($recentActivities) > 0)
                        <div class="list-group list-group-flush">
                            @foreach ($recentActivities as $activity)
                                <div class="list-group-item d-flex justify-content-between align-items-start">
                                    <div class="ms-2 me-auto">
                                        <div class="fw-bold">
                                            @if ($activity['type'] === 'mata_kuliah')
                                                <i class="bi bi-book text-primary me-2"></i>
                                            @else
                                                <i class="bi bi-person text-success me-2"></i>
                                            @endif
                                            {{ $activity['message'] }}
                                        </div>
                                        <small class="text-muted">{{ $activity['time']->diffForHumans() }}</small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            Belum ada aktivitas
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-pie-chart me-2"></i>
                        Statistik Jurusan
                    </h5>
                </div>
                <div class="card-body">
                    @if (count($jurusanStats) > 0)
                        @foreach ($jurusanStats as $stat)
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="flex-grow-1">
                                    <div class="fw-semibold">{{ $stat->nama_jrs }}</div>
                                    <div class="progress mt-1" style="height: 8px;">
                                        <div class="progress-bar"
                                            style="width: {{ ($stat->total / $jurusanStats->max('total')) * 100 }}%"></div>
                                    </div>
                                </div>
                                <div class="ms-3">
                                    <span class="badge bg-primary">{{ $stat->total }}</span>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-bar-chart fs-1 d-block mb-2"></i>
                            Belum ada data statistik
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-speedometer2 me-2"></i>
                        Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('scraping.index') }}" class="btn btn-primary w-100 py-3">
                                <i class="bi bi-cloud-download fs-4 d-block mb-2"></i>
                                <div>Mulai Scraping</div>
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('dashboard.mahasiswa') }}" class="btn btn-success w-100 py-3">
                                <i class="bi bi-people fs-4 d-block mb-2"></i>
                                <div>Lihat Mahasiswa</div>
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('dashboard.mata-kuliah') }}" class="btn btn-warning w-100 py-3">
                                <i class="bi bi-book fs-4 d-block mb-2"></i>
                                <div>Mata Kuliah</div>
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('dashboard.nilai') }}" class="btn btn-info w-100 py-3">
                                <i class="bi bi-bar-chart fs-4 d-block mb-2"></i>
                                <div>Data Nilai</div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
