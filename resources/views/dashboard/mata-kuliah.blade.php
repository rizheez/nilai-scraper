@extends('layouts.app')

@section('title', 'Mata Kuliah - Nilai Scraper')
@section('page-title', 'Data Mata Kuliah')

@section('content')
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-book me-2"></i>
                                Filter & Pencarian
                            </h5>
                        </div>
                        <div class="col-auto">
                            <div class="btn-group" role="group">
                                <a href="{{ route('export.mata-kuliah', 'json') }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}"
                                    class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-file-earmark-code me-1"></i>
                                    Export JSON
                                </a>
                                <a href="{{ route('export.mata-kuliah', 'excel') }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}"
                                    class="btn btn-outline-success btn-sm">
                                    <i class="bi bi-file-earmark-excel me-1"></i>
                                    Export Excel
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('dashboard.mata-kuliah') }}">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="jurusan" class="form-label">Jurusan</label>
                                    <select class="form-select" id="jurusan" name="jurusan">
                                        <option value="">Semua Jurusan</option>
                                        @foreach ($jurusan as $jrs)
                                            <option value="{{ $jrs->kode_jrs }}"
                                                {{ request('jurusan') == $jrs->kode_jrs ? 'selected' : '' }}>
                                                {{ $jrs->nama_jrs }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="semester" class="form-label">Semester</label>
                                    <select class="form-select" id="semester" name="semester">
                                        <option value="">Semua Semester</option>
                                        @foreach ($semester as $sem)
                                            <option value="{{ $sem->smtthnakd }}"
                                                {{ request('semester') == $sem->smtthnakd ? 'selected' : '' }}>
                                                {{ $sem->keterangan }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="search" class="form-label">Pencarian</label>
                                    <input type="text" class="form-control" id="search" name="search"
                                        value="{{ request('search') }}" placeholder="Nama MK, Kode MK, atau Dosen...">
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
                        Daftar Mata Kuliah ({{ $mataKuliah->total() }} total)
                    </h5>
                </div>
                <div class="card-body">
                    @if ($mataKuliah->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Kode MK</th>
                                        <th>Nama Mata Kuliah</th>
                                        <th>Kelas</th>
                                        <th>Dosen</th>
                                        <th>Semester</th>
                                        <th>Jurusan</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($mataKuliah as $mk)
                                        <tr>
                                            <td>
                                                <code>{{ $mk->kode_mk }}</code>
                                            </td>
                                            <td>
                                                <div class="fw-semibold">{{ $mk->nama_mk }}</div>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">{{ $mk->kelas }}</span>
                                            </td>
                                            <td>
                                                <div class="text-truncate" style="max-width: 200px;"
                                                    title="{{ $mk->nama_dosen }}">
                                                    {{ $mk->nama_dosen }}
                                                </div>
                                            </td>
                                            <td>
                                                @if ($mk->semester)
                                                    <span class="badge bg-info">{{ $mk->semester->keterangan }}</span>
                                                @else
                                                    <span class="badge bg-warning">{{ $mk->smtthnakd }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-primary">{{ $mk->nama_jrs }}</span>
                                            </td>
                                            <td>
                                                <a href="{{ route('dashboard.mata-kuliah-detail', $mk->id) }}"
                                                    class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-eye me-1"></i>
                                                    Detail
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->

                            {{ $mataKuliah->withQueryString()->links() }}
                    @else
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-book-half fs-1 d-block mb-3"></i>
                            <h5>Tidak ada data mata kuliah</h5>
                            <p>Belum ada data mata kuliah yang tersedia atau sesuai dengan filter pencarian.</p>
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
