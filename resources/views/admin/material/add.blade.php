{{-- resources/views/admin/material/add.blade.php --}}
@php
  // Si viene desde el modal o por AJAX, solo renderiza el <form> sin layout
  $isModal = $isModal ?? request()->ajax();

  $isEdit  = isset($material) && $material;
  $action  = $isEdit ? route('admin.material.update', $material->id) : route('admin.material.store');

  // Normaliza catálogos por si vienen nulos
  $divisions = $divisions ?? [];
  $classes   = $classes ?? [];
  $types     = $types ?? [];
  $units     = $units ?? [];
  $products  = $products ?? [];

  // Associated products (array)
  $assoc = old('associated_products', isset($material) ? (json_decode($material->associated_products ?? '[]', true) ?: []) : []);
@endphp

@if(!$isModal)
  {{-- Página completa (no modal) --}}
  @extends('admin.layouts.app')
  @section('title', $isEdit ? 'Edit Material' : 'Create Material')
  @section('content')
  <div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h3 class="mb-0">{{ $isEdit ? 'Edit Material' : 'Create Material' }}</h3>
      <a href="{{ route('admin.material.index') }}" class="btn btn-outline-secondary">Volver</a>
    </div>

    @if ($errors->any())
      <div class="alert alert-danger">
        <ul class="mb-0">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <div class="card">
      <div class="card-header bg-transparent border-bottom text-uppercase">
        {{ $isEdit ? 'Editar' : 'Nuevo' }} material
      </div>
      <div class="card-body">
@endif

