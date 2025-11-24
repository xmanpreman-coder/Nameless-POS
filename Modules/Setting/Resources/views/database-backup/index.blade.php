@extends('layouts.app')

@section('title', 'Database Backup & Restore')

@section('breadcrumb')
    <ol class="breadcrumb border-0 m-0">
        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('settings.index') }}">Settings</a></li>
        <li class="breadcrumb-item active">Database Backup & Restore</li>
    </ol>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <!-- Success/Error Messages -->
            <div class="col-lg-12">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle"></i> <strong>Success!</strong>
                        <div class="mt-2">{{ session('success') }}</div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle"></i> <strong>Error!</strong>
                        <div class="mt-2">{{ session('error') }}</div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
            </div>

            <!-- Download Backup -->
            <div class="col-lg-12 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-cloud-download"></i> Download Database Backup
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">
                            Create a backup of your entire database with current date/time timestamp.
                        </p>
                        <form action="{{ route('database.backup.download') }}" method="GET">
                            @csrf
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="bi bi-download"></i> Download Backup Now
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Restore Options Tab -->
            <div class="col-lg-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-arrow-repeat"></i> Restore Database Operations
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <!-- Nav Tabs -->
                        <ul class="nav nav-tabs nav-fill border-bottom" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="full-restore-tab" data-bs-toggle="tab" data-bs-target="#full-restore" type="button" role="tab">
                                    <i class="bi bi-arrow-repeat"></i> Full Restore
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="merge-tab" data-bs-toggle="tab" data-bs-target="#merge" type="button" role="tab">
                                    <i class="bi bi-share"></i> Merge Data
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="selective-tab" data-bs-toggle="tab" data-bs-target="#selective" type="button" role="tab">
                                    <i class="bi bi-hand-index"></i> Selective Restore
                                </button>
                            </li>
                        </ul>

                        <!-- Tab Content -->
                        <div class="tab-content p-4">
                            <!-- Tab 1: Full Restore -->
                            <div class="tab-pane fade show active" id="full-restore" role="tabpanel">
                                <div class="alert alert-danger mb-4">
                                    <i class="bi bi-exclamation-triangle-fill"></i> <strong>WARNING: Destructive Operation</strong>
                                    <ul class="mb-0 mt-2">
                                        <li>All current data will be replaced with backup data</li>
                                        <li>Current database will be backed up automatically (.bak file)</li>
                                        <li>Cannot be undone - rollback requires manual file restoration</li>
                                        <li>Recommended: Only use when database is corrupted or data loss recovery</li>
                                    </ul>
                                </div>

                                <h6 class="mb-3"><strong>How it works:</strong></h6>
                                <ol class="mb-4">
                                    <li>Your current database is backed up as <code>.restore_TIMESTAMP.bak</code></li>
                                    <li>Backup file completely replaces current database.sqlite</li>
                                    <li>All data and structure from backup are restored</li>
                                    <li>Any data added after backup download will be lost</li>
                                </ol>

                                <form action="{{ route('database.backup.restore') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <div class="form-group mb-3">
                                        <label for="backup_file_full" class="form-label"><strong>Select Backup File</strong></label>
                                        <input type="file" class="form-control form-control-lg @error('backup_file') is-invalid @enderror" 
                                               name="backup_file" id="backup_file_full" accept=".sqlite" required>
                                        <small class="form-text text-muted">Max 100MB | .sqlite format only</small>
                                        @error('backup_file')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <button type="submit" class="btn btn-danger btn-lg"
                                            onclick="return confirm('⚠️ FINAL WARNING: All current data will be replaced!\n\nAre you absolutely sure you want to continue?')">
                                        <i class="bi bi-arrow-repeat"></i> Full Restore Database
                                    </button>
                                </form>
                            </div>

                            <!-- Tab 2: Merge Data -->
                            <div class="tab-pane fade" id="merge" role="tabpanel">
                                <div class="alert alert-info mb-4">
                                    <i class="bi bi-info-circle-fill"></i> <strong>Non-Destructive Operation</strong>
                                    <ul class="mb-0 mt-2">
                                        <li>Current data is preserved and not deleted</li>
                                        <li>New data from backup is added to existing data</li>
                                        <li>If data with same ID exists, current data is kept (backup data skipped)</li>
                                        <li>Safe for combining data from multiple databases</li>
                                        <li>Recommended: Merging data from different locations/periods</li>
                                    </ul>
                                </div>

                                <h6 class="mb-3"><strong>How it works:</strong></h6>
                                <ol class="mb-4">
                                    <li>All tables from backup are compared with current database</li>
                                    <li>New records from backup are added to current database</li>
                                    <li>Existing records (by ID) are not modified</li>
                                    <li>Result: Union of data from both databases</li>
                                </ol>

                                <form action="{{ route('database.backup.merge') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <div class="form-group mb-3">
                                        <label for="backup_file_merge" class="form-label"><strong>Select Backup File</strong></label>
                                        <input type="file" class="form-control form-control-lg @error('backup_file') is-invalid @enderror" 
                                               name="backup_file" id="backup_file_merge" accept=".sqlite" required>
                                        <small class="form-text text-muted">Max 100MB | .sqlite format only</small>
                                        @error('backup_file')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <button type="submit" class="btn btn-info btn-lg"
                                            onclick="return confirm('Merge data from backup?\n\nCurrent data will be preserved. New data from backup will be added.')">
                                        <i class="bi bi-share"></i> Merge Database
                                    </button>
                                </form>
                            </div>

                            <!-- Tab 3: Selective Restore -->
                            <div class="tab-pane fade" id="selective" role="tabpanel">
                                <div class="alert alert-warning mb-4">
                                    <i class="bi bi-exclamation-triangle-fill"></i> <strong>Caution: Partial Replacement</strong>
                                    <ul class="mb-0 mt-2">
                                        <li>Only selected tables are restored (replaced)</li>
                                        <li>Other tables in current database remain unchanged</li>
                                        <li>Selected tables: ALL data is replaced (destructive)</li>
                                        <li>Recommended: Restoring specific corrupted tables only</li>
                                    </ul>
                                </div>

                                <h6 class="mb-3"><strong>How it works:</strong></h6>
                                <ol class="mb-4">
                                    <li>Analyze backup file to view available tables</li>
                                    <li>Select which tables you want to restore</li>
                                    <li>Only selected tables are truncated and replaced</li>
                                    <li>Unselected tables remain untouched</li>
                                </ol>

                                <form id="selective-restore-form" action="{{ route('database.backup.selectiveRestore') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <div class="form-group mb-3">
                                        <label for="backup_file_selective" class="form-label"><strong>Select Backup File</strong></label>
                                        <input type="file" class="form-control form-control-lg @error('backup_file') is-invalid @enderror" 
                                               name="backup_file" id="backup_file_selective" accept=".sqlite" required>
                                        <small class="form-text text-muted">Max 100MB | .sqlite format only</small>
                                        @error('backup_file')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group mb-3">
                                        <button type="button" class="btn btn-secondary" id="analyze-btn">
                                            <i class="bi bi-search"></i> Analyze Backup
                                        </button>
                                        <small class="form-text text-muted d-block mt-2">Click to see available tables in backup file</small>
                                    </div>

                                    <div id="tables-list" class="mb-3" style="display:none;">
                                        <label class="form-label"><strong>Select Tables to Restore:</strong></label>
                                        <div id="tables-checkboxes" class="list-group"></div>
                                    </div>

                                    <button type="submit" class="btn btn-warning btn-lg" id="selective-restore-btn" style="display:none;"
                                            onclick="return confirm('Restore selected tables?\n\nSelected tables will be completely replaced with backup data.')">
                                        <i class="bi bi-hand-index"></i> Restore Selected Tables
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Important Notes -->
            <div class="col-lg-12 mt-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-shield-check"></i> Important Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-6">
                                <h6 class="mb-3">
                                    <i class="bi bi-check-circle text-success"></i> Best Practices
                                </h6>
                                <ul class="list-unstyled">
                                    <li><i class="bi bi-arrow-right"></i> Create backups regularly (daily/weekly)</li>
                                    <li><i class="bi bi-arrow-right"></i> Store backups in multiple locations</li>
                                    <li><i class="bi bi-arrow-right"></i> Label backups with dates clearly</li>
                                    <li><i class="bi bi-arrow-right"></i> Keep at least 3 recent backups</li>
                                    <li><i class="bi bi-arrow-right"></i> Test restore process periodically</li>
                                </ul>
                            </div>
                            <div class="col-lg-6">
                                <h6 class="mb-3">
                                    <i class="bi bi-exclamation-circle text-danger"></i> Recovery Notes
                                </h6>
                                <ul class="list-unstyled">
                                    <li><i class="bi bi-arrow-right"></i> Full Restore backup files: `database.sqlite.restore_TIMESTAMP.bak`</li>
                                    <li><i class="bi bi-arrow-right"></i> Manual rollback: Copy .bak file back to database.sqlite</li>
                                    <li><i class="bi bi-arrow-right"></i> Merge is always reversible (no data deleted)</li>
                                    <li><i class="bi bi-arrow-right"></i> Selective restore: Only affects chosen tables</li>
                                    <li><i class="bi bi-arrow-right"></i> Check logs for all operations</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.getElementById('analyze-btn').addEventListener('click', function() {
            const fileInput = document.getElementById('backup_file_selective');
            if (!fileInput.files.length) {
                alert('Please select a backup file first');
                return;
            }

            const formData = new FormData();
            formData.append('backup_file', fileInput.files[0]);
            formData.append('_token', document.querySelector('input[name="_token"]').value);

            fetch('{{ route("database.backup.analyze") }}', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    displayTables(data.data);
                } else {
                    alert('Error: ' + data.error);
                }
            })
            .catch(err => alert('Error analyzing backup: ' + err));
        });

        function displayTables(data) {
            const container = document.getElementById('tables-checkboxes');
            container.innerHTML = '';

            if (data.tables_in_both && data.tables_in_both.length > 0) {
                data.tables_in_both.forEach((table, index) => {
                    const rowCount = data.row_counts[table];
                    const html = `
                        <div class="list-group-item">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input table-checkbox" 
                                       id="table_${index}" name="tables[]" value="${table}">
                                <label class="custom-control-label" for="table_${index}">
                                    <strong>${table}</strong>
                                    <br>
                                    <small class="text-muted">
                                        Current: ${rowCount.current} rows | 
                                        Backup: ${rowCount.backup} rows
                                    </small>
                                </label>
                            </div>
                        </div>
                    `;
                    container.insertAdjacentHTML('beforeend', html);
                });

                document.getElementById('tables-list').style.display = 'block';
                document.getElementById('selective-restore-btn').style.display = 'block';
            }
        }
    </script>
    @endpush
@endsection
