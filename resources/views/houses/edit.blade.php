{{-- resources/views/houses/edit.blade.php --}}
@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6 col-12">
                <h2>Edit House</h2>
                <p class="mb-0 text-title-gray">Update {{ $house->name }} details</p>
            </div>
            <div class="col-sm-6 col-12">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="iconly-Home icli svg-color"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('houses.index') }}">Houses</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('houses.show', $house->id) }}">{{ $house->name }}</a></li>
                    <li class="breadcrumb-item active">Edit</li>
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
                    <h3>Edit House: {{ $house->name }}</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('houses.update', $house->id) }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">House Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                    value="{{ old('name', $house->name) }}" required>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">House Code <span class="text-danger">*</span></label>
                                <input type="text" name="house_code" class="form-control @error('house_code') is-invalid @enderror" 
                                    value="{{ old('house_code', $house->house_code) }}" required>
                                <small class="text-muted">Unique identifier for this house (e.g., H01, BARN-A)</small>
                                @error('house_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Species Assignment</label>
                                <select name="species_id" class="form-select @error('species_id') is-invalid @enderror">
                                    <option value="">Not Assigned</option>
                                    @foreach($species as $spec)
                                        <option value="{{ $spec->id }}" {{ old('species_id', $house->species_id) == $spec->id ? 'selected' : '' }}>
                                            {{ $spec->name }} ({{ $spec->code }})
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Assign a default species for this house</small>
                                @error('species_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Capacity</label>
                                <input type="number" name="capacity" class="form-control @error('capacity') is-invalid @enderror" 
                                    value="{{ old('capacity', $house->capacity) }}" min="0">
                                <small class="text-muted">Maximum number of animals this house can hold</small>
                                @error('capacity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-12 mb-3">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">Dimensions</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Length (m)</label>
                                                <input type="number" name="length_m" class="form-control @error('length_m') is-invalid @enderror" 
                                                    value="{{ old('length_m', $house->length_m) }}" step="0.01" min="0">
                                                @error('length_m') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            </div>
                                            
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Width (m)</label>
                                                <input type="number" name="width_m" class="form-control @error('width_m') is-invalid @enderror" 
                                                    value="{{ old('width_m', $house->width_m) }}" step="0.01" min="0">
                                                @error('width_m') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            </div>
                                            
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Height (m)</label>
                                                <input type="number" name="height_m" class="form-control @error('height_m') is-invalid @enderror" 
                                                    value="{{ old('height_m', $house->height_m) }}" step="0.01" min="0">
                                                @error('height_m') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            </div>
                                        </div>
                                        @if($house->length_m && $house->width_m)
                                            <div class="alert alert-info mt-2">
                                                <strong>Area:</strong> {{ number_format($house->length_m * $house->width_m, 2) }} m²
                                                @if($house->capacity)
                                                    <br><strong>Density:</strong> {{ number_format($house->capacity / ($house->length_m * $house->width_m), 2) }} animals/m²
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-12 mb-3">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">Equipment Count</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-3 mb-3">
                                                <label class="form-label">Feeders</label>
                                                <input type="number" name="feeders_count" class="form-control @error('feeders_count') is-invalid @enderror" 
                                                    value="{{ old('feeders_count', $house->feeders_count) }}" min="0">
                                                @error('feeders_count') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            </div>
                                            
                                            <div class="col-md-3 mb-3">
                                                <label class="form-label">Drinkers</label>
                                                <input type="number" name="drinkers_count" class="form-control @error('drinkers_count') is-invalid @enderror" 
                                                    value="{{ old('drinkers_count', $house->drinkers_count) }}" min="0">
                                                @error('drinkers_count') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            </div>
                                            
                                            <div class="col-md-3 mb-3">
                                                <label class="form-label">Fans</label>
                                                <input type="number" name="fans_count" class="form-control @error('fans_count') is-invalid @enderror" 
                                                    value="{{ old('fans_count', $house->fans_count) }}" min="0">
                                                @error('fans_count') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            </div>
                                            
                                            <div class="col-md-3 mb-3">
                                                <label class="form-label">Heaters</label>
                                                <input type="number" name="heaters_count" class="form-control @error('heaters_count') is-invalid @enderror" 
                                                    value="{{ old('heaters_count', $house->heaters_count) }}" min="0">
                                                @error('heaters_count') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                    <option value="active" {{ old('status', $house->status) == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="maintenance" {{ old('status', $house->status) == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                    <option value="cleaning" {{ old('status', $house->status) == 'cleaning' ? 'selected' : '' }}>Cleaning</option>
                                    <option value="inactive" {{ old('status', $house->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                                <small class="text-muted">
                                    @if($house->status === 'active' && $house->currentFlock)
                                        <span class="text-warning">⚠️ This house currently has an active flock. Changing status may affect operations.</span>
                                    @endif
                                </small>
                                @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-12 mb-3">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" 
                                    rows="3">{{ old('notes', $house->notes) }}</textarea>
                                @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        
                        <!-- Current Occupancy Info -->
                        @php
                            $currentFlock = App\Models\Flock::where('house_id', $house->id)->where('status', 'active')->first();
                        @endphp
                        @if($currentFlock)
                            <div class="alert alert-warning">
                                <h5><i class="fa fa-info-circle"></i> Current Occupancy Information</h5>
                                <p>This house currently has an active flock:</p>
                                <ul>
                                    <li><strong>Flock Number:</strong> {{ $currentFlock->flock_number }}</li>
                                    <li><strong>Animals:</strong> {{ number_format($currentFlock->current_count) }} / {{ number_format($house->capacity) }}</li>
                                    <li><strong>Occupancy Rate:</strong> {{ number_format(($currentFlock->current_count / max($house->capacity, 1)) * 100, 1) }}%</li>
                                </ul>
                                <p class="mb-0">Changing capacity below current animal count may cause issues.</p>
                            </div>
                        @endif
                        
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">Update House</button>
                            <a href="{{ route('houses.show', $house->id) }}" class="btn btn-secondary">Cancel</a>
                            @if(!$currentFlock)
                                <button type="button" class="btn btn-danger float-end" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                    <i class="fa fa-trash"></i> Delete House
                                </button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
@if(!$currentFlock)
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('houses.destroy', $house->id) }}">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title">Delete House</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete <strong>{{ $house->name }}</strong>?</p>
                    <p class="text-danger">This action cannot be undone. All associated data will be permanently removed.</p>
                    @if($house->flocks()->count() > 0)
                        <p class="text-warning">⚠️ This house has {{ $house->flocks()->count() }} flock records. Deleting will also remove these associations.</p>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete Permanently</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@push('scripts')
<script>
    // Auto-calculate area when dimensions change
    const lengthInput = document.querySelector('input[name="length_m"]');
    const widthInput = document.querySelector('input[name="width_m"]');
    const capacityInput = document.querySelector('input[name="capacity"]');
    
    function updateAreaInfo() {
        const length = parseFloat(lengthInput?.value) || 0;
        const width = parseFloat(widthInput?.value) || 0;
        const capacity = parseFloat(capacityInput?.value) || 0;
        
        if (length > 0 && width > 0) {
            const area = length * width;
            const density = capacity / area;
            
            // Find or create area info div
            let areaInfo = document.querySelector('.area-info');
            if (!areaInfo) {
                areaInfo = document.createElement('div');
                areaInfo.className = 'alert alert-info mt-2 area-info';
                lengthInput.closest('.row').appendChild(areaInfo);
            }
            
            areaInfo.innerHTML = `
                <strong>Area:</strong> ${area.toFixed(2)} m²
                ${capacity > 0 ? `<br><strong>Density:</strong> ${density.toFixed(2)} animals/m²` : ''}
            `;
        }
    }
    
    if (lengthInput && widthInput) {
        lengthInput.addEventListener('input', updateAreaInfo);
        widthInput.addEventListener('input', updateAreaInfo);
        capacityInput?.addEventListener('input', updateAreaInfo);
    }
</script>
@endpush
@endsection