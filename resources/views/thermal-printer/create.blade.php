@extends('layouts.app')

@section('title', 'Add New Thermal Printer')

@section('breadcrumb')
    <ol class="breadcrumb border-0 m-0">
        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('thermal-printer.index') }}">Thermal Printers</a></li>
        <li class="breadcrumb-item active">Add New</li>
    </ol>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <form action="{{ route('thermal-printer.store') }}" method="POST">
                    @csrf
                    <div class="row">
                        <!-- Main Settings -->
                        <div class="col-lg-8">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title mb-0">
                                        <i class="bi bi-printer me-2"></i>
                                        Printer Configuration
                                    </h4>
                                </div>
                                <div class="card-body">
                                    <!-- Quick Setup -->
                                    <div class="row mb-4">
                                        <div class="col-12">
                                            <h6 class="fw-bold mb-3">
                                                <i class="bi bi-lightning me-1"></i>
                                                Quick Setup (Optional)
                                            </h6>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label for="preset_select" class="form-label">Choose Preset</label>
                                                    <select class="form-select" id="preset_select" onchange="loadPreset()">
                                                        <option value="">-- Custom Configuration --</option>
                                                        <option value="eppos_ep220ii">Eppos EP220II</option>
                                                        <option value="xprinter_xp80c">Xprinter XP-80C</option>
                                                        <option value="epson_tm_t20">Epson TM-T20</option>
                                                        <option value="star_tsp143">Star TSP143</option>
                                                        <option value="generic_80mm">Generic 80mm Thermal</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6 d-flex align-items-end">
                                                    <button type="button" class="btn btn-outline-info" onclick="loadPreset()">
                                                        <i class="bi bi-arrow-down-circle me-1"></i>
                                                        Load Preset
                                                    </button>
                                                </div>
                                            </div>
                                            <hr class="my-4">
                                        </div>
                                    </div>

                                    <!-- Basic Information -->
                                    <div class="row mb-4">
                                        <div class="col-12">
                                            <h6 class="fw-bold mb-3">Basic Information</h6>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="name" class="form-label">Printer Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                                   id="name" name="name" value="{{ old('name') }}" required>
                                            @error('name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-3">
                                            <label for="brand" class="form-label">Brand</label>
                                            <input type="text" class="form-control @error('brand') is-invalid @enderror" 
                                                   id="brand" name="brand" value="{{ old('brand') }}" 
                                                   placeholder="e.g. Eppos, Xprinter">
                                            @error('brand')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-3">
                                            <label for="model" class="form-label">Model</label>
                                            <input type="text" class="form-control @error('model') is-invalid @enderror" 
                                                   id="model" name="model" value="{{ old('model') }}" 
                                                   placeholder="e.g. EP220II, XP-80C">
                                            @error('model')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Connection Settings -->
                                    <div class="row mb-4">
                                        <div class="col-12">
                                            <h6 class="fw-bold mb-3">Connection Settings</h6>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="connection_type" class="form-label">Connection Type <span class="text-danger">*</span></label>
                                            <select class="form-select @error('connection_type') is-invalid @enderror" 
                                                    id="connection_type" name="connection_type" onchange="toggleConnectionFields()" required>
                                                <option value="usb" {{ old('connection_type') == 'usb' ? 'selected' : '' }}>USB</option>
                                                <option value="ethernet" {{ old('connection_type') == 'ethernet' ? 'selected' : '' }}>Ethernet</option>
                                                <option value="wifi" {{ old('connection_type') == 'wifi' ? 'selected' : '' }}>WiFi</option>
                                                <option value="bluetooth" {{ old('connection_type') == 'bluetooth' ? 'selected' : '' }}>Bluetooth</option>
                                                <option value="serial" {{ old('connection_type') == 'serial' ? 'selected' : '' }}>Serial</option>
                                            </select>
                                            @error('connection_type')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        
                                        <!-- Network Settings -->
                                        <div class="col-md-4 network-fields" style="display: none;">
                                            <label for="ip_address" class="form-label">IP Address</label>
                                            <input type="text" class="form-control @error('ip_address') is-invalid @enderror" 
                                                   id="ip_address" name="ip_address" value="{{ old('ip_address') }}" 
                                                   placeholder="192.168.1.100">
                                            @error('ip_address')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-4 network-fields" style="display: none;">
                                            <label for="port" class="form-label">Port</label>
                                            <input type="number" class="form-control @error('port') is-invalid @enderror" 
                                                   id="port" name="port" value="{{ old('port', 9100) }}" 
                                                   min="1" max="65535">
                                            @error('port')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Serial Settings -->
                                        <div class="col-md-4 serial-fields" style="display: none;">
                                            <label for="serial_port" class="form-label">Serial Port</label>
                                            <input type="text" class="form-control @error('serial_port') is-invalid @enderror" 
                                                   id="serial_port" name="serial_port" value="{{ old('serial_port') }}" 
                                                   placeholder="COM1 or /dev/ttyUSB0">
                                            @error('serial_port')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-4 serial-fields bluetooth-fields" style="display: none;">
                                            <label for="baud_rate" class="form-label">Baud Rate</label>
                                            <select class="form-select @error('baud_rate') is-invalid @enderror" 
                                                    id="baud_rate" name="baud_rate">
                                                <option value="9600" {{ old('baud_rate') == '9600' ? 'selected' : '' }}>9600</option>
                                                <option value="19200" {{ old('baud_rate') == '19200' ? 'selected' : '' }}>19200</option>
                                                <option value="38400" {{ old('baud_rate') == '38400' ? 'selected' : '' }}>38400</option>
                                                <option value="57600" {{ old('baud_rate') == '57600' ? 'selected' : '' }}>57600</option>
                                                <option value="115200" {{ old('baud_rate', '115200') == '115200' ? 'selected' : '' }}>115200</option>
                                            </select>
                                            @error('baud_rate')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Bluetooth Settings -->
                                        <div class="col-md-8 bluetooth-fields" style="display: none;">
                                            <label for="bluetooth_address" class="form-label">Bluetooth Address</label>
                                            <input type="text" class="form-control @error('bluetooth_address') is-invalid @enderror" 
                                                   id="bluetooth_address" name="bluetooth_address" value="{{ old('bluetooth_address') }}" 
                                                   placeholder="00:11:22:33:44:55">
                                            @error('bluetooth_address')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Paper Settings -->
                                    <div class="row mb-4">
                                        <div class="col-12">
                                            <h6 class="fw-bold mb-3">Paper Settings</h6>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="paper_width" class="form-label">Paper Width <span class="text-danger">*</span></label>
                                            <select class="form-select @error('paper_width') is-invalid @enderror" 
                                                    id="paper_width" name="paper_width" required>
                                                <option value="58" {{ old('paper_width') == '58' ? 'selected' : '' }}>58mm</option>
                                                <option value="80" {{ old('paper_width', '80') == '80' ? 'selected' : '' }}>80mm</option>
                                                <option value="112" {{ old('paper_width') == '112' ? 'selected' : '' }}>112mm</option>
                                            </select>
                                            @error('paper_width')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-4">
                                            <label for="paper_type" class="form-label">Paper Type</label>
                                            <select class="form-select @error('paper_type') is-invalid @enderror" 
                                                    id="paper_type" name="paper_type">
                                                <option value="thermal" {{ old('paper_type', 'thermal') == 'thermal' ? 'selected' : '' }}>Thermal</option>
                                                <option value="impact" {{ old('paper_type') == 'impact' ? 'selected' : '' }}>Impact</option>
                                            </select>
                                            @error('paper_type')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-4">
                                            <label for="paper_length" class="form-label">Paper Length (mm)</label>
                                            <input type="number" class="form-control @error('paper_length') is-invalid @enderror" 
                                                   id="paper_length" name="paper_length" value="{{ old('paper_length', 0) }}" 
                                                   min="0">
                                            <div class="form-text">0 = Continuous paper</div>
                                            @error('paper_length')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Print Settings -->
                                    <div class="row mb-4">
                                        <div class="col-12">
                                            <h6 class="fw-bold mb-3">Print Settings</h6>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="print_speed" class="form-label">Print Speed <span class="text-danger">*</span></label>
                                            <select class="form-select @error('print_speed') is-invalid @enderror" 
                                                    id="print_speed" name="print_speed" required>
                                                <option value="1" {{ old('print_speed') == '1' ? 'selected' : '' }}>Level 1 (Fastest)</option>
                                                <option value="2" {{ old('print_speed', '2') == '2' ? 'selected' : '' }}>Level 2</option>
                                                <option value="3" {{ old('print_speed') == '3' ? 'selected' : '' }}>Level 3</option>
                                                <option value="4" {{ old('print_speed') == '4' ? 'selected' : '' }}>Level 4</option>
                                                <option value="5" {{ old('print_speed') == '5' ? 'selected' : '' }}>Level 5 (Best Quality)</option>
                                            </select>
                                            @error('print_speed')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-3">
                                            <label for="print_density" class="form-label">Print Density <span class="text-danger">*</span></label>
                                            <select class="form-select @error('print_density') is-invalid @enderror" 
                                                    id="print_density" name="print_density" required>
                                                <option value="1" {{ old('print_density') == '1' ? 'selected' : '' }}>Level 1 (Lightest)</option>
                                                <option value="2" {{ old('print_density') == '2' ? 'selected' : '' }}>Level 2</option>
                                                <option value="3" {{ old('print_density', '3') == '3' ? 'selected' : '' }}>Level 3 (Normal)</option>
                                                <option value="4" {{ old('print_density') == '4' ? 'selected' : '' }}>Level 4</option>
                                                <option value="5" {{ old('print_density') == '5' ? 'selected' : '' }}>Level 5 (Darkest)</option>
                                            </select>
                                            @error('print_density')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-3">
                                            <label for="character_set" class="form-label">Character Set</label>
                                            <select class="form-select @error('character_set') is-invalid @enderror" 
                                                    id="character_set" name="character_set">
                                                <option value="PC437" {{ old('character_set', 'PC437') == 'PC437' ? 'selected' : '' }}>PC437 (USA)</option>
                                                <option value="PC850" {{ old('character_set') == 'PC850' ? 'selected' : '' }}>PC850 (Latin-1)</option>
                                                <option value="PC852" {{ old('character_set') == 'PC852' ? 'selected' : '' }}>PC852 (Latin-2)</option>
                                                <option value="PC858" {{ old('character_set') == 'PC858' ? 'selected' : '' }}>PC858 (Latin-1 + Euro)</option>
                                                <option value="PC866" {{ old('character_set') == 'PC866' ? 'selected' : '' }}>PC866 (Cyrillic)</option>
                                            </select>
                                            @error('character_set')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-3">
                                            <label for="font_size" class="form-label">Font Size</label>
                                            <select class="form-select @error('font_size') is-invalid @enderror" 
                                                    id="font_size" name="font_size">
                                                <option value="small" {{ old('font_size') == 'small' ? 'selected' : '' }}>Small (Font B)</option>
                                                <option value="normal" {{ old('font_size', 'normal') == 'normal' ? 'selected' : '' }}>Normal (Font A)</option>
                                                <option value="large" {{ old('font_size') == 'large' ? 'selected' : '' }}>Large</option>
                                            </select>
                                            @error('font_size')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Side Panel -->
                        <div class="col-lg-4">
                            <!-- Features -->
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Features</h5>
                                </div>
                                <div class="card-body">
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="auto_cut" name="auto_cut" 
                                               value="1" {{ old('auto_cut', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="auto_cut">
                                            <strong>Auto Cut Paper</strong>
                                            <br><small class="text-muted">Automatically cut paper after printing</small>
                                        </label>
                                    </div>
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="buzzer_enabled" name="buzzer_enabled" 
                                               value="1" {{ old('buzzer_enabled') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="buzzer_enabled">
                                            <strong>Enable Buzzer</strong>
                                            <br><small class="text-muted">Sound notification after printing</small>
                                        </label>
                                    </div>
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="print_logo" name="print_logo" 
                                               value="1" {{ old('print_logo') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="print_logo">
                                            <strong>Print Logo</strong>
                                            <br><small class="text-muted">Include company logo in receipts</small>
                                        </label>
                                    </div>
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="print_barcode" name="print_barcode" 
                                               value="1" {{ old('print_barcode', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="print_barcode">
                                            <strong>Print Barcode</strong>
                                            <br><small class="text-muted">Include barcode on receipts</small>
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_default" name="is_default" 
                                               value="1" {{ old('is_default') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_default">
                                            <strong>Set as Default</strong>
                                            <br><small class="text-muted">Use this as default printer</small>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Quick Test -->
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Quick Test</h5>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted small">Save the configuration first, then use these tools to test your printer.</p>
                                    <button type="button" class="btn btn-outline-info btn-sm me-2" disabled>
                                        <i class="bi bi-wifi me-1"></i>
                                        Test Connection
                                    </button>
                                    <button type="button" class="btn btn-outline-success btn-sm" disabled>
                                        <i class="bi bi-printer me-1"></i>
                                        Print Test
                                    </button>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Actions</h5>
                                </div>
                                <div class="card-body">
                                    <button type="submit" class="btn btn-primary w-100 mb-2">
                                        <i class="bi bi-check-circle me-1"></i>
                                        Save Printer Configuration
                                    </button>
                                    <a href="{{ route('thermal-printer.index') }}" class="btn btn-secondary w-100">
                                        <i class="bi bi-arrow-left me-1"></i>
                                        Back to Printers
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('page_scripts')
<script>
    // Presets data
    const presets = @json($presets);

    // Toggle connection fields based on connection type
    function toggleConnectionFields() {
        const connectionType = document.getElementById('connection_type').value;
        
        // Hide all connection-specific fields
        document.querySelectorAll('.network-fields').forEach(el => el.style.display = 'none');
        document.querySelectorAll('.serial-fields').forEach(el => el.style.display = 'none');
        document.querySelectorAll('.bluetooth-fields').forEach(el => el.style.display = 'none');
        
        // Show relevant fields
        if (connectionType === 'ethernet' || connectionType === 'wifi') {
            document.querySelectorAll('.network-fields').forEach(el => el.style.display = 'block');
        } else if (connectionType === 'serial') {
            document.querySelectorAll('.serial-fields').forEach(el => el.style.display = 'block');
        } else if (connectionType === 'bluetooth') {
            document.querySelectorAll('.bluetooth-fields').forEach(el => el.style.display = 'block');
        }
    }

    // Load preset configuration
    function loadPreset() {
        const presetKey = document.getElementById('preset_select').value;
        
        if (!presetKey || !presets[presetKey]) {
            return;
        }
        
        const preset = presets[presetKey];
        
        // Fill form fields
        if (preset.name) document.getElementById('name').value = preset.name;
        if (preset.brand) document.getElementById('brand').value = preset.brand;
        if (preset.model) document.getElementById('model').value = preset.model;
        if (preset.paper_width) document.getElementById('paper_width').value = preset.paper_width;
        
        // Set checkboxes based on capabilities
        if (preset.capabilities) {
            document.getElementById('auto_cut').checked = preset.capabilities.auto_cut || false;
            document.getElementById('buzzer_enabled').checked = false; // Usually disabled by default
            document.getElementById('print_barcode').checked = preset.capabilities.barcode ? true : false;
        }
        
        // Show success message
        showMessage('info', 'Preset loaded successfully! Review and adjust settings as needed.');
    }

    // Show message
    function showMessage(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : type === 'error' ? 'alert-danger' : 'alert-info';
        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        // Insert at the top of the form
        const form = document.querySelector('form');
        form.insertAdjacentHTML('afterbegin', alertHtml);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            const alert = document.querySelector('.alert');
            if (alert) {
                alert.remove();
            }
        }, 5000);
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        toggleConnectionFields();
    });
</script>
@endpush