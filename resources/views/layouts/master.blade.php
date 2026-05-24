<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>POULTRY MANAGEMENT SYSTEM</title>

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('assets/images/favicon.png') }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.png') }}" type="image/x-icon">

    <!-- Google Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Vendor CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/vendors/flag-icon.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/vendors/weather-icons/weather-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/vendors/scrollbar.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/vendors/swiper-bundle.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/vendors/slick.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/vendors/slick-theme.css') }}">

    <!-- Icon Libraries -->
    <link rel="stylesheet" href="{{ asset('assets/css/iconly-icon.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/bulk-style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/themify.css') }}">

    <!-- Font Awesome (FIXED - FULL LIBRARY) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Boxicons CSS -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

    <!-- Other Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@7.0.96/css/materialdesignicons.min.css">

    <!-- SweetAlert -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <!-- Iconify -->
    <script src="https://code.iconify.design/3/3.1.0/iconify.min.js"></script>

    <!-- App CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link id="color" rel="stylesheet" href="{{ asset('assets/css/color-1.css') }}" media="screen">

    <!-- Custom Styles -->
    <style>
        .btn-group .btn {
            transition: all 0.3s ease;
            border-radius: 4px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .btn-group .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }

        .btn-group .btn i {
            font-size: 0.875rem;
        }

        .table .btn-group {
            display: flex;
            gap: 5px;
        }

        .badge.bg-danger { background-color: #dc3545 !important; }
        .badge.bg-primary { background-color: #0d6efd !important; }
        .badge.bg-warning { background-color: #ffc107 !important; color: #000 !important; }
        .badge.bg-secondary { background-color: #6c757d !important; }
        .badge.bg-success { background-color: #198754 !important; }
        .badge.bg-info { background-color: #0dcaf0 !important; color: #000 !important; }

        .mdi:before {
            font-size: inherit;
        }

        .submenu-arrow {
            margin-left: auto;
            width: 16px;
            height: 16px;
            transition: transform 0.3s ease;
        }

        .sidebar-list.open > .sidebar-link .submenu-arrow {
            transform: rotate(90deg);
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sidebar-link h6 {
            margin: 0;
        }

        .sidebar-submenu {
            transition: all 0.3s ease;
        }

        .modern-stat-card{
            background: #ffffff;
            border-radius: 18px;
            padding: 22px;
            display: flex;
            align-items: center;
            gap: 18px;
            border: 1px solid #eef1f6;
            box-shadow: 0 4px 18px rgba(15, 23, 42, 0.05);
            transition: all 0.25s ease;
            height: 100%;
        }

        .modern-stat-card:hover{
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.10);
        }

        .stat-icon{
            width: 58px;
            height: 58px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            flex-shrink: 0;
        }

        .stat-content{
            flex: 1;
        }

        .stat-label{
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.8px;
            color: #94a3b8;
            margin-bottom: 6px;
        }

        .stat-value{
            font-size: 30px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 4px;
            line-height: 1;
        }

        .stat-subtitle{
            font-size: 13px;
            color: #94a3b8;
        }

        /* Soft Background Colors */
        .bg-primary-soft{
            background: rgba(59, 130, 246, 0.12);
        }

        .bg-success-soft{
            background: rgba(16, 185, 129, 0.12);
        }

        .bg-danger-soft{
            background: rgba(239, 68, 68, 0.12);
        }

        .bg-info-soft{
            background: rgba(6, 182, 212, 0.12);
        }

        /* Calendar Styles for Modal */
        .calendar-weekdays {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .calendar-weekday {
            padding: 12px;
            text-align: center;
            font-weight: 600;
            color: #475569;
            font-size: 14px;
        }
        
        .calendar-week {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            min-height: 100px;
        }
        
        .calendar-day {
            border-right: 1px solid #e2e8f0;
            border-bottom: 1px solid #e2e8f0;
            padding: 8px;
            background: white;
            transition: all 0.2s ease;
            display: flex;
            flex-direction: column;
        }
        
        .calendar-day:nth-child(7n) {
            border-right: none;
        }
        
        .calendar-day.other-month {
            background: #f8fafc;
        }
        
        .calendar-day.today {
            background: #f0fdf4;
        }
        
        .calendar-day-header {
            text-align: right;
            margin-bottom: 8px;
        }
        
        .calendar-day-number {
            display: inline-block;
            width: 28px;
            height: 28px;
            line-height: 28px;
            text-align: center;
            border-radius: 50%;
            font-size: 13px;
            font-weight: 500;
            color: #1e293b;
        }
        
        .calendar-day.today .calendar-day-number {
            background: #22c55e;
            color: white;
        }
        
        .calendar-events {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 4px;
            overflow-y: auto;
            max-height: 100px;
        }
        
        .calendar-event {
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 11px;
            cursor: pointer;
            transition: all 0.2s ease;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .calendar-event:hover {
            transform: translateX(2px);
            filter: brightness(0.95);
        }
        
        .event-success {
            background: #dcfce7;
            border-left: 3px solid #22c55e;
            color: #166534;
        }
        
        .event-warning {
            background: #fef3c7;
            border-left: 3px solid #f59e0b;
            color: #92400e;
        }
        
        .event-danger {
            background: #fee2e2;
            border-left: 3px solid #ef4444;
            color: #991b1b;
        }
        
        .event-info {
            background: #dbeafe;
            border-left: 3px solid #3b82f6;
            color: #1e40af;
        }
        
        .event-title {
            font-weight: 600;
            margin-bottom: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .event-flock {
            font-size: 9px;
            opacity: 0.8;
            display: flex;
            align-items: center;
            gap: 2px;
        }
        
        .detail-section .row {
            margin-bottom: 8px;
        }


        /* Species Icon Animation */
.species-icon-wrapper {
    display: inline-block;
    transition: all 0.3s ease;
}

.species-card:hover .species-icon-wrapper {
    transform: scale(1.1);
    animation: bounce 0.5s ease;
}

.species-card:hover .species-icon {
    filter: drop-shadow(0 4px 8px rgba(0,0,0,0.2));
}

@keyframes bounce {
    0%, 100% { transform: scale(1.1); }
    50% { transform: scale(1.2); }
}

/* Hover transition for cards */
.hover-shadow {
    transition: all 0.3s ease;
    cursor: pointer;
}

.hover-shadow:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}


/* Logo Styling - Add this to your existing styles */
.logo-wrapper {
    position: relative;
    display: flex;
    align-items: center;
    gap: 10px;
}

.logo-wrapper .logo-link {
    display: flex;
    align-items: center;
    text-decoration: none;
}

.logo-wrapper .light-logo,
.logo-wrapper .dark-logo {
    max-height: 45px;
    width: auto;
    max-width: 180px;
    object-fit: contain;
    transition: all 0.3s ease;
}

/* Dark mode logo visibility */
.dark .light-logo {
    display: none !important;
}

.dark .dark-logo {
    display: block !important;
}

.light-logo {
    display: block;
}

.dark-logo {
    display: none;
}

/* Responsive logo */
@media (max-width: 768px) {
    .logo-wrapper .light-logo,
    .logo-wrapper .dark-logo {
        max-height: 35px;
        max-width: 140px;
    }
}

/* If logo is too wide, add this */
.logo-wrapper img {
    width: auto;
    height: auto;
    max-width: 160px;
}

.poultry-logo-link { text-decoration: none; }

.poultry-logo-wrap {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 6px 12px 6px 6px;
    border-radius: 10px;
    transition: background 0.2s;
}
.poultry-logo-wrap:hover { background: rgba(255,255,255,0.12); }

.poultry-icon-ring {
    width: 38px; height: 38px;
    border-radius: 50%;
    background: rgba(255,255,255,0.18);
    display: flex; align-items: center; justify-content: center;
    position: relative; overflow: hidden;
    flex-shrink: 0;
}

.poultry-icon {
    position: absolute;
    font-size: 20px;
    line-height: 1;
    transition: transform 0.4s cubic-bezier(0.34,1.56,0.64,1), opacity 0.3s ease;
}
.poultry-icon.visible  { opacity:1; transform: scale(1) rotate(0deg); }
.poultry-icon.hidden   { opacity:0; transform: scale(0.2) rotate(45deg); pointer-events:none; }

.poultry-logo-text {
    display: flex; flex-direction: column; line-height: 1.15;
}
.poultry-logo-text strong {
    color: #fff;
    font-size: 15px; font-weight: 700;
    letter-spacing: 0.04em;
}
.poultry-logo-text span {
    color: rgba(255,255,255,0.7);
    font-size: 9.5px;
    text-transform: uppercase;
    letter-spacing: 0.06em;
}

/* Pagination */
.page-link {
    border-radius: 8px !important;
    color: #0d6e4f;
    margin: 0 2px;
}
.page-item.active .page-link {
    background-color: #0d6e4f !important;
    border-color: #0d6e4f !important;
}
.page-link:hover {
    color: #0d6e4f;
    background-color: #f0fdf4;
    border-color: #0d6e4f;
}


/* ── Global Modal Consistency ── */
.modal-content {
    border: none;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0,0,0,0.15);
}

.modal-header {
    padding: 1.25rem 1.5rem;
    border-bottom: none;
}

.modal-header .modal-title {
    font-weight: 600;
    font-size: 1rem;
}

.modal-body {
    padding: 1.5rem;
}

.modal-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid #f1f5f9;
    background: #fff;
}

.modal-header.bg-primary,
.modal-header.bg-warning,
.modal-header.bg-success,
.modal-header.bg-danger,
.modal-header.bg-info {
    border-bottom: none;
}

/* Scrollable modal body max height */
.modal-dialog-scrollable .modal-body {
    max-height: 70vh;
    overflow-y: auto;
}

/* Detail sections inside modals */
.detail-section {
    margin-bottom: 1.5rem;
}

.detail-section h6 {
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #e2e8f0;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.detail-item {
    display: flex;
    flex-direction: column;
}

.detail-label {
    font-size: 0.68rem;
    text-transform: uppercase;
    color: #64748b;
    font-weight: 600;
    margin-bottom: 0.25rem;
    letter-spacing: 0.04em;
}

.detail-value {
    font-size: 0.95rem;
    font-weight: 500;
    color: #1e293b;
}


    </style>

    @stack('styles')
</head>

<body>

    <!-- IMPERSONATION BANNER -->
    @if(session()->has('impersonator_id'))
    <div id="impersonationBanner" style="background: #fef3c7; color: #92400e; padding: 12px 20px; text-align: center; border-bottom: 3px solid #f59e0b; position: sticky; top: 0; z-index: 9999;">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-8 text-center text-md-start">
                    <i class="fas fa-exchange-alt me-2"></i>
                    <strong>🔐 You are impersonating {{ Auth::user()->name }} ({{ ucfirst(Auth::user()->role) }})</strong>
                    <span class="mx-2">|</span>
                    <small class="text-muted">All actions are being logged with your original admin identity.</small>
                </div>
                <div class="col-md-4 text-center text-md-end mt-2 mt-md-0">
                    <form method="POST" action="{{ route('user.switch.back') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-danger btn-sm" style="background: #dc2626; color: white; border: none; padding: 6px 16px; border-radius: 6px; cursor: pointer;">
                            <i class="fas fa-sign-out-alt me-1"></i> Exit Impersonation
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Loader -->
    <div class="loader-wrapper">
        <div class="loader">
            <span></span><span></span><span></span><span></span><span></span>
        </div>
    </div>

    <!-- Scroll Top -->
    <div class="tap-top">
        <i class="iconly-Arrow-Up icli"></i>
    </div>

    <!-- Page Wrapper -->
    <div class="page-wrapper compact-wrapper" id="pageWrapper">

        @include('layouts.header')

        <div class="page-body-wrapper">

            @include('layouts.sidebar')

            <div class="page-body">
                @yield('content')
            </div>

            <footer class="footer">
                @include('layouts.footer')
            </footer>

        </div>
    </div>

    <!-- ==================== CLINICAL TOOLS MODALS ==================== -->

    <!-- Drug Formulary Modal -->
    <div class="modal fade" id="drugFormularyModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-capsules me-2"></i>Drug Formulary - Poultry Medications
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="drugFormularyContent" style="max-height: 70vh; overflow-y: auto;">
                    <div class="text-center py-4">
                        <div class="spinner-border text-info" role="status"></div>
                        <p class="mt-2">Loading drug formulary...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Disease Guide Modal -->
    <div class="modal fade" id="diseaseGuideModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-book-medical me-2"></i>Disease Guide - Common Poultry Diseases
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="diseaseGuideContent" style="max-height: 70vh; overflow-y: auto;">
                    <div class="text-center py-4">
                        <div class="spinner-border text-danger" role="status"></div>
                        <p class="mt-2">Loading disease guide...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Health Calendar Modal -->
    <div class="modal fade" id="healthCalendarModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-calendar-alt me-2"></i>Health Calendar
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="healthCalendarContent" style="max-height: 70vh; overflow-y: auto;">
                    <div class="text-center py-4">
                        <div class="spinner-border text-success" role="status"></div>
                        <p class="mt-2">Loading calendar...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Event Details Sub-Modal (for calendar events) -->
    <div class="modal fade" id="eventDetailsModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header" id="eventModalHeader">
                    <h5 class="modal-title" id="eventModalTitle">Event Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="eventDetailsContent">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-2">Loading details...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <a href="#" id="viewFullDetailsBtn" class="btn btn-primary" target="_blank">View Full Details</a>
                </div>
            </div>
        </div>
    </div>

    <!-- JS -->
    <script src="{{ asset('assets/js/vendors/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/js/vendors/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/vendors/bootstrap/dist/js/popper.min.js') }}"></script>

    <script src="{{ asset('assets/js/sidebar.js') }}"></script>
    <script src="{{ asset('assets/js/config.js') }}"></script>

    <!-- Charts -->
    <script src="{{ asset('assets/js/chart/apex-chart/apex-chart.js') }}"></script>
    <script src="{{ asset('assets/js/chart/apex-chart/stock-prices.js') }}"></script>

    <!-- Scrollbar -->
    <script src="{{ asset('assets/js/scrollbar/simplebar.js') }}"></script>
    <script src="{{ asset('assets/js/scrollbar/custom.js') }}"></script>

    <!-- UI Plugins -->
    <script src="{{ asset('assets/js/slick/slick.min.js') }}"></script>
    <script src="{{ asset('assets/js/slick/slick.js') }}"></script>
    <script src="{{ asset('assets/js/touchspin_2/custom_touchspin.js') }}"></script>

    <!-- DataTables -->
    <script src="{{ asset('assets/js/js-datatables/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/js/js-datatables/datatables/datatable.custom.js') }}"></script>
    <script src="{{ asset('assets/js/js-datatables/datatables/datatable.custom1.js') }}"></script>

    <!-- Swiper -->
    <script src="{{ asset('assets/js/vendors/swiper/swiper-bundle.min.js') }}"></script>

    <!-- Dashboard -->
    <script src="{{ asset('assets/js/dashboard/dashboard_2.js') }}"></script>

    <!-- Custom -->
    <script src="{{ asset('assets/js/script.js') }}"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            document.querySelectorAll('.sidebar-list').forEach(item => {
                const link = item.querySelector('.sidebar-link');
                const submenu = item.querySelector('.sidebar-submenu');
        
                if (link && submenu) {
                    link.addEventListener('click', function () {
                        item.classList.toggle('open');
                    });
                }
            });
        
            feather.replace();
        });

        // ==================== CLINICAL TOOLS MODAL FUNCTIONS ====================

        function openDrugFormularyModal() {
    const modal = new bootstrap.Modal(document.getElementById('drugFormularyModal'));
    const modalBody = document.getElementById('drugFormularyContent');
    modalBody.innerHTML = `<div class="text-center py-4"><div class="spinner-border text-info" role="status"></div><p class="mt-2">Loading drug formulary...</p></div>`;
    modal.show();
    
    fetch('{{ route("health-records.drug-formulary-modal") }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) modalBody.innerHTML = data.html;
            else modalBody.innerHTML = `<div class="alert alert-danger">Failed to load: ${data.message}</div>`;
        })
        .catch(error => modalBody.innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`);
}

function openDiseaseGuideModal() {
    const modal = new bootstrap.Modal(document.getElementById('diseaseGuideModal'));
    const modalBody = document.getElementById('diseaseGuideContent');
    modalBody.innerHTML = `<div class="text-center py-4"><div class="spinner-border text-danger" role="status"></div><p class="mt-2">Loading disease guide...</p></div>`;
    modal.show();
    
    fetch('{{ route("health-records.disease-guide-modal") }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) modalBody.innerHTML = data.html;
            else modalBody.innerHTML = `<div class="alert alert-danger">Failed to load: ${data.message}</div>`;
        })
        .catch(error => modalBody.innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`);
}

function openHealthCalendarModal() {
    const modal = new bootstrap.Modal(document.getElementById('healthCalendarModal'));
    const modalBody = document.getElementById('healthCalendarContent');
    modalBody.innerHTML = `<div class="text-center py-4"><div class="spinner-border text-success" role="status"></div><p class="mt-2">Loading calendar...</p></div>`;
    modal.show();
    
    fetch('{{ route("health-records.health-calendar-modal") }}?year=' + new Date().getFullYear() + '&month=' + (new Date().getMonth() + 1))
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                modalBody.innerHTML = data.html;
                attachCalendarEvents();
            } else {
                modalBody.innerHTML = `<div class="alert alert-danger">Failed to load: ${data.message}</div>`;
            }
        })
        .catch(error => modalBody.innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`);
}
        function attachCalendarEvents() {
            const prevBtn = document.getElementById('calendarPrevMonth');
            if (prevBtn) {
                prevBtn.addEventListener('click', function() {
                    loadCalendarMonth(this.dataset.year, this.dataset.month);
                });
            }
            
            const nextBtn = document.getElementById('calendarNextMonth');
            if (nextBtn) {
                nextBtn.addEventListener('click', function() {
                    loadCalendarMonth(this.dataset.year, this.dataset.month);
                });
            }
        }

        function loadCalendarMonth(year, month) {
    const modalBody = document.getElementById('healthCalendarContent');
    modalBody.innerHTML = `<div class="text-center py-4"><div class="spinner-border text-success" role="status"></div><p class="mt-2">Loading calendar...</p></div>`;
    
    fetch(`/health-records/health-calendar-modal?year=${year}&month=${month}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                modalBody.innerHTML = data.html;
                // Re-attach event listeners after content loads
                setTimeout(() => {
                    attachCalendarEvents();
                }, 100);
            } else {
                modalBody.innerHTML = `<div class="alert alert-danger">Failed to load calendar: ${data.message}</div>`;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            modalBody.innerHTML = `<div class="alert alert-danger">Error loading calendar. Please try again.</div>`;
        });
}

function attachCalendarEvents() {
    // Handle previous month button
    const prevBtn = document.getElementById('calendarPrevMonth');
    if (prevBtn) {
        // Remove any existing event listeners by cloning
        const newPrevBtn = prevBtn.cloneNode(true);
        prevBtn.parentNode.replaceChild(newPrevBtn, prevBtn);
        newPrevBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const year = this.getAttribute('data-year');
            const month = this.getAttribute('data-month');
            if (year && month) {
                loadCalendarMonth(year, month);
            }
        });
    }
    
    // Handle next month button
    const nextBtn = document.getElementById('calendarNextMonth');
    if (nextBtn) {
        const newNextBtn = nextBtn.cloneNode(true);
        nextBtn.parentNode.replaceChild(newNextBtn, nextBtn);
        newNextBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const year = this.getAttribute('data-year');
            const month = this.getAttribute('data-month');
            if (year && month) {
                loadCalendarMonth(year, month);
            }
        });
    }
}
        function showEventDetails(type, id) {
            const modal = new bootstrap.Modal(document.getElementById('eventDetailsModal'));
            const modalBody = document.getElementById('eventDetailsContent');
            const modalTitle = document.getElementById('eventModalTitle');
            const modalHeader = document.getElementById('eventModalHeader');
            const viewFullBtn = document.getElementById('viewFullDetailsBtn');
            
            let url = '', title = '', headerClass = '';
            
            switch(type) {
                case 'vaccination':
                    url = `/vaccinations/${id}/details`;
                    title = '💉 Vaccination Details';
                    headerClass = 'bg-success text-white';
                    viewFullBtn.href = `/vaccinations/${id}`;
                    break;
                case 'treatment':
                    url = `/treatments/${id}/details`;
                    title = '💊 Treatment Details';
                    headerClass = 'bg-warning text-white';
                    viewFullBtn.href = `/treatments/${id}`;
                    break;
                case 'health_record':
                    url = `/health-records/${id}/details`;
                    title = '🏥 Health Record Details';
                    headerClass = 'bg-info text-white';
                    viewFullBtn.href = `/health-records/${id}`;
                    break;
                default:
                    url = '#';
                    title = 'Event Details';
                    headerClass = 'bg-primary text-white';
            }
            
            modalTitle.innerHTML = `<i class="fas fa-info-circle me-2"></i>${title}`;
            modalHeader.className = `modal-header ${headerClass}`;
            modalBody.innerHTML = `<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Loading details...</p></div>`;
            modal.show();
            
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        let detailsHtml = '';
                        if (type === 'vaccination') {
                            const v = data.vaccination;
                            detailsHtml = `
                                <div class="detail-section">
                                    <div class="row mb-2"><div class="col-4 fw-bold">Flock:</div><div class="col-8">${escapeHtml(v.flock_number)}</div></div>
                                    <div class="row mb-2"><div class="col-4 fw-bold">Vaccine:</div><div class="col-8">${escapeHtml(v.vaccine_name)}</div></div>
                                    <div class="row mb-2"><div class="col-4 fw-bold">Disease:</div><div class="col-8">${escapeHtml(v.disease_target)}</div></div>
                                    <div class="row mb-2"><div class="col-4 fw-bold">Date:</div><div class="col-8">${v.administration_date}</div></div>
                                    <div class="row mb-2"><div class="col-4 fw-bold">Route:</div><div class="col-8">${escapeHtml(v.route)}</div></div>
                                    <div class="row mb-2"><div class="col-4 fw-bold">Batch:</div><div class="col-8">${escapeHtml(v.batch_number)}</div></div>
                                </div>
                            `;
                        } else if (type === 'treatment') {
                            const t = data.treatment;
                            detailsHtml = `
                                <div class="detail-section">
                                    <div class="row mb-2"><div class="col-4 fw-bold">Flock:</div><div class="col-8">${escapeHtml(t.flock_number)}</div></div>
                                    <div class="row mb-2"><div class="col-4 fw-bold">Diagnosis:</div><div class="col-8">${escapeHtml(t.diagnosis)}</div></div>
                                    <div class="row mb-2"><div class="col-4 fw-bold">Product:</div><div class="col-8">${escapeHtml(t.product_name)}</div></div>
                                    <div class="row mb-2"><div class="col-4 fw-bold">Period:</div><div class="col-8">${t.start_date} to ${t.end_date}</div></div>
                                    <div class="row mb-2"><div class="col-4 fw-bold">Dosage:</div><div class="col-8">${escapeHtml(t.dosage)}</div></div>
                                    ${t.withdrawal_end_date ? `<div class="row mb-2"><div class="col-4 fw-bold">Withdrawal:</div><div class="col-8">Ends ${t.withdrawal_end_date}</div></div>` : ''}
                                </div>
                            `;
                        } else if (type === 'health_record') {
                            const hr = data.record;
                            detailsHtml = `
                                <div class="detail-section">
                                    <div class="row mb-2"><div class="col-4 fw-bold">Flock:</div><div class="col-8">${escapeHtml(hr.flock_number)}</div></div>
                                    <div class="row mb-2"><div class="col-4 fw-bold">Date:</div><div class="col-8">${hr.record_date}</div></div>
                                    <div class="row mb-2"><div class="col-4 fw-bold">Type:</div><div class="col-8">${escapeHtml(hr.record_type)}</div></div>
                                    <div class="row mb-2"><div class="col-4 fw-bold">Condition:</div><div class="col-8">${escapeHtml(hr.condition || 'N/A')}</div></div>
                                    <div class="row mb-2"><div class="col-4 fw-bold">Severity:</div><div class="col-8"><span class="badge bg-${hr.severity === 'critical' ? 'danger' : (hr.severity === 'warning' ? 'warning' : 'info')}">${escapeHtml(hr.severity)}</span></div></div>
                                    <div class="row mb-2"><div class="col-4 fw-bold">Affected:</div><div class="col-8">${hr.affected_count || 'N/A'} birds</div></div>
                                </div>
                            `;
                        }
                        modalBody.innerHTML = detailsHtml;
                    } else {
                        modalBody.innerHTML = `<div class="alert alert-danger">Failed to load details: ${data.message}</div>`;
                    }
                })
                .catch(error => {
                    modalBody.innerHTML = `<div class="alert alert-danger">Error loading details: ${error.message}</div>`;
                });
        }

        function escapeHtml(str) {
            if (!str) return '';
            return String(str).replace(/[&<>]/g, function(m) {
                if (m === '&') return '&amp;';
                if (m === '<') return '&lt;';
                if (m === '>') return '&gt;';
                return m;
            });
        }

        (function() {
    const icons = ['🐔','🥚','🐣','🍗','🐖','🐐','🌾','🏡'];
    let idx = 0, cycleTimer = null;
    const link = document.querySelector('.poultry-logo-link');
    if (!link) return;

    function nextIcon() {
        const cur  = document.getElementById('pl-cur');
        const next = document.getElementById('pl-next');
        idx = (idx + 1) % icons.length;
        next.textContent = icons[idx];
        cur.classList.remove('visible');  cur.classList.add('hidden');
        next.classList.remove('hidden'); next.classList.add('visible');
        // swap IDs so the pattern repeats correctly
        cur.id = 'pl-next';
        next.id = 'pl-cur';
    }

    link.addEventListener('mouseenter', () => {
        cycleTimer = setInterval(nextIcon, 500);
    });
    link.addEventListener('mouseleave', () => {
        clearInterval(cycleTimer);
    });
})();
    </script>

    @stack('scripts')

</body>
</html>