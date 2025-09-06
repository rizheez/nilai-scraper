@extends('layouts.app')

@section('title', 'Data Nilai - Nilai Scraper')
@section('page-title', 'Data Nilai')

@section('content')
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-bar-chart me-2"></i>
                                Filter & Pencarian
                            </h5>
                        </div>
                        <div class="col-auto">
                            <div class="btn-group" role="group">
                                <a href="{{ route('export.nilai', 'json') }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}"
                                    class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-file-earmark-code me-1"></i>
                                    Export JSON
                                </a>
                                <a href="{{ route('export.nilai', 'excel') }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}"
                                    class="btn btn-outline-success btn-sm">
                                    <i class="bi bi-file-earmark-excel me-1"></i>
                                    Export Excel
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('dashboard.nilai') }}">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="mata_kuliah" class="form-label">Mata Kuliah</label>
                                    <select class="form-select" id="mata_kuliah" name="mata_kuliah">
                                        <option value="">Semua Mata Kuliah</option>
                                        @foreach ($mataKuliah as $mk)
                                            <option value="{{ $mk->id }}"
                                                {{ request('mata_kuliah') == $mk->id ? 'selected' : '' }}>
                                                {{ $mk->nama_mk }} - {{ $mk->kelas }} ({{ $mk->nama_dosen }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="mb-3">
                                    <label for="tahun_masuk" class="form-label">Tahun Masuk</label>
                                    <select class="form-select" id="tahun_masuk" name="tahun_masuk">
                                        <option value="">Semua Tahun</option>
                                        @foreach ($availableYears as $year)
                                            <option value="{{ $year }}"
                                                {{ request('tahun_masuk') == $year ? 'selected' : '' }}>
                                                {{ $year }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="search" class="form-label">Pencarian (NIM / Nama)</label>
                                    <input type="text" class="form-control" id="search" name="search"
                                        value="{{ request('search') }}" placeholder="Masukkan NIM atau nama mahasiswa...">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="mb-3">
                                    <label class="form-label">&nbsp;</label>
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-search me-1"></i>
                                            Cari
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if(request()->hasAny(['mata_kuliah', 'tahun_masuk', 'search']))
                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="alert alert-info py-2">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div>
                                                <i class="bi bi-funnel me-1"></i>
                                                <strong>Filter Aktif:</strong>
                                                @if(request('mata_kuliah'))
                                                    <span class="badge bg-primary ms-1">Mata Kuliah: {{ collect($mataKuliah)->where('id', request('mata_kuliah'))->first()->nama_mk ?? 'ID: ' . request('mata_kuliah') }}</span>
                                                @endif
                                                @if(request('tahun_masuk'))
                                                    <span class="badge bg-success ms-1">Tahun Masuk: {{ request('tahun_masuk') }}</span>
                                                @endif
                                                @if(request('search'))
                                                    <span class="badge bg-warning ms-1">Pencarian: "{{ request('search') }}"</span>
                                                @endif
                                            </div>
                                            <a href="{{ route('dashboard.nilai') }}" class="btn btn-outline-secondary btn-sm">
                                                <i class="bi bi-x-circle me-1"></i>
                                                Reset Filter
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-table me-2"></i>
                        Daftar Nilai ({{ $nilai->total() }} total)
                    </h5>
                </div>
                <div class="card-body">
                    @if ($nilai->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>NIM</th>
                                        <th>Nama</th>
                                        <th>Mata Kuliah</th>
                                        <th>Nilai</th>
                                        <th>Kehadiran</th>
                                        <th>Projek</th>
                                        <th>Quiz</th>
                                        <th>Tugas</th>
                                        <th>UTS</th>
                                        <th>UAS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($nilai as $n)
                                        <tr>
                                            <td>
                                                <code>{{ $n->nim }}</code>
                                            </td>
                                            <td>
                                                <div class="fw-semibold">{{ $n->nama }}</div>
                                            </td>
                                            <td>
                                                @if ($n->mataKuliah)
                                                    <div class="fw-semibold">{{ $n->mataKuliah->nama_mk }}</div>
                                                    <small class="text-muted">{{ $n->mataKuliah->kelas }} -
                                                        {{ $n->mataKuliah->nama_dosen }}</small>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($n->nil_angka || $n->nil_huruf)
                                                    <div>
                                                        @if ($n->nil_angka)
                                                            <span
                                                                class="badge {{ intval($n->nil_angka) >= 80 ? 'bg-success' : (intval($n->nil_angka) >= 70 ? 'bg-warning' : 'bg-danger') }}">
                                                                {{ $n->nil_angka }}
                                                            </span>
                                                        @endif
                                                        @if ($n->nil_huruf)
                                                            <span
                                                                class="badge bg-secondary ms-1">{{ $n->nil_huruf }}</span>
                                                        @endif
                                                    </div>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($n->hadir)
                                                    <span class="badge bg-info">{{ $n->hadir }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($n->projek)
                                                    <span class="badge bg-primary">{{ $n->projek }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($n->quiz)
                                                    <span class="badge bg-warning">{{ $n->quiz }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($n->tugas)
                                                    <span class="badge bg-success">{{ $n->tugas }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($n->uts)
                                                    <span class="badge bg-secondary">{{ $n->uts }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($n->uas)
                                                    <span class="badge bg-dark">{{ $n->uas }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center">
                            {{ $nilai->withQueryString()->links() }}
                        </div>
                    @else
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-clipboard-x fs-1 d-block mb-3"></i>
                            <h5>Tidak ada data nilai</h5>
                            <p>Belum ada data nilai yang tersedia atau sesuai dengan filter pencarian.</p>
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
@endsection
