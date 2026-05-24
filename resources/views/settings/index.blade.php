{{-- resources/views/settings/index.blade.php --}}
@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6 col-12">
                <h2>Settings</h2>
                <p class="mb-0 text-title-gray">Configure system settings and preferences</p>
            </div>
            <div class="col-sm-6 col-12">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="iconly-Home icli svg-color"></i></a></li>
                    <li class="breadcrumb-item">Settings</li>
                    <li class="breadcrumb-item active">General</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <!-- General Settings -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h3>General Settings</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('settings.update-general') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Application Name</label>
                            <input type="text" name="app_name" class="form-control" value="{{ config('app.name') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Farm Name</label>
                            <input type="text" name="farm_name" class="form-control" value="{{ $settings['farm_name'] ?? 'My Farm' }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Farm Address</label>
                            <textarea name="farm_address" class="form-control" rows="2">{{ $settings['farm_address'] ?? '' }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Farm Phone</label>
                            <input type="text" name="farm_phone" class="form-control" value="{{ $settings['farm_phone'] ?? '' }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Farm Email</label>
                            <input type="email" name="farm_email" class="form-control" value="{{ $settings['farm_email'] ?? '' }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Timezone</label>
                            <select name="timezone" class="form-select" required>
                                @foreach(timezone_identifiers_list() as $tz)
                                    <option value="{{ $tz }}" {{ (config('app.timezone') == $tz || ($settings['timezone'] ?? 'UTC') == $tz) ? 'selected' : '' }}>
                                        {{ $tz }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date Format</label>
                            <select name="date_format" class="form-select" required>
                                <option value="Y-m-d" {{ ($settings['date_format'] ?? 'Y-m-d') == 'Y-m-d' ? 'selected' : '' }}>YYYY-MM-DD</option>
                                <option value="m/d/Y" {{ ($settings['date_format'] ?? 'Y-m-d') == 'm/d/Y' ? 'selected' : '' }}>MM/DD/YYYY</option>
                                <option value="d/m/Y" {{ ($settings['date_format'] ?? 'Y-m-d') == 'd/m/Y' ? 'selected' : '' }}>DD/MM/YYYY</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Currency</label>
                            <select name="currency" class="form-select" required>
                                <option value="USD" {{ ($settings['currency'] ?? 'USD') == 'USD' ? 'selected' : '' }}>USD - US Dollar</option>
                                <option value="EUR" {{ ($settings['currency'] ?? 'USD') == 'EUR' ? 'selected' : '' }}>EUR - Euro</option>
                                <option value="GBP" {{ ($settings['currency'] ?? 'USD') == 'GBP' ? 'selected' : '' }}>GBP - British Pound</option>
                                <option value="GHS" {{ ($settings['currency'] ?? 'USD') == 'GHS' ? 'selected' : '' }}>GHS - Ghana Cedi</option>
                                <option value="NGN" {{ ($settings['currency'] ?? 'USD') == 'NGN' ? 'selected' : '' }}>NGN - Nigerian Naira</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Save General Settings</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Alert Thresholds -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h3>Alert Thresholds</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('settings.update-alerts') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Mortality Threshold (%)</label>
                            <input type="number" name="mortality_threshold" class="form-control" value="{{ $settings['mortality_threshold'] ?? 3 }}" step="0.5" min="0" max="100" required>
                            <small class="text-muted">Alert when daily mortality exceeds this percentage</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Temperature Deviation (°C)</label>
                            <input type="number" name="temperature_deviation" class="form-control" value="{{ $settings['temperature_deviation'] ?? 3 }}" step="0.5" min="0" max="10" required>
                            <small class="text-muted">Alert when temperature deviates beyond this range</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Ammonia Threshold (ppm)</label>
                            <input type="number" name="ammonia_threshold" class="form-control" value="{{ $settings['ammonia_threshold'] ?? 25 }}" step="1" min="0" max="100" required>
                            <small class="text-muted">Alert when ammonia levels exceed this value</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Low Feed Threshold (kg)</label>
                            <input type="number" name="low_feed_threshold_kg" class="form-control" value="{{ $settings['low_feed_threshold_kg'] ?? 500 }}" step="50" min="0" required>
                            <small class="text-muted">Alert when feed stock falls below this level</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Withdrawal Alert Days</label>
                            <input type="number" name="withdrawal_alert_days" class="form-control" value="{{ $settings['withdrawal_alert_days'] ?? 3 }}" min="1" max="30" required>
                            <small class="text-muted">Days before withdrawal end to send alert</small>
                        </div>
                        <button type="submit" class="btn btn-primary">Save Alert Settings</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- System Actions -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h3>System Actions</h3>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <form method="POST" action="{{ route('settings.clear-cache') }}">
                            @csrf
                            <button type="submit" class="btn btn-warning w-100">
                                <i class="fas fa-trash-alt me-2"></i> Clear Application Cache
                            </button>
                        </form>
                        <a href="{{ route('settings.backup') }}" class="btn btn-info w-100">
                            <i class="fas fa-database me-2"></i> Download Database Backup
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Info -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h3>System Information</h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th width="40%">Laravel Version</th>
                            <td>{{ app()->version() }}</td
                         </tr
                        <tr>
                            <th>PHP Version</th>
                            <td>{{ phpversion() }}</td
                         </tr
                        <tr>
                            <th>Environment</th>
                            <td>{{ app()->environment() }}</td
                         </tr
                        <tr>
                            <th>Debug Mode</th>
                            <td>{{ config('app.debug') ? 'Enabled' : 'Disabled' }}</td
                         </tr
                     </table
                </div>
            </div>
        </div>
    </div>
</div>
@endsection