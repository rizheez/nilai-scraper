@extends('layouts.app')

@section('title', 'Scraping Data - Nilai Scraper')
@section('page-title', 'Scraping Data')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-cloud-download me-2"></i>
                        Login SIAKAD
                    </h5>
                </div>
                <div class="card-body">
                    <div id="login-section">
                        <form id="login-form">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="username" class="form-label">Username SIAKAD</label>
                                        <input type="text" class="form-control" id="username" name="username" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="password" class="form-label">Password SIAKAD</label>
                                        <input type="password" class="form-control" id="password" name="password" required>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-box-arrow-in-right me-2"></i>
                                Login
                            </button>
                        </form>
                    </div>

                    <div id="authenticated-section" style="display: none;">
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle me-2"></i>
                            Berhasil login sebagai: <span id="username-display"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="scraping-section" style="display: none;">
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-gear me-2"></i>
                            Pengaturan Scraping
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="scraping-form">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="jurusan" class="form-label">Pilih Jurusan</label>
                                        <select class="form-select" id="jurusan" name="jurusan_id" required>
                                            <option value="">Pilih Jurusan...</option>
                                            @foreach ($jurusan as $jrs)
                                                <option value="{{ $jrs->id }}">{{ $jrs->nama_jrs }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="semester" class="form-label">Pilih Semester</label>
                                        <select class="form-select" id="semester" name="semester" required>
                                            <option value="">Pilih Semester...</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="scraping-type" class="form-label">Jenis Scraping</label>
                                        <select class="form-select" id="scraping-type" name="scraping_type" required>
                                            <option value="">Pilih Jenis...</option>
                                            <option value="nilai">Scrape Nilai Mata Kuliah</option>
                                            <option value="mahasiswa">Scrape Data Mahasiswa</option>
                                            <option value="both">Scrape Keduanya</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-success me-2">
                                <i class="bi bi-play-circle me-2"></i>
                                Mulai Scraping
                            </button>
                            <button type="button" class="btn btn-secondary" id="refresh-semesters">
                                <i class="bi bi-arrow-clockwise me-2"></i>
                                Refresh Semester
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-info-circle me-2"></i>
                            Status Scraping
                        </h5>
                    </div>
                    <div class="card-body">
                        <div id="scraping-status">
                            <div class="text-center text-muted py-4">
                                <i class="bi bi-hourglass-split fs-1 d-block mb-2"></i>
                                Siap untuk memulai scraping
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Check if already authenticated
            checkAuthentication();

            // Login form handler
            $('#login-form').on('submit', function(e) {
                e.preventDefault();

                const submitBtn = $(this).find('button[type="submit"]');
                const originalText = submitBtn.html();

                submitBtn.html('<i class="bi bi-hourglass-split me-2"></i>Logging in...').prop('disabled',
                    true);

                $.ajax({
                    url: '{{ route('scraping.login') }}',
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.success) {
                            $('#username-display').text($('#username').val());
                            $('#login-section').hide();
                            $('#authenticated-section').show();
                            $('#scraping-section').show();
                            loadSemesters();
                        } else {
                            alert('Login gagal: ' + response.message);
                        }
                    },
                    error: function(xhr) {
                        const response = xhr.responseJSON;
                        alert('Login gagal: ' + (response ? response.message :
                            'Terjadi kesalahan'));
                    },
                    complete: function() {
                        submitBtn.html(originalText).prop('disabled', false);
                    }
                });
            });

            // Scraping form handler
            $('#scraping-form').on('submit', function(e) {
                e.preventDefault();

                const formData = $(this).serialize();
                const scrapingType = $('#scraping-type').val();
                const submitBtn = $(this).find('button[type="submit"]');
                const originalText = submitBtn.html();

                submitBtn.html('<i class="bi bi-hourglass-split me-2"></i>Processing...').prop('disabled',
                    true);

                updateScrapingStatus('Memulai scraping...', 'info');

                if (scrapingType === 'nilai' || scrapingType === 'both') {
                    scrapeNilai(formData, function() {
                        if (scrapingType === 'both') {
                            scrapeMahasiswa(formData, function() {
                                submitBtn.html(originalText).prop('disabled', false);
                            });
                        } else {
                            submitBtn.html(originalText).prop('disabled', false);
                        }
                    });
                } else if (scrapingType === 'mahasiswa') {
                    scrapeMahasiswa(formData, function() {
                        submitBtn.html(originalText).prop('disabled', false);
                    });
                }
            });

            // Refresh semesters button
            $('#refresh-semesters').on('click', function() {
                loadSemesters();
            });

            function checkAuthentication() {
                $.ajax({
                    url: '{{ route('scraping.check-session') }}',
                    method: 'GET',
                    success: function(response) {
                        if (response.valid) {
                            $('#login-section').hide();
                            $('#authenticated-section').show();
                            $('#scraping-section').show();
                            loadSemesters();
                        }
                    }
                });
            }

            function loadSemesters() {
                $.ajax({
                    url: '{{ route('scraping.semesters') }}',
                    method: 'GET',
                    success: function(response) {
                        const semesterSelect = $('#semester');
                        semesterSelect.empty().append('<option value="">Pilih Semester...</option>');

                        response.semesters.forEach(function(semester) {
                            semesterSelect.append(
                                '<option value="' + semester.smtthnakd + '">' + semester
                                .keterangan + '</option>'
                            );
                        });
                    },
                    error: function() {
                        alert('Gagal memuat daftar semester');
                    }
                });
            }

            function scrapeNilai(formData, callback) {
                updateScrapingStatus('Melakukan scraping nilai mata kuliah...', 'info');

                $.ajax({
                    url: '{{ route('scraping.scrape-nilai') }}',
                    method: 'POST',
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            updateScrapingStatus(
                                `Berhasil scraping nilai: ${response.processed} dari ${response.total} mata kuliah diproses`,
                                'success'
                            );
                            if (response.errors.length > 0) {
                                updateScrapingStatus('Beberapa error: ' + response.errors.join(', '),
                                    'warning');
                            }
                        } else {
                            updateScrapingStatus('Gagal scraping nilai', 'danger');
                        }
                    },
                    error: function() {
                        updateScrapingStatus('Error saat scraping nilai', 'danger');
                    },
                    complete: function() {
                        if (callback) callback();
                    }
                });
            }

            function scrapeMahasiswa(formData, callback) {
                updateScrapingStatus('Melakukan scraping data mahasiswa...', 'info');

                $.ajax({
                    url: '{{ route('scraping.scrape-mahasiswa') }}',
                    method: 'POST',
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            updateScrapingStatus(
                                `Berhasil scraping mahasiswa: ${response.processed} dari ${response.total} mahasiswa diproses`,
                                'success'
                            );
                            if (response.errors.length > 0) {
                                updateScrapingStatus('Beberapa error: ' + response.errors.join(', '),
                                    'warning');
                            }
                        } else {
                            updateScrapingStatus('Gagal scraping mahasiswa', 'danger');
                        }
                    },
                    error: function() {
                        updateScrapingStatus('Error saat scraping mahasiswa', 'danger');
                    },
                    complete: function() {
                        if (callback) callback();
                    }
                });
            }

            function updateScrapingStatus(message, type) {
                const alertClass = 'alert-' + type;
                const iconClass = type === 'success' ? 'check-circle' :
                    type === 'danger' ? 'exclamation-triangle' :
                    type === 'warning' ? 'exclamation-triangle' : 'info-circle';

                const statusHtml = `
            <div class="alert ${alertClass}">
                <i class="bi bi-${iconClass} me-2"></i>
                ${message}
            </div>
        `;

                $('#scraping-status').html(statusHtml);
            }
        });
    </script>
@endpush
