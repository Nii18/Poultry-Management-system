{{-- resources/views/health-records/create.blade.php --}}
@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6 col-12">
                <h2>New Health Record</h2>
                <p class="mb-0 text-title-gray">Record health observations and veterinary visits</p>
            </div>
            <div class="col-sm-6 col-12">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="iconly-Home icli svg-color"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('health-records.index') }}">Health</a></li>
                    <li class="breadcrumb-item active">Create</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3>New Health Record</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('health-records.store') }}">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Flock <span class="text-danger">*</span></label>
                                <select name="flock_id" class="form-select @error('flock_id') is-invalid @enderror" required>
                                    <option value="">Select Flock</option>
                                    @foreach($flocks as $flock)
                                        <option value="{{ $flock->id }}" {{ old('flock_id', $flock->id ?? '') == $flock->id ? 'selected' : '' }}>
                                            {{ $flock->flock_number }} - {{ $flock->breed_variety }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('flock_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Record Date <span class="text-danger">*</span></label>
                                <input type="date" name="record_date" class="form-control @error('record_date') is-invalid @enderror" value="{{ old('record_date', date('Y-m-d')) }}" required>
                                @error('record_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Record Type <span class="text-danger">*</span></label>
                                <select name="record_type" class="form-select @error('record_type') is-invalid @enderror" required>
                                    <option value="">Select Type</option>
                                    <option value="checkup" {{ old('record_type') == 'checkup' ? 'selected' : '' }}>Routine Checkup</option>
                                    <option value="symptom" {{ old('record_type') == 'symptom' ? 'selected' : '' }}>Symptom Observation</option>
                                    <option value="lab_result" {{ old('record_type') == 'lab_result' ? 'selected' : '' }}>Lab Result</option>
                                    <option value="post_mortem" {{ old('record_type') == 'post_mortem' ? 'selected' : '' }}>Post-Mortem</option>
                                    <option value="consultation" {{ old('record_type') == 'consultation' ? 'selected' : '' }}>Veterinary Consultation</option>
                                </select>
                                @error('record_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Severity <span class="text-danger">*</span></label>
                                <select name="severity" class="form-select @error('severity') is-invalid @enderror" required>
                                    <option value="info" {{ old('severity') == 'info' ? 'selected' : '' }}>Info - Routine</option>
                                    <option value="warning" {{ old('severity') == 'warning' ? 'selected' : '' }}>Warning - Monitor</option>
                                    <option value="critical" {{ old('severity') == 'critical' ? 'selected' : '' }}>Critical - Immediate Action</option>
                                </select>
                                @error('severity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Condition/Diagnosis</label>
                                <input type="text" name="condition" class="form-control @error('condition') is-invalid @enderror" value="{{ old('condition') }}" placeholder="e.g., Respiratory infection, Coccidiosis">
                                @error('condition') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Affected Count</label>
                                <input type="number" name="affected_count" class="form-control @error('affected_count') is-invalid @enderror" value="{{ old('affected_count') }}" min="0">
                                @error('affected_count') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-12 mb-3">
                                <label class="form-label">Symptoms (JSON format)</label>
                                <textarea name="symptoms" class="form-control @error('symptoms') is-invalid @enderror" rows="2" placeholder='{"coughing": true, "fever": 39.5, "loss_of_appetite": true}'>{{ old('symptoms') }}</textarea>
                                <small class="text-muted">Enter symptoms as JSON key-value pairs</small>
                                @error('symptoms') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-12 mb-3">
                                <label class="form-label">Lab Results (JSON format)</label>
                                <textarea name="lab_results" class="form-control @error('lab_results') is-invalid @enderror" rows="2" placeholder='{"blood_test": "normal", "parasite_count": 150}'>{{ old('lab_results') }}</textarea>
                                <small class="text-muted">Enter lab results as JSON key-value pairs</small>
                                @error('lab_results') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-12 mb-3">
                                <label class="form-label">Veterinarian Notes</label>
                                <textarea name="veterinarian_notes" class="form-control @error('veterinarian_notes') is-invalid @enderror" rows="3">{{ old('veterinarian_notes') }}</textarea>
                                @error('veterinarian_notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-12 mb-3">
                                <label class="form-label">Additional Notes</label>
                                <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="2">{{ old('notes') }}</textarea>
                                @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">Create Health Record</button>
                            <a href="{{ route('health-records.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection