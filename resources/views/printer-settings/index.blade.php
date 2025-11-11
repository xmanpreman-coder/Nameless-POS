@extends('layouts.app')

@section('title', 'Printer Settings')

@section('breadcrumb')
    <ol class="breadcrumb border-0 m-0">
        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
        <li class="breadcrumb-item active">Printer Settings</li>
    </ol>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                @include('utils.alerts')
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="bi bi-printer"></i> Printer Configuration
                        </h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('printer-settings.update') }}">
                            @csrf
                            @method('PATCH')
                            
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="receipt_paper_size">Receipt Paper Size <span class="text-danger">*</span></label>
                                        <select name="receipt_paper_size" id="receipt_paper_size" class="form-control @error('receipt_paper_size') is-invalid @enderror" required>
                                            <option value="58mm" {{ old('receipt_paper_size', $printerSettings->receipt_paper_size) == '58mm' ? 'selected' : '' }}>58mm (Small Thermal)</option>
                                            <option value="80mm" {{ old('receipt_paper_size', $printerSettings->receipt_paper_size) == '80mm' ? 'selected' : '' }}>80mm (Standard Thermal)</option>
                                            <option value="letter" {{ old('receipt_paper_size', $printerSettings->receipt_paper_size) == 'letter' ? 'selected' : '' }}>Letter (8.5" x 11")</option>
                                            <option value="a4" {{ old('receipt_paper_size', $printerSettings->receipt_paper_size) == 'a4' ? 'selected' : '' }}>A4 (210mm x 297mm)</option>
                                        </select>
                                        @error('receipt_paper_size')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">Default paper size for all receipts</small>
                                    </div>
                                </div>
                                
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label for="default_receipt_printer">Default Receipt Printer</label>
                                        <input type="text" name="default_receipt_printer" id="default_receipt_printer" 
                                               class="form-control @error('default_receipt_printer') is-invalid @enderror" 
                                               value="{{ old('default_receipt_printer', $printerSettings->default_receipt_printer) }}"
                                               placeholder="e.g., EPSON TM-T20, POS-80, Star TSP143">
                                        @error('default_receipt_printer')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">Leave empty for system default printer</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label for="receipt_copies">Number of Copies</label>
                                        <select name="receipt_copies" id="receipt_copies" class="form-control @error('receipt_copies') is-invalid @enderror">
                                            <option value="1" {{ old('receipt_copies', $printerSettings->receipt_copies) == 1 ? 'selected' : '' }}>1 Copy</option>
                                            <option value="2" {{ old('receipt_copies', $printerSettings->receipt_copies) == 2 ? 'selected' : '' }}>2 Copies</option>
                                            <option value="3" {{ old('receipt_copies', $printerSettings->receipt_copies) == 3 ? 'selected' : '' }}>3 Copies</option>
                                        </select>
                                        @error('receipt_copies')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <div class="form-check mt-4">
                                            <input type="checkbox" name="auto_print_receipt" id="auto_print_receipt" 
                                                   class="form-check-input" value="1" 
                                                   {{ old('auto_print_receipt', $printerSettings->auto_print_receipt) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="auto_print_receipt">
                                                Auto Print After Sale
                                            </label>
                                        </div>
                                        <small class="form-text text-muted">Automatically print receipt when sale is completed</small>
                                    </div>
                                </div>
                                
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <div class="form-check mt-4">
                                            <input type="checkbox" name="print_customer_copy" id="print_customer_copy" 
                                                   class="form-check-input" value="1" 
                                                   {{ old('print_customer_copy', $printerSettings->print_customer_copy) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="print_customer_copy">
                                                Print Customer Copy
                                            </label>
                                        </div>
                                        <small class="form-text text-muted">Print additional copy for customer</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label for="thermal_printer_commands">Thermal Printer Commands (ESC/POS)</label>
                                        <textarea name="thermal_printer_commands" id="thermal_printer_commands" rows="4" 
                                                  class="form-control @error('thermal_printer_commands') is-invalid @enderror"
                                                  placeholder="Custom ESC/POS commands for thermal printers...">{{ old('thermal_printer_commands', $printerSettings->thermal_printer_commands) }}</textarea>
                                        @error('thermal_printer_commands')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">Advanced: Custom commands for thermal printer control (optional)</small>
                                    </div>
                                </div>
                            </div>

                            <!-- User Information -->
                            <div class="alert alert-info">
                                <h6><i class="bi bi-info-circle"></i> Individual User Preferences</h6>
                                <p class="mb-0">Each user can override these settings from their profile menu. These are system-wide defaults.</p>
                            </div>

                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check"></i> Update Printer Settings
                                </button>
                                <a href="{{ route('home') }}" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left"></i> Back to Dashboard
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection