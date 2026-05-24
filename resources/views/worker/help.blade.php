@extends('layouts.master')

@section('title', 'Help & Tips')

@section('content')
<div class="container-fluid px-4">
    <!-- Page Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <div class="d-flex align-items-center gap-3">
                    <div class="page-icon bg-primary-soft">
                        <i class="fas fa-question-circle fs-1 text-primary"></i>
                    </div>
                    <div>
                        <h1 class="page-title mb-1">Help & Tips</h1>
                        <p class="page-description text-muted mb-0">Learn how to use the system effectively</p>
                    </div>
                </div>
            </div>
            <div class="col-auto">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Help & Tips</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Quick Log Guide -->
        <div class="col-md-6">
            <div class="help-card">
                <div class="help-icon bg-success-soft">
                    <i class="fas fa-plus-circle text-success"></i>
                </div>
                <div class="help-content">
                    <h5>📝 How to Use Quick Log</h5>
                    <ol class="mt-2">
                        <li>Click the <strong>"Quick Log"</strong> button on your dashboard</li>
                        <li>Select the <strong>flock</strong> you're working with</li>
                        <li>Enter the <strong>date</strong> (defaults to today)</li>
                        <li>Record <strong>mortality count</strong> (dead birds found)</li>
                        <li>Record <strong>feed intake</strong> in kilograms</li>
                        <li>Add any <strong>notes</strong> about bird behavior or issues</li>
                        <li>Click <strong>"Save Daily Log"</strong> to submit</li>
                    </ol>
                    <div class="alert alert-info mt-2">
                        <i class="fas fa-info-circle me-2"></i>
                        Tip: Always record mortality immediately to keep flock records accurate!
                    </div>
                </div>
            </div>
        </div>

        <!-- Feed Issuance Guide -->
        <div class="col-md-6">
            <div class="help-card">
                <div class="help-icon bg-primary-soft">
                    <i class="fas fa-seedling text-primary"></i>
                </div>
                <div class="help-content">
                    <h5>🍽️ How to Record Feed Issuance</h5>
                    <ol class="mt-2">
                        <li>Click <strong>"Feed Issuance"</strong> from your dashboard</li>
                        <li>Select the <strong>flock</strong> receiving feed</li>
                        <li>Choose the <strong>feed type</strong> (starter, grower, finisher, layer)</li>
                        <li>Enter the <strong>quantity</strong> in kilograms</li>
                        <li>Select the <strong>feeding method</strong> (manual or automatic)</li>
                        <li>Click <strong>"Save"</strong> to record</li>
                    </ol>
                    <div class="alert alert-warning mt-2">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Important: Feed is 60-70% of farm costs - accurate recording helps track efficiency!
                    </div>
                </div>
            </div>
        </div>

        <!-- Task Management -->
        <div class="col-md-6">
            <div class="help-card">
                <div class="help-icon bg-warning-soft">
                    <i class="fas fa-tasks text-warning"></i>
                </div>
                <div class="help-content">
                    <h5>✅ Using the Task Checklist</h5>
                    <ol class="mt-2">
                        <li>Open your <strong>dashboard</strong> to see today's tasks</li>
                        <li>As you complete each task, <strong>check the box</strong></li>
                        <li>Tasks will be <strong>saved automatically</strong> even after refresh</li>
                        <li>The counter shows your <strong>completion rate</strong></li>
                        <li>Managers can see your completed tasks</li>
                    </ol>
                    <div class="alert alert-success mt-2">
                        <i class="fas fa-check-circle me-2"></i>
                        Tip: Complete tasks as you go - it helps track your productivity!
                    </div>
                </div>
            </div>
        </div>

        <!-- Health Observation -->
        <div class="col-md-6">
            <div class="help-card">
                <div class="help-icon bg-danger-soft">
                    <i class="fas fa-heartbeat text-danger"></i>
                </div>
                <div class="help-content">
                    <h5>🩺 Health Observation Tips</h5>
                    <ul class="mt-2">
                        <li>✅ Birds should be <strong>alert and active</strong></li>
                        <li>✅ Feed and water should be <strong>consumed normally</strong></li>
                        <li>⚠️ Watch for: <strong>coughing, sneezing, discharge</strong></li>
                        <li>⚠️ Report: <strong>dead birds, sick birds, unusual behavior</strong></li>
                        <li>📋 Record all observations in the <strong>Quick Log</strong> notes</li>
                    </ul>
                    <div class="alert alert-danger mt-2">
                        <i class="fas fa-bell me-2"></i>
                        Emergency: Report any disease signs to your supervisor immediately!
                    </div>
                </div>
            </div>
        </div>

        <!-- Attendance Guide -->
        <div class="col-md-6">
            <div class="help-card">
                <div class="help-icon bg-info-soft">
                    <i class="fas fa-clock text-info"></i>
                </div>
                <div class="help-content">
                    <h5>⏰ Using the Attendance System</h5>
                    <ol class="mt-2">
                        <li>Click <strong>"Clock In"</strong> when you start work</li>
                        <li>Click <strong>"Clock Out"</strong> when you finish</li>
                        <li>The system automatically <strong>calculates your hours</strong></li>
                        <li>View your <strong>attendance history</strong> anytime</li>
                        <li>Total hours are <strong>saved daily</strong></li>
                    </ol>
                    <div class="alert alert-secondary mt-2">
                        <i class="fas fa-info-circle me-2"></i>
                        Tip: Always clock in/out for accurate payroll records!
                    </div>
                </div>
            </div>
        </div>

        <!-- FAQ -->
        <div class="col-md-6">
            <div class="help-card">
                <div class="help-icon bg-secondary-soft">
                    <i class="fas fa-question text-secondary"></i>
                </div>
                <div class="help-content">
                    <h5>❓ Frequently Asked Questions</h5>
                    <div class="accordion mt-2" id="faqAccordion">
                        <div class="accordion-item">
                            <h6 class="accordion-header">
                                <button class="accordion-button collapsed p-2" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                    What if I forget to clock out?
                                </button>
                            </h6>
                            <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body small">
                                    You can still clock out the next day, but the system will calculate based on the time you clock out. Contact your manager for corrections.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h6 class="accordion-header">
                                <button class="accordion-button collapsed p-2" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                    How do I edit a log I already saved?
                                </button>
                            </h6>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body small">
                                    Go to "All Logs", find your entry, click "Edit", make changes, and save. Only your own logs can be edited.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h6 class="accordion-header">
                                <button class="accordion-button collapsed p-2" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                    What should I do if I find sick birds?
                                </button>
                            </h6>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body small">
                                    Immediately report to your supervisor or the veterinarian. Use the Quick Log to record observations in the notes section.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .help-card {
        background: white;
        border-radius: 20px;
        border: 1px solid #e2e8f0;
        padding: 1.5rem;
        display: flex;
        gap: 1rem;
        transition: all 0.3s ease;
        height: 100%;
    }
    .help-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.08);
    }
    .help-icon {
        width: 50px;
        height: 50px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        flex-shrink: 0;
    }
    .help-content {
        flex: 1;
    }
    .help-content h5 {
        font-size: 1rem;
        margin-bottom: 0;
    }
    .help-content ol, .help-content ul {
        padding-left: 1.2rem;
        margin-bottom: 0;
        font-size: 0.85rem;
    }
    .help-content li {
        margin-bottom: 0.25rem;
    }
    .accordion-button {
        font-size: 0.8rem;
        background: transparent;
    }
    .accordion-button:focus {
        box-shadow: none;
    }
</style>
@endpush

@endsection