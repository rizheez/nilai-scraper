@extends('layouts.app')

@section('title', 'Status Scraping - Nilai Scraper')
@section('page-title', 'Status Scraping')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4>Monitor Status Scraping</h4>
                <div>
                    <button class="btn btn-primary" id="refresh-status">
                        <i class="bi bi-arrow-clockwise me-2"></i>
                        Refresh Status
                    </button>
                    <a href="{{ route('scraping.index') }}" class="btn btn-success">
                        <i class="bi bi-play-circle me-2"></i>
                        Mulai Scraping Baru
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title mb-0">Total Aktif</h5>
                            <h2 class="mb-0" id="total-active">0</h2>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-gear fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title mb-0">Jobs Individual</h5>
                            <h2 class="mb-0" id="individual-jobs">0</h2>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-cpu fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title mb-0">Batch Jobs</h5>
                            <h2 class="mb-0" id="batch-jobs">0</h2>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-collection fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Jobs Section -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-list-task me-2"></i>
                        Jobs Aktif Saat Ini
                    </h5>
                </div>
                <div class="card-body">
                    <div id="active-jobs-container">
                        <div class="text-center text-muted py-4" id="no-active-jobs">
                            <i class="bi bi-check-circle fs-1 d-block mb-2"></i>
                            Tidak ada scraping yang sedang berjalan
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Batch Jobs Section -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-collection me-2"></i>
                        Batch Jobs Aktif
                    </h5>
                </div>
                <div class="card-body">
                    <div id="batch-jobs-container">
                        <div class="text-center text-muted py-4" id="no-batch-jobs">
                            <i class="bi bi-check-circle fs-1 d-block mb-2"></i>
                            Tidak ada batch scraping yang sedang berjalan
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity (placeholder for future enhancement) -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-clock-history me-2"></i>
                        Aktivitas Terakhir
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-info-circle fs-1 d-block mb-2"></i>
                        Fitur log aktivitas akan segera tersedia
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Initial load
            loadActiveJobs();

            // Auto-refresh every 3 seconds
            setInterval(loadActiveJobs, 3000);

            // Manual refresh button
            $('#refresh-status').on('click', function() {
                const btn = $(this);
                const originalText = btn.html();
                btn.html('<i class="bi bi-hourglass-split me-2"></i>Refreshing...').prop('disabled', true);

                $.ajax({
                    url: '{{ route('scraping.active-jobs') }}',
                    method: 'GET',
                    success: function(response) {
                        updateSummaryCards(response);
                        displayActiveJobs(response.jobs);
                        displayBatchJobs(response.batches);
                    },
                    error: function(xhr) {
                        console.error('Failed to load active jobs:', xhr);
                    },
                    complete: function() {
                        btn.html(originalText).prop('disabled', false);
                    }
                });
            });

            function loadActiveJobs() {
                return $.ajax({
                    url: '{{ route('scraping.active-jobs') }}',
                    method: 'GET',
                    success: function(response) {
                        updateSummaryCards(response);
                        displayActiveJobs(response.jobs);
                        displayBatchJobs(response.batches);
                    },
                    error: function(xhr) {
                        console.error('Failed to load active jobs:', xhr);
                    }
                });
            }

            function updateSummaryCards(response) {
                $('#total-active').text(response.total_active);
                $('#individual-jobs').text(response.jobs.length);
                $('#batch-jobs').text(response.batches.length);
            }

            function displayActiveJobs(jobs) {
                const container = $('#active-jobs-container');
                const noJobsMessage = $('#no-active-jobs');

                if (jobs.length === 0) {
                    container.html(noJobsMessage.prop('outerHTML'));
                    return;
                }

                let html = '';
                jobs.forEach(function(job) {
                    const progressPercentage = Math.round(job.progress);
                    const statusBadge = getStatusBadge(job.status);
                    const typeBadge = getTypeBadge(job.type);

                    html += `
                        <div class="card mb-3 border-start border-primary border-3">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <h6 class="card-title mb-1">
                                            Job ID: ${job.id}
                                            ${typeBadge}
                                            ${statusBadge}
                                        </h6>
                                        <p class="card-text text-muted mb-2">${job.message}</p>
                                        <small class="text-muted">
                                            <i class="bi bi-clock me-1"></i>
                                            Dimulai: ${formatDateTime(job.started_at)}
                                        </small>
                                        ${job.processed && job.total ?
                                            `<br><small class="text-info">Progress: ${job.processed}/${job.total} items</small>`
                                            : ''
                                        }
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-end">
                                            <div class="progress mb-2" style="height: 20px;">
                                                <div class="progress-bar progress-bar-striped progress-bar-animated ${getProgressBarClass(job.status)}"
                                                     role="progressbar" style="width: ${progressPercentage}%"
                                                     aria-valuenow="${progressPercentage}" aria-valuemin="0" aria-valuemax="100">
                                                    ${progressPercentage}%
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });

                container.html(html);
            }

            function displayBatchJobs(batches) {
                const container = $('#batch-jobs-container');
                const noBatchMessage = $('#no-batch-jobs');

                if (batches.length === 0) {
                    container.html(noBatchMessage.prop('outerHTML'));
                    return;
                }

                let html = '';
                batches.forEach(function(batch) {
                    const progressPercentage = Math.round(batch.progress);
                    const statusBadge = getStatusBadge(batch.status);

                    html += `
                        <div class="card mb-3 border-start border-warning border-3">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <h6 class="card-title mb-1">
                                            Batch ID: ${batch.id}
                                            ${statusBadge}
                                        </h6>
                                        <p class="card-text text-muted mb-2">${batch.message}</p>
                                        <small class="text-muted">
                                            <i class="bi bi-clock me-1"></i>
                                            Dimulai: ${formatDateTime(batch.started_at)}
                                        </small>
                                        ${batch.types && batch.types.length ?
                                            `<br><small class="text-info">
                                                                <i class="bi bi-tags me-1"></i>
                                                                Types: ${batch.types.join(', ')}
                                                             </small>`
                                            : ''
                                        }
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-end">
                                            <div class="progress mb-2" style="height: 20px;">
                                                <div class="progress-bar progress-bar-striped progress-bar-animated ${getProgressBarClass(batch.status)}"
                                                     role="progressbar" style="width: ${progressPercentage}%"
                                                     aria-valuenow="${progressPercentage}" aria-valuemin="0" aria-valuemax="100">
                                                    ${progressPercentage}%
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });

                container.html(html);
            }

            function getStatusBadge(status) {
                const badges = {
                    'processing': '<span class="badge bg-primary">Processing</span>',
                    'started': '<span class="badge bg-info">Started</span>',
                    'running': '<span class="badge bg-success">Running</span>',
                    'queued': '<span class="badge bg-secondary">Queued</span>',
                    'completed': '<span class="badge bg-success">Completed</span>',
                    'failed': '<span class="badge bg-danger">Failed</span>',
                    'not_found': '<span class="badge bg-warning">Not Found</span>'
                };
                return badges[status] || '<span class="badge bg-secondary">Unknown</span>';
            }

            function getTypeBadge(type) {
                const badges = {
                    'nilai': '<span class="badge bg-info">Nilai</span>',
                    'mahasiswa': '<span class="badge bg-warning">Mahasiswa</span>'
                };
                return badges[type] || '<span class="badge bg-secondary">Unknown</span>';
            }

            function getProgressBarClass(status) {
                const classes = {
                    'processing': 'bg-primary',
                    'started': 'bg-info',
                    'running': 'bg-success',
                    'queued': 'bg-secondary',
                    'completed': 'bg-success',
                    'failed': 'bg-danger',
                    'not_found': 'bg-warning'
                };
                return classes[status] || 'bg-secondary';
            }

            function formatDateTime(dateTime) {
                if (!dateTime) return 'Unknown';
                try {
                    return new Date(dateTime).toLocaleString('id-ID');
                } catch (e) {
                    return dateTime;
                }
            }
        });
    </script>
@endpush
