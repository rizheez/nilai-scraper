@extends('layouts.app')

@section('title', 'Scraping Data - Nilai Scraper')
@section('page-title', 'Scraping Data')

@section('content')
    <!-- Quick Status Bar -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card border-info">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">
                                <i class="bi bi-activity me-2"></i>
                                Status Scraping Saat Ini
                            </h6>
                            <small class="text-muted" id="quick-status-text">Memuat status...</small>
                        </div>
                        <div>
                            <span class="badge bg-primary" id="active-count">0</span>
                            <span class="text-muted small">jobs aktif</span>
                            <a href="{{ route('scraping.status') }}" class="btn btn-sm btn-outline-info ms-2">
                                <i class="bi bi-eye me-1"></i>
                                Lihat Detail
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
                                        <div class="input-group">
                                            <input type="password" class="form-control" id="password" name="password"
                                                required>
                                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                                <i class="bi bi-eye" id="togglePasswordIcon"></i>
                                            </button>
                                        </div>
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
                                            <option value="batch">Batch Scraping (Keduanya)</option>
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

                        <!-- Progress Bar -->
                        <div id="progress-container" style="display: none;">
                            <div class="mb-3">
                                <label class="form-label">Progress</label>
                                <div class="progress" style="height: 25px;">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated"
                                        role="progressbar" id="progress-bar" style="width: 0%" aria-valuenow="0"
                                        aria-valuemin="0" aria-valuemax="100">
                                        <span id="progress-text">0%</span>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-2">
                                <small class="text-muted" id="progress-message">Memulai scraping...</small>
                            </div>
                            <div id="job-details" class="small text-info"></div>
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
            // Toggle password visibility
            $('#togglePassword').on('click', function() {
                const passwordField = $('#password');
                const passwordIcon = $('#togglePasswordIcon');

                if (passwordField.attr('type') === 'password') {
                    passwordField.attr('type', 'text');
                    passwordIcon.removeClass('bi-eye').addClass('bi-eye-slash');
                } else {
                    passwordField.attr('type', 'password');
                    passwordIcon.removeClass('bi-eye-slash').addClass('bi-eye');
                }
            });

            // Check if already authenticated
            $('#login-section').hide();
            checkAuthentication();

            // Load initial quick status
            loadQuickStatus();

            // Auto-refresh quick status every 5 seconds
            setInterval(loadQuickStatus, 5000);
            $('#username').val('{{ $username }}');
            $('#password').val('{{ $password }}');
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



                updateScrapingStatus('Memulai scraping...', 'info');
                showProgress(true);

                if (scrapingType === 'batch') {
                    startBatchScraping(formData, submitBtn, originalText);
                } else if (scrapingType === 'nilai') {
                    startScrapingJob('nilai', formData, submitBtn, originalText);
                } else if (scrapingType === 'mahasiswa') {
                    startScrapingJob('mahasiswa', formData, submitBtn, originalText);
                }
            });

            // Refresh semesters button
            $('#refresh-semesters').on('click', function() {
                loadSemesters();
            });

            function loadQuickStatus() {
                $.ajax({
                    url: '{{ route('scraping.active-jobs') }}',
                    method: 'GET',
                    success: function(response) {
                        const totalActive = response.total_active;
                        $('#active-count').text(totalActive);

                        if (totalActive > 0) {
                            const jobText = totalActive === 1 ? 'job' : 'jobs';
                            $('#quick-status-text').html(
                                `<span class="text-warning"><i class="bi bi-gear-fill me-1"></i>${totalActive} ${jobText} sedang berjalan</span>`
                            );
                        } else {
                            $('#quick-status-text').html(
                                `<span class="text-success"><i class="bi bi-check-circle-fill me-1"></i>Tidak ada scraping yang berjalan</span>`
                            );
                        }
                    },
                    error: function() {
                        $('#quick-status-text').html(
                            `<span class="text-muted"><i class="bi bi-exclamation-triangle me-1"></i>Gagal memuat status</span>`
                        );
                    }
                });
            }

            function checkAuthentication() {
                $.ajax({
                    url: '{{ route('scraping.check-session') }}',
                    method: 'GET',
                    success: function(response) {
                        if (response.valid) {
                            console.log(response)
                            $('#login-section').hide();
                            $('#authenticated-section').show();
                            $('#scraping-section').show();
                            $('#username-display').text(response.username);
                            loadSemesters();
                        } else {
                            $('#login-section').show();
                            $('#authenticated-section').hide();
                            $('#scraping-section').hide();

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

            function startScrapingJob(type, formData, submitBtn, originalText) {
                const url = type === 'nilai' ? '{{ route('scraping.scrape-nilai') }}' :
                    '{{ route('scraping.scrape-mahasiswa') }}';

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: formData,
                    success: function(response) {
                        if (response.success && response.queue) {
                            updateScrapingStatus(`Job ${type} dimulai dengan ID: ${response.job_id}`,
                                'info');
                            $('#job-details').html(
                                `<strong>Job ID:</strong> ${response.job_id}<br><strong>Type:</strong> ${type}`
                            );

                            // Show success message and option to view status
                            const statusLink = '{{ route('scraping.status') }}';
                            updateScrapingStatus(
                                `Job ${type} berhasil dimulai! <a href="${statusLink}" class="btn btn-sm btn-primary ms-2"><i class="bi bi-eye me-1"></i>Lihat Status</a>`,
                                'success'
                            );

                            pollJobProgress(response.job_id, submitBtn, originalText);
                        } else {
                            updateScrapingStatus('Gagal memulai job scraping', 'danger');
                            showProgress(false);
                            submitBtn.html(originalText).prop('disabled', false);
                        }
                    },
                    error: function(xhr) {
                        const response = xhr.responseJSON;
                        updateScrapingStatus('Error: ' + (response ? response.message :
                            'Terjadi kesalahan'), 'danger');
                        showProgress(false);
                        submitBtn.html(originalText).prop('disabled', false);
                    }
                });
            }

            function startBatchScraping(formData, submitBtn, originalText) {
                // For batch scraping, we need to modify the form data
                const modifiedData = formData + '&types[]=nilai&types[]=mahasiswa';

                $.ajax({
                    url: '{{ route('scraping.batch-scraping') }}',
                    method: 'POST',
                    data: modifiedData,
                    success: function(response) {
                        if (response.success && response.queue) {
                            updateScrapingStatus(
                                `Batch scraping dimulai dengan ID: ${response.batch_id}`, 'info');
                            $('#job-details').html(
                                `<strong>Batch ID:</strong> ${response.batch_id}<br><strong>Types:</strong> ${response.types.join(', ')}`
                            );

                            // Show success message and option to view status
                            const statusLink = '{{ route('scraping.status') }}';
                            updateScrapingStatus(
                                `Batch scraping berhasil dimulai! <a href="${statusLink}" class="btn btn-sm btn-primary ms-2"><i class="bi bi-eye me-1"></i>Lihat Status</a>`,
                                'success'
                            );

                            pollBatchProgress(response.batch_id, submitBtn, originalText);
                        } else {
                            updateScrapingStatus('Gagal memulai batch scraping', 'danger');
                            showProgress(false);
                            submitBtn.html(originalText).prop('disabled', false);
                        }
                    },
                    error: function(xhr) {
                        const response = xhr.responseJSON;
                        updateScrapingStatus('Error: ' + (response ? response.message :
                            'Terjadi kesalahan'), 'danger');
                        showProgress(false);
                        submitBtn.html(originalText).prop('disabled', false);
                    }
                });
            }

            function pollJobProgress(jobId, submitBtn, originalText) {
                const pollInterval = setInterval(function() {
                    $.ajax({
                        url: '{{ route('scraping.job-progress') }}',
                        method: 'GET',
                        data: {
                            job_id: jobId
                        },
                        success: function(response) {
                            updateProgressBar(response.progress, response.message);

                            if (response.status === 'completed') {
                                clearInterval(pollInterval);
                                updateScrapingStatus(
                                    `Scraping selesai! ${response.processed || 0} dari ${response.total || 0} item diproses`,
                                    'success'
                                );
                                showProgress(false);
                                submitBtn.html(originalText).prop('disabled', false);
                            } else if (response.status === 'failed') {
                                clearInterval(pollInterval);
                                updateScrapingStatus('Scraping gagal: ' + (response.error ||
                                        response.message),
                                    'danger');
                                showProgress(false);
                                submitBtn.html(originalText).prop('disabled', false);
                            } else if (response.status === 'not_found') {
                                clearInterval(pollInterval);
                                updateScrapingStatus(
                                    'Job tidak ditemukan atau sudah selesai. Silakan periksa Status Scraping untuk detail lebih lanjut.',
                                    'warning'
                                );
                                showProgress(false);
                                submitBtn.html(originalText).prop('disabled', false);
                            } else if (response.status === 'queued') {
                                // Update progress for queued jobs
                                updateProgressBar(0,
                                    `Job dalam antrian${response.queue_position ? ` (posisi: ${response.queue_position})` : ''}...`
                                );
                            }
                        },
                        error: function(xhr) {
                            // Handle 404 and other errors
                            if (xhr.status === 404) {
                                const response = xhr.responseJSON;
                                if (response && response.status === 'not_found') {
                                    clearInterval(pollInterval);
                                    updateScrapingStatus(
                                        'Job tidak ditemukan atau sudah selesai.',
                                        'warning'
                                    );
                                    showProgress(false);
                                    submitBtn.html(originalText).prop('disabled', false);
                                }
                            }
                            // For other errors, continue polling (job might not be started yet)
                        }
                    });
                }, 2000); // Poll every 2 seconds
            }

            function pollBatchProgress(batchId, submitBtn, originalText) {
                const pollInterval = setInterval(function() {
                    $.ajax({
                        url: '{{ route('scraping.batch-progress') }}',
                        method: 'GET',
                        data: {
                            batch_id: batchId
                        },
                        success: function(response) {
                            updateProgressBar(response.batch.progress, response.batch.message);

                            // Update job details with individual job progress
                            let jobDetailsHtml = `<strong>Batch ID:</strong> ${batchId}<br>`;
                            if (response.jobs) {
                                jobDetailsHtml += '<strong>Jobs:</strong><br>';
                                Object.keys(response.jobs).forEach(function(jobId) {
                                    const job = response.jobs[jobId];
                                    jobDetailsHtml +=
                                        `&nbsp;&nbsp;• ${job.type}: ${Math.round(job.progress)}% - ${job.message}<br>`;
                                });
                            }
                            $('#job-details').html(jobDetailsHtml);

                            if (response.batch.status === 'completed') {
                                clearInterval(pollInterval);
                                updateScrapingStatus('Batch scraping selesai!', 'success');
                                showProgress(false);
                                submitBtn.html(originalText).prop('disabled', false);
                            } else if (response.batch.status === 'failed') {
                                clearInterval(pollInterval);
                                updateScrapingStatus('Batch scraping gagal: ' + response.batch
                                    .error, 'danger');
                                showProgress(false);
                                submitBtn.html(originalText).prop('disabled', false);
                            }
                        },
                        error: function() {
                            // Batch might not be started yet, continue polling
                        }
                    });
                }, 3000); // Poll every 3 seconds for batch
            }

            function showProgress(show) {
                if (show) {
                    $('#progress-container').show();
                    $('#scraping-status').hide();
                } else {
                    $('#progress-container').hide();
                    $('#scraping-status').show();
                }
            }

            function updateProgressBar(percentage, message) {
                const roundedPercentage = Math.round(percentage);
                $('#progress-bar').css('width', percentage + '%').attr('aria-valuenow', percentage);
                $('#progress-text').text(roundedPercentage + '%');
                $('#progress-message').text(message || 'Processing...');
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

                // Refresh quick status when status changes
                loadQuickStatus();
            }
        });
    </script>
@endpush
