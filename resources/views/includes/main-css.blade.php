<!-- Dropezone CSS -->
<link rel="stylesheet" href="{{ asset('css/dropzone.css') }}">
<!-- CoreUI CSS -->
@vite('resources/sass/app.scss')
<link href="https://cdn.datatables.net/v/bs4/jszip-3.10.1/dt-1.13.5/b-2.4.1/b-html5-2.4.1/b-print-2.4.1/sl-1.7.0/datatables.min.css" rel="stylesheet">
<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css">

@yield('third_party_stylesheets')

@stack('page_css')

@livewireStyles

<style>
    div.dataTables_wrapper div.dataTables_length select {
        width: 65px;
        display: inline-block;
    }
    .select2-container--default .select2-selection--single {
        background-color: #fff;
        border: 1px solid #D8DBE0;
        border-radius: 4px;
    }
    .select2-container--default .select2-selection--multiple {
        background-color: #fff;
        border: 1px solid #D8DBE0;
        border-radius: 4px;
    }
    .select2-container .select2-selection--multiple {
        height: 35px;
    }
    .select2-container .select2-selection--single {
        height: 35px;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 33px;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow b {
        margin-top: 2px;
    }
    
    /* Fix sidebar active state issues */
    .c-sidebar-nav-item:not(.c-active) .c-sidebar-nav-link {
        color: rgba(255, 255, 255, 0.7) !important;
        background: transparent !important;
    }
    
    .c-sidebar-nav-item.c-active .c-sidebar-nav-link {
        color: #fff !important;
        background: rgba(255, 255, 255, 0.1) !important;
    }
    
    /* Ensure sidebar is always clickable */
    .c-sidebar,
    .c-sidebar-nav,
    .c-sidebar-nav-item,
    .c-sidebar-nav-link,
    .c-sidebar-nav-dropdown-toggle,
    .c-sidebar-nav-dropdown-items {
        pointer-events: auto !important;
    }
    
    /* Prevent print button double-click visual issues */
    .buttons-print {
        pointer-events: auto !important;
    }
    
    .buttons-print:disabled {
        pointer-events: none !important;
        opacity: 0.6 !important;
    }
    
    /* Print styles - sembunyikan sidebar dan elemen yang tidak perlu */
    @media print {
        .c-sidebar,
        .c-header,
        .c-subheader,
        .c-footer,
        .btn,
        .dataTables_length,
        .dataTables_filter,
        .dataTables_info,
        .dataTables_paginate,
        .dt-buttons {
            display: none !important;
        }
        .c-body {
            margin: 0 !important;
            padding: 0 !important;
        }
        .c-main {
            padding: 0 !important;
        }
        .card {
            border: none !important;
            box-shadow: none !important;
        }
        table {
            width: 100% !important;
        }
        
        /* Ensure print preview only shows once */
        @page {
            margin: 0.5in;
        }
    }
</style>
