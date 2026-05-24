{{-- resources/views/breeding-records/create.blade.php --}}
@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6 col-12">
                <h2>New Breeding Record</h2>
                <p class="mb-0 text-title-gray">Record a new breeding activity</p>
            </div>
            <div class="col-sm-6 col-12">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="iconly-Home icli svg-color"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('breeding-records.index') }}">Breeding</a></li>
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
                    <h3>New Breeding Record</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('breeding-records.store') }}">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Female Flock (Dam) <span class="text-danger">*</span></label>
                                <select name="flock_id" class="form-select @error('flock_id') is-invalid @enderror" required>
                                    <option value="">Select Female Flock</option>
                                    @foreach($femaleFlocks as $flock)
                                        <option value="{{ $flock->id }}" {{ old('flock_id', $flock->id ?? '') == $flock->id ? 'selected' : '' }}>
                                            {{ $flock->flock_number }} - {{ $flock->breed_variety }} ({{ $flock->species->name ?? 'N/A' }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('flock_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Male Flock (Sire)</label>
                                <select name="mate_id" class="form-select @error('mate_id') is-invalid @enderror">
                                    <option value="">Select Male Flock (or use AI)</option>
                                    @foreach($maleFlocks as $flock)
                                        <option value="{{ $flock->id }}" {{ old('mate_id') == $flock->id ? 'selected' : '' }}>
                                            {{ $flock->flock_number }} - {{ $flock->breed_variety }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Leave empty for Artificial Insemination</small>
                                @error('mate_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Breeding Date <span class="text-danger">*</span></label>
                                <input type="date" name="breeding_date" class="form-control @error('breeding_date') is-invalid @enderror" value="{{ old('breeding_date', date('Y-m-d')) }}" required>
                                @error('breeding_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Expected Delivery Date <span class="text-danger">*</span></label>
                                <input type="date" name="expected_delivery_date" class="form-control @error('expected_delivery_date') is-invalid @enderror" value="{{ old('expected_delivery_date') }}" required>
                                <small class="text-muted">Based on species gestation period</small>
                                @error('expected_delivery_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Breeding Method <span class="text-danger">*</span></label>
                                <select name="breeding_method" class="form-select @error('breeding_method') is-invalid @enderror" required>
                                    <option value="natural" {{ old('breeding_method') == 'natural' ? 'selected' : '' }}>Natural</option>
                                    <option value="artificial_insemination" {{ old('breeding_method') == 'artificial_insemination' ? 'selected' : '' }}>Artificial Insemination</option>
                                </select>
                                @error('breeding_method') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-12 mb-3">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3">{{ old('notes') }}</textarea>
                                @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">Create Breeding Record</button>
                            <a href="{{ route('breeding-records.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Auto-calculate expected delivery date based on species
    const speciesGestation = @json($femaleFlocks->pluck('species.gestation_days', 'id'));
    
    document.querySelector('select[name="flock_id"]').addEventListener('change', function() {
        const flockId = this.value;
        const breedingDate = document.querySelector('input[name="breeding_date"]').value;
        
        if (flockId && speciesGestation[flockId] && breedingDate) {
            const gestationDays = speciesGestation[flockId];
            const expectedDate = new Date(breedingDate);
            expectedDate.setDate(expectedDate.getDate() + parseInt(gestationDays));
            
            const year = expectedDate.getFullYear();
            const month = String(expectedDate.getMonth() + 1).padStart(2, '0');
            const day = String(expectedDate.getDate()).padStart(2, '0');
            
            document.querySelector('input[name="expected_delivery_date"]').value = `${year}-${month}-${day}`;
        }
    });
    
    document.querySelector('input[name="breeding_date"]').addEventListener('change', function() {
        const event = new Event('change');
        document.querySelector('select[name="flock_id"]').dispatchEvent(event);
    });
</script>
@endpush
@endsection