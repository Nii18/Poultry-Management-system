{{-- resources/views/settings/index.blade.php --}}
@extends('layouts.master')

@section('content')
<div class="container-fluid settings-page">

    {{-- Page title --}}
    <div class="page-title">
        <div class="row align-items-center">
            <div class="col-sm-6 col-12">
                <h2>Settings</h2>
                <p class="mb-0 text-title-gray">Configure your farm and system preferences</p>
            </div>
            <div class="col-sm-6 col-12">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li class="breadcrumb-item active">Settings</li>
                </ol>
            </div>
        </div>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="alert settings-alert settings-alert-success" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert settings-alert settings-alert-error" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        </div>
    @endif

    {{-- Tab layout --}}
    <div class="settings-shell">

        {{-- Sidebar nav --}}
        <nav class="settings-nav" id="settingsNav">
            @php
                $tabs = [
                    ['id' => 'general',    'icon' => 'fa-building',   'label' => 'Farm details'],
                    ['id' => 'alerts',     'icon' => 'fa-bell',       'label' => 'Alert thresholds'],
                    ['id' => 'production', 'icon' => 'fa-chart-line', 'label' => 'Production standards'],
                    ['id' => 'system',     'icon' => 'fa-server',     'label' => 'System'],
                ];
            @endphp

            @foreach($tabs as $tab)
                <button
                    class="settings-nav-item {{ $loop->first ? 'active' : '' }}"
                    data-tab="{{ $tab['id'] }}"
                    onclick="switchTab('{{ $tab['id'] }}')"
                    type="button"
                >
                    <span class="settings-nav-icon"><i class="fas {{ $tab['icon'] }}"></i></span>
                    <span class="settings-nav-label">{{ $tab['label'] }}</span>
                </button>
            @endforeach
        </nav>

        {{-- Content panels --}}
        <div class="settings-content">

            {{-- ── FARM DETAILS ─────────────────────────────────── --}}
            <div class="settings-panel active" id="tab-general">
                <div class="settings-panel-header">
                    <div>
                        <h3>Farm details</h3>
                        <p>Basic information about your farm shown across the system.</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('settings.update-general') }}">
                    @csrf
                    <div class="settings-grid-2">
                        <div class="field-group">
                            <label class="field-label">Farm name</label>
                            <input type="text" name="farm_name" class="field-input @error('farm_name') is-invalid @enderror"
                                   value="{{ old('farm_name', $settings['farm_name'] ?? '') }}" required>
                            @error('farm_name')<span class="field-error">{{ $message }}</span>@enderror
                        </div>
                        <div class="field-group">
                            <label class="field-label">Farm phone</label>
                            <input type="text" name="farm_phone" class="field-input @error('farm_phone') is-invalid @enderror"
                                   value="{{ old('farm_phone', $settings['farm_phone'] ?? '') }}">
                            @error('farm_phone')<span class="field-error">{{ $message }}</span>@enderror
                        </div>
                        <div class="field-group field-span-2">
                            <label class="field-label">Farm address</label>
                            <textarea name="farm_address" class="field-input field-textarea @error('farm_address') is-invalid @enderror"
                                      rows="2">{{ old('farm_address', $settings['farm_address'] ?? '') }}</textarea>
                        </div>
                        <div class="field-group">
                            <label class="field-label">Farm email</label>
                            <input type="email" name="farm_email" class="field-input @error('farm_email') is-invalid @enderror"
                                   value="{{ old('farm_email', $settings['farm_email'] ?? '') }}">
                            @error('farm_email')<span class="field-error">{{ $message }}</span>@enderror
                        </div>
                        <div class="field-group">
                            <label class="field-label">Timezone</label>
                            <select name="timezone" class="field-input field-select" required>
                                @foreach(timezone_identifiers_list() as $tz)
                                    <option value="{{ $tz }}" {{ ($settings['timezone'] ?? config('app.timezone')) === $tz ? 'selected' : '' }}>
                                        {{ $tz }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field-group">
                            <label class="field-label">Date format</label>
                            <select name="date_format" class="field-input field-select" required>
                                @foreach(['Y-m-d' => 'YYYY-MM-DD', 'm/d/Y' => 'MM/DD/YYYY', 'd/m/Y' => 'DD/MM/YYYY'] as $val => $label)
                                    <option value="{{ $val }}" {{ ($settings['date_format'] ?? 'Y-m-d') === $val ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field-group">
                            <label class="field-label">Currency</label>
                            <select name="currency" class="field-input field-select" required>
                                @foreach(['USD' => 'USD — US Dollar', 'GHS' => 'GHS — Ghana Cedi', 'NGN' => 'NGN — Nigerian Naira', 'EUR' => 'EUR — Euro', 'GBP' => 'GBP — British Pound'] as $val => $label)
                                    <option value="{{ $val }}" {{ ($settings['currency'] ?? 'GHS') === $val ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="settings-actions">
                        <button type="submit" class="btn-save">Save farm details</button>
                    </div>
                </form>
            </div>

            {{-- ── ALERT THRESHOLDS ──────────────────────────────── --}}
            <div class="settings-panel" id="tab-alerts">
                <div class="settings-panel-header">
                    <div>
                        <h3>Alert thresholds</h3>
                        <p>The system sends alerts when any reading exceeds these limits.</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('settings.update-alerts') }}">
                    @csrf
                    <div class="settings-grid-2">
                        <div class="field-group">
                            <label class="field-label">Mortality threshold <span class="field-unit">%</span></label>
                            <input type="number" name="mortality_threshold" class="field-input" step="0.5" min="0" max="100"
                                   value="{{ old('mortality_threshold', $settings['mortality_threshold'] ?? 3) }}" required>
                            <span class="field-hint">Alert when daily mortality exceeds this rate</span>
                        </div>
                        <div class="field-group">
                            <label class="field-label">Temperature deviation <span class="field-unit">°C</span></label>
                            <input type="number" name="temperature_deviation" class="field-input" step="0.5" min="0" max="10"
                                   value="{{ old('temperature_deviation', $settings['temperature_deviation'] ?? 3) }}" required>
                            <span class="field-hint">Alert when house temp deviates beyond this</span>
                        </div>
                        <div class="field-group">
                            <label class="field-label">Ammonia threshold <span class="field-unit">ppm</span></label>
                            <input type="number" name="ammonia_threshold" class="field-input" step="1" min="0" max="100"
                                   value="{{ old('ammonia_threshold', $settings['ammonia_threshold'] ?? 25) }}" required>
                            <span class="field-hint">Safe limit is typically 25 ppm</span>
                        </div>
                        <div class="field-group">
                            <label class="field-label">Low feed stock <span class="field-unit">kg</span></label>
                            <input type="number" name="low_feed_threshold_kg" class="field-input" step="50" min="0"
                                   value="{{ old('low_feed_threshold_kg', $settings['low_feed_threshold_kg'] ?? 500) }}" required>
                            <span class="field-hint">Alert when total feed stock falls below this</span>
                        </div>
                        <div class="field-group">
                            <label class="field-label">Withdrawal alert <span class="field-unit">days before</span></label>
                            <input type="number" name="withdrawal_alert_days" class="field-input" min="1" max="30"
                                   value="{{ old('withdrawal_alert_days', $settings['withdrawal_alert_days'] ?? 3) }}" required>
                            <span class="field-hint">Days before medication withdrawal ends</span>
                        </div>
                    </div>
                    <div class="settings-actions">
                        <button type="submit" class="btn-save">Save thresholds</button>
                    </div>
                </form>
            </div>

            {{-- ── PRODUCTION STANDARDS ─────────────────────────── --}}
            <div class="settings-panel" id="tab-production">
                <div class="settings-panel-header">
                    <div>
                        <h3>Production standards</h3>
                        <p>Target benchmarks used in performance dashboards and reports.</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('settings.update-production') }}">
                    @csrf
                    <div class="settings-grid-2">
                        <div class="field-group">
                            <label class="field-label">Target FCR</label>
                            <input type="number" name="target_fcr" class="field-input" step="0.01" min="0" max="10"
                                   value="{{ old('target_fcr', $settings['target_fcr'] ?? 1.8) }}" required>
                            <span class="field-hint">Feed Conversion Ratio — lower is better. Broilers typically 1.6–2.0</span>
                        </div>
                        <div class="field-group">
                            <label class="field-label">Daily weight gain target <span class="field-unit">g/day</span></label>
                            <input type="number" name="target_weight_gain_g" class="field-input" step="1" min="0"
                                   value="{{ old('target_weight_gain_g', $settings['target_weight_gain_g'] ?? 55) }}" required>
                            <span class="field-hint">Average grams per bird per day</span>
                        </div>
                        <div class="field-group">
                            <label class="field-label">Egg production rate <span class="field-unit">%</span></label>
                            <input type="number" name="target_egg_production_pct" class="field-input" step="0.5" min="0" max="100"
                                   value="{{ old('target_egg_production_pct', $settings['target_egg_production_pct'] ?? 80) }}" required>
                            <span class="field-hint">Target percentage of hens laying daily</span>
                        </div>
                        <div class="field-group">
                            <label class="field-label">Stocking density <span class="field-unit">birds/m²</span></label>
                            <input type="number" name="target_flock_density" class="field-input" step="0.5" min="0"
                                   value="{{ old('target_flock_density', $settings['target_flock_density'] ?? 10) }}" required>
                            <span class="field-hint">Maximum birds per square metre</span>
                        </div>
                        <div class="field-group">
                            <label class="field-label">Water-to-feed ratio</label>
                            <input type="number" name="target_water_feed_ratio" class="field-input" step="0.1" min="0" max="10"
                                   value="{{ old('target_water_feed_ratio', $settings['target_water_feed_ratio'] ?? 1.8) }}" required>
                            <span class="field-hint">Litres of water per kg of feed consumed</span>
                        </div>
                    </div>
                    <div class="settings-actions">
                        <button type="submit" class="btn-save">Save production standards</button>
                    </div>
                </form>
            </div>

            {{-- ── SYSTEM ────────────────────────────────────────── --}}
            <div class="settings-panel" id="tab-system">
                <div class="settings-panel-header">
                    <div>
                        <h3>System</h3>
                        <p>Maintenance actions and runtime information.</p>
                    </div>
                </div>

                <div class="settings-section-label">Actions</div>
                <div class="system-actions-grid">
                    <div class="system-action-card">
                        <div class="system-action-icon system-action-icon--amber">
                            <i class="fas fa-broom"></i>
                        </div>
                        <div>
                            <div class="system-action-title">Clear application cache</div>
                            <div class="system-action-desc">Flushes config, view, route, and data caches. Safe to run anytime.</div>
                        </div>
                        <form method="POST" action="{{ route('settings.clear-cache') }}" class="ms-auto">
                            @csrf
                            <button type="submit" class="btn-outline btn-outline--amber">Clear cache</button>
                        </form>
                    </div>
                    <div class="system-action-card">
                        <div class="system-action-icon system-action-icon--blue">
                            <i class="fas fa-database"></i>
                        </div>
                        <div>
                            <div class="system-action-title">Download database backup</div>
                            <div class="system-action-desc">Creates a full SQL dump. Requires <code>mysqldump</code> on the server.</div>
                        </div>
                        <a href="{{ route('settings.backup') }}" class="btn-outline btn-outline--blue ms-auto">
                            Download backup
                        </a>
                    </div>
                </div>

                <div class="settings-section-label" style="margin-top:1.75rem;">Runtime information</div>
                <table class="sysinfo-table">
                    <tr><th>Laravel version</th><td>{{ app()->version() }}</td></tr>
                    <tr><th>PHP version</th><td>{{ phpversion() }}</td></tr>
                    <tr><th>Environment</th><td>{{ app()->environment() }}</td></tr>
                    <tr><th>Debug mode</th>
                        <td>
                            @if(config('app.debug'))
                                <span class="sysinfo-badge sysinfo-badge--warn">Enabled — disable in production</span>
                            @else
                                <span class="sysinfo-badge sysinfo-badge--ok">Disabled</span>
                            @endif
                        </td>
                    </tr>
                    <tr><th>Server time</th><td>{{ now()->format('Y-m-d H:i:s T') }}</td></tr>
                    <tr><th>Cache driver</th><td>{{ config('cache.default') }}</td></tr>
                </table>
            </div>

        </div>{{-- /settings-content --}}
    </div>{{-- /settings-shell --}}
</div>
@endsection


@push('styles')
<style>
/* ─────────────────────────────────────────────────────────────
   Settings page — design system
   Palette:  primary green #1a7a4a, surface whites, slate text
   Signature: left sidebar nav with active pill indicator
───────────────────────────────────────────────────────────── */
.settings-page { padding-bottom: 3rem; }

/* Alert banners */
.settings-alert {
    display: flex;
    align-items: center;
    padding: .75rem 1.1rem;
    border-radius: 8px;
    margin-bottom: 1.25rem;
    font-size: .875rem;
    font-weight: 500;
    border: none;
}
.settings-alert-success { background: #d1fae5; color: #065f46; }
.settings-alert-error   { background: #fee2e2; color: #991b1b; }
.dark .settings-alert-success { background: #064e3b; color: #6ee7b7; }
.dark .settings-alert-error   { background: #7f1d1d; color: #fca5a5; }

/* Shell — sidebar + content */
.settings-shell {
    display: flex;
    gap: 0;
    background: var(--white);
    border-radius: 14px;
    border: 1px solid #e5e7eb;
    overflow: hidden;
    min-height: 680px;
}
.dark .settings-shell { background: #1e2130; border-color: #2d3147; }

/* ── Sidebar nav ──────────────────────────────────────────── */
.settings-nav {
    width: 220px;
    flex-shrink: 0;
    border-right: 1px solid #e5e7eb;
    padding: 1rem .625rem;
    display: flex;
    flex-direction: column;
    gap: 2px;
    background: #f9fafb;
}
.dark .settings-nav { background: #181c2c; border-color: #2d3147; }

.settings-nav-item {
    display: flex;
    align-items: center;
    gap: .6rem;
    padding: .55rem .75rem;
    border-radius: 8px;
    border: none;
    background: transparent;
    cursor: pointer;
    font-size: .84rem;
    font-weight: 500;
    color: #6b7280;
    text-align: left;
    width: 100%;
    transition: background .15s, color .15s;
}
.settings-nav-item:hover { background: #f0fdf4; color: #1a7a4a; }
.settings-nav-item.active { background: #dcfce7; color: #15803d; }
.dark .settings-nav-item:hover  { background: #1e3a2f; color: #4ade80; }
.dark .settings-nav-item.active { background: #14532d; color: #86efac; }

.settings-nav-icon { width: 20px; text-align: center; font-size: .875rem; }
.settings-nav-label { line-height: 1.2; }

/* ── Content area ─────────────────────────────────────────── */
.settings-content { flex: 1; min-width: 0; }

.settings-panel { display: none; padding: 2rem; }
.settings-panel.active { display: block; }

.settings-panel-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    margin-bottom: 1.75rem;
    padding-bottom: 1.25rem;
    border-bottom: 1px solid #e5e7eb;
}
.dark .settings-panel-header { border-color: #2d3147; }

.settings-panel-header h3 {
    font-size: 1.05rem;
    font-weight: 600;
    margin: 0 0 .25rem;
    color: #111827;
}
.dark .settings-panel-header h3 { color: #f1f5f9; }

.settings-panel-header p {
    font-size: .84rem;
    color: #6b7280;
    margin: 0;
}

/* ── Field groups ─────────────────────────────────────────── */
.settings-grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.25rem 1.5rem;
}
.field-span-2 { grid-column: span 2; }

.field-group { display: flex; flex-direction: column; gap: .35rem; }

.field-label {
    font-size: .8rem;
    font-weight: 600;
    color: #374151;
    text-transform: uppercase;
    letter-spacing: .03em;
}
.dark .field-label { color: #94a3b8; }

.field-unit {
    font-weight: 400;
    text-transform: none;
    color: #9ca3af;
    letter-spacing: 0;
}

.field-input {
    padding: .55rem .75rem;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: .875rem;
    color: #111827;
    background: #fff;
    width: 100%;
    transition: border-color .15s, box-shadow .15s;
    outline: none;
}
.field-input:focus {
    border-color: #1a7a4a;
    box-shadow: 0 0 0 3px rgba(26,122,74,.12);
}
.field-input.is-invalid { border-color: #ef4444; }
.dark .field-input { background: #252a3d; border-color: #3a3f52; color: #e2e8f0; }
.dark .field-input:focus { border-color: #4ade80; box-shadow: 0 0 0 3px rgba(74,222,128,.12); }

.field-textarea { resize: vertical; min-height: 70px; }
.field-select { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%236b7280' d='M6 8L1 3h10z'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right .7rem center; padding-right: 2rem; }

.field-hint  { font-size: .75rem; color: #9ca3af; }
.field-error { font-size: .75rem; color: #ef4444; }

/* Section labels */
.settings-section-label {
    font-size: .7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: #9ca3af;
    margin-bottom: .75rem;
}

/* ── Save button ──────────────────────────────────────────── */
.settings-actions {
    margin-top: 1.75rem;
    padding-top: 1.25rem;
    border-top: 1px solid #e5e7eb;
}
.dark .settings-actions { border-color: #2d3147; }

.btn-save {
    padding: .6rem 1.5rem;
    background: #1a7a4a;
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: .875rem;
    font-weight: 600;
    cursor: pointer;
    transition: background .15s;
}
.btn-save:hover { background: #15803d; }

.btn-outline {
    padding: .55rem 1.1rem;
    background: transparent;
    border: 1.5px solid #d1d5db;
    border-radius: 8px;
    font-size: .84rem;
    font-weight: 500;
    cursor: pointer;
    color: #374151;
    transition: border-color .15s, color .15s;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
}
.btn-outline:hover { border-color: #6b7280; color: #111827; }
.btn-outline--amber { border-color: #f59e0b; color: #b45309; }
.btn-outline--amber:hover { background: #fef3c7; border-color: #d97706; }
.btn-outline--blue  { border-color: #3b82f6; color: #1d4ed8; }
.btn-outline--blue:hover  { background: #eff6ff; border-color: #2563eb; }
.dark .btn-outline { border-color: #3a3f52; color: #cbd5e1; }

/* ── System section ───────────────────────────────────────── */
.system-actions-grid { display: flex; flex-direction: column; gap: .75rem; }

.system-action-card {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem 1.25rem;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    background: #f9fafb;
}
.dark .system-action-card { background: #1a1f30; border-color: #2d3147; }

.system-action-icon {
    width: 40px; height: 40px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem;
    flex-shrink: 0;
}
.system-action-icon--amber { background: #fef3c7; color: #b45309; }
.system-action-icon--blue  { background: #eff6ff; color: #1d4ed8; }

.system-action-title { font-size: .875rem; font-weight: 600; color: #111827; margin-bottom: .2rem; }
.system-action-desc  { font-size: .78rem; color: #6b7280; }
.dark .system-action-title { color: #f1f5f9; }

.sysinfo-table { width: 100%; border-collapse: collapse; font-size: .875rem; }
.sysinfo-table th, .sysinfo-table td { padding: .65rem .85rem; border-bottom: 1px solid #e5e7eb; }
.dark .sysinfo-table th, .dark .sysinfo-table td { border-color: #2d3147; }
.sysinfo-table th { font-weight: 600; color: #374151; width: 40%; background: #f9fafb; }
.sysinfo-table td { color: #111827; }
.dark .sysinfo-table th { background: #1a1f30; color: #94a3b8; }
.dark .sysinfo-table td { color: #e2e8f0; }
.sysinfo-badge { display: inline-block; padding: 2px 10px; border-radius: 12px; font-size: .75rem; font-weight: 600; }
.sysinfo-badge--ok   { background: #d1fae5; color: #065f46; }
.sysinfo-badge--warn { background: #fef3c7; color: #92400e; }

/* ── Responsive ───────────────────────────────────────────── */
@media (max-width: 768px) {
    .settings-shell { flex-direction: column; }
    .settings-nav { width: 100%; flex-direction: row; overflow-x: auto; border-right: none; border-bottom: 1px solid #e5e7eb; padding: .5rem; gap: 4px; }
    .settings-nav-item { flex-direction: column; gap: .2rem; padding: .5rem .6rem; font-size: .72rem; white-space: nowrap; }
    .settings-nav-icon { font-size: 1rem; }
    .settings-grid-2 { grid-template-columns: 1fr; }
    .field-span-2 { grid-column: span 1; }
    .system-action-card { flex-wrap: wrap; }
    .system-action-card .ms-auto { margin-left: 0 !important; }
}
</style>
@endpush


@push('scripts')
<script>
function switchTab(id) {
    document.querySelectorAll('.settings-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.settings-nav-item').forEach(b => b.classList.remove('active'));

    const panel = document.getElementById('tab-' + id);
    if (panel) panel.classList.add('active');

    const btn = document.querySelector('[data-tab="' + id + '"]');
    if (btn) btn.classList.add('active');

    // Persist active tab across page loads (flash redirect)
    try { sessionStorage.setItem('settingsTab', id); } catch(e) {}
}

// Restore tab after form submit redirect
document.addEventListener('DOMContentLoaded', function () {
    try {
        const saved = sessionStorage.getItem('settingsTab');
        if (saved) switchTab(saved);
    } catch(e) {}

    // Persist tab before any form submit
    document.querySelectorAll('.settings-panel form').forEach(function(form) {
        form.addEventListener('submit', function() {
            const panel = form.closest('.settings-panel');
            if (panel) {
                try { sessionStorage.setItem('settingsTab', panel.id.replace('tab-', '')); } catch(e) {}
            }
        });
    });
});
</script>
@endpush