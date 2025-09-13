@extends('layouts.app')

@section('title', 'Data Mahasiswa - Nilai Scraper')
@section('page-title', 'Data Mahasiswa')

@section('content')
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-people me-2"></i>
                                Filter & Pencarian
                            </h5>
                        </div>
                        <div class="col-auto">
                            <div class="btn-group" role="group">
                                <a href="{{ route('export.mahasiswa', 'json') }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}"
                                    class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-file-earmark-code me-1"></i>
                                    Export JSON
                                </a>
                                <a href="{{ route('export.mahasiswa', 'excel') }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}"
                                    class="btn btn-outline-success btn-sm">
                                    <i class="bi bi-file-earmark-excel me-1"></i>
                                    Export Excel
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('dashboard.mahasiswa') }}">
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

                        @if (request()->hasAny(['jurusan', 'tahun_masuk', 'search']))
                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="alert alert-info py-2">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div>
                                                <i class="bi bi-funnel me-1"></i>
                                                <strong>Filter Aktif:</strong>
                                                @if (request('jurusan'))
                                                    <span class="badge bg-primary ms-1">Jurusan:
                                                        {{ collect($jurusan)->where('kode_jrs', request('jurusan'))->first()->nama_jrs ?? request('jurusan') }}</span>
                                                @endif
                                                @if (request('tahun_masuk'))
                                                    <span class="badge bg-success ms-1">Tahun Masuk:
                                                        {{ request('tahun_masuk') }}</span>
                                                @endif
                                                @if (request('search'))
                                                    <span class="badge bg-warning ms-1">Pencarian:
                                                        "{{ request('search') }}"</span>
                                                @endif
                                            </div>
                                            <a href="{{ route('dashboard.mahasiswa') }}"
                                                class="btn btn-outline-secondary btn-sm">
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
                        Daftar Mahasiswa ({{ $mahasiswa->total() }} total)
                    </h5>
                </div>
                <div class="card-body">
                    @if ($mahasiswa->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>NIM</th>
                                        <th>Nama</th>
                                        <th>Jurusan</th>
                                        <th>Tahun Masuk</th>
                                        <th>Email</th>
                                        <th>IPK</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($mahasiswa as $mhs)
                                        <tr>
                                            <td>
                                                <code>{{ $mhs->nim }}</code>
                                            </td>
                                            <td>
                                                <div class="fw-semibold">{{ $mhs->nama }}</div>
                                                @if ($mhs->tem_lahir && $mhs->tgl_lahir)
                                                    <small class="text-muted">
                                                        {{ $mhs->tem_lahir }},
                                                        {{ \Carbon\Carbon::parse($mhs->tgl_lahir)->format('d/m/Y') }}
                                                    </small>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-primary">{{ $mhs->namajrs ?: $mhs->kodejrs }}</span>
                                            </td>
                                            <td>
                                                @if ($mhs->tgl_masuk)
                                                    <span class="badge bg-info">
                                                        {{ \Carbon\Carbon::parse($mhs->tgl_masuk)->format('Y') }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($mhs->email)
                                                    <a href="mailto:{{ $mhs->email }}" class="text-decoration-none">
                                                        {{ $mhs->email }}
                                                    </a>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($mhs->ipk)
                                                    <span
                                                        class="badge {{ floatval($mhs->ipk) >= 3.5 ? 'bg-success' : (floatval($mhs->ipk) >= 3.0 ? 'bg-warning' : 'bg-danger') }}">
                                                        {{ $mhs->ipk }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('dashboard.mahasiswa-detail', $mhs->id) }}"
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
                        <div>
                            {{ $mahasiswa->withQueryString()->links() }}
                        </div>
                    @else
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-person-x fs-1 d-block mb-3"></i>
                            <h5>Tidak ada data mahasiswa</h5>
                            <p>Belum ada data mahasiswa yang tersedia atau sesuai dengan filter pencarian.</p>
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