<form id="material-form" method="POST" action="{{ $action }}">
  @csrf
  @if($isEdit)
    @method('PUT')
  @endif

  <div class="row g-3">

    {{-- Name --}}
    <div class="col-md-6">
      <label class="form-label">Name <span class="text-danger">*</span></label>
      <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
             value="{{ old('name', $material->name ?? '') }}" required>
      @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Default Unit --}}
    <div class="col-md-6">
      <label class="form-label">Default Unit <span class="text-danger">*</span></label>
      <input type="text" name="default_unit" class="form-control @error('default_unit') is-invalid @enderror"
             value="{{ old('default_unit', $material->default_unit ?? '') }}" required>
      @error('default_unit') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Division --}}
    <div class="col-md-4">
      <label class="form-label">Division <span class="text-danger">*</span></label>
      <select name="material_division_id" class="form-select @error('material_division_id') is-invalid @enderror" required>
        <option value="">-- Select --</option>
        @foreach($divisions as $id => $text)
          <option value="{{ $id }}" @selected(old('material_division_id', $material->material_division_id ?? null) == $id)>{{ $text }}</option>
        @endforeach
      </select>
      @error('material_division_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Class --}}
    <div class="col-md-4">
      <label class="form-label">Class <span class="text-danger">*</span></label>
      <select name="material_class_id" class="form-select @error('material_class_id') is-invalid @enderror" required>
        <option value="">-- Select --</option>
        @foreach($classes as $id => $text)
          <option value="{{ $id }}" @selected(old('material_class_id', $material->material_class_id ?? null) == $id)>{{ $text }}</option>
        @endforeach
      </select>
      @error('material_class_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Type --}}
    <div class="col-md-4">
      <label class="form-label">Type</label>
      <select name="material_type_id" class="form-select @error('material_type_id') is-invalid @enderror">
        <option value="">-- Select --</option>
        @foreach($types as $id => $text)
          <option value="{{ $id }}" @selected(old('material_type_id', $material->material_type_id ?? null) == $id)>{{ $text }}</option>
        @endforeach
      </select>
      @error('material_type_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Measurement Unit --}}
    <div class="col-md-4">
      <label class="form-label">Measurement Unit <span class="text-danger">*</span></label>
      <select name="measurement_unit" class="form-select @error('measurement_unit') is-invalid @enderror" required>
        @foreach($units as $u => $label)
          <option value="{{ $u }}" @selected(old('measurement_unit', $material->measurement_unit ?? '') == $u)>{{ $label }}</option>
        @endforeach
      </select>
      @error('measurement_unit') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Unit Measure Value --}}
    <div class="col-md-4">
      <label class="form-label">Unit Measure Value</label>
      <input type="number" step="any" name="unit_measure_value" class="form-control @error('unit_measure_value') is-invalid @enderror"
             value="{{ old('unit_measure_value', $material->unit_measure_value ?? '') }}">
      @error('unit_measure_value') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Description --}}
    <div class="col-md-12">
      <label class="form-label">Description</label>
      <textarea name="description" rows="2" class="form-control @error('description') is-invalid @enderror">{{ old('description', $material->description ?? '') }}</textarea>
      @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Height --}}
    <div class="col-md-4">
      <label class="form-label">Height</label>
      <input type="number" step="any" name="height" class="form-control @error('height') is-invalid @enderror"
             value="{{ old('height', $material->height ?? '') }}">
      @error('height') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Width --}}
    <div class="col-md-4">
      <label class="form-label">Width</label>
      <input type="number" step="any" name="width" class="form-control @error('width') is-invalid @enderror"
             value="{{ old('width', $material->width ?? '') }}">
      @error('width') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Length --}}
    <div class="col-md-4">
      <label class="form-label">Length</label>
      <input type="number" step="any" name="length" class="form-control @error('length') is-invalid @enderror"
             value="{{ old('length', $material->length ?? '') }}">
      @error('length') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Waste --}}
    <div class="col-md-4">
      <label class="form-label">Waste</label>
      <input type="number" step="any" name="waste" class="form-control @error('waste') is-invalid @enderror"
             value="{{ old('waste', $material->waste ?? '') }}">
      @error('waste') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Production Rate --}}
    <div class="col-md-4">
      <label class="form-label">Production Rate</label>
      <input type="number" step="any" name="production_rate" class="form-control @error('production_rate') is-invalid @enderror"
             value="{{ old('production_rate', $material->production_rate ?? '') }}">
      @error('production_rate') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Subed Out Cost --}}
    <div class="col-md-4">
      <label class="form-label">Subed Out Cost</label>
      <input type="number" step="any" name="production_subed_out_cost" class="form-control @error('production_subed_out_cost') is-invalid @enderror"
             value="{{ old('production_subed_out_cost', $material->production_subed_out_cost ?? '') }}">
      @error('production_subed_out_cost') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Cleaning Cost --}}
    <div class="col-md-4">
      <label class="form-label">Cleaning Cost</label>
      <input type="number" step="any" name="cleaning_cost" class="form-control @error('cleaning_cost') is-invalid @enderror"
             value="{{ old('cleaning_cost', $material->cleaning_cost ?? '') }}">
      @error('cleaning_cost') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Cleaning Subed Out --}}
    <div class="col-md-4">
      <label class="form-label">Cleaning Subed Out</label>
      <input type="number" step="any" name="cleaning_subed_out" class="form-control @error('cleaning_subed_out') is-invalid @enderror"
             value="{{ old('cleaning_subed_out', $material->cleaning_subed_out ?? '') }}">
      @error('cleaning_subed_out') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Prices --}}
    <div class="col-md-12">
      <label class="form-label">Prices (JSON o texto)</label>
      <input type="text" name="prices" class="form-control @error('prices') is-invalid @enderror"
             value="{{ old('prices', $material->prices ?? '') }}">
      @error('prices') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Associated Products (multiple) --}}
    <div class="col-md-12">
      <label class="form-label">Associated Products</label>
      <select name="associated_products[]" multiple class="form-select @error('associated_products') is-invalid @enderror">
        @foreach($products as $pid => $pname)
          <option value="{{ $pid }}" @selected(in_array($pid, $assoc))>{{ $pname }}</option>
        @endforeach
      </select>
      @error('associated_products') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

  </div>

  @if(!$isModal)
    <div class="mt-4">
      <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Update' : 'Create' }}</button>
      <a href="{{ route('admin.material.index') }}" class="btn btn-outline-secondary">Cancelar</a>
    </div>
  @endif
</form>

@if(!$isModal)
      </div>
    </div>
  </div>
  @endsection
@endif
