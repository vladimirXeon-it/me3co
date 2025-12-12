{{-- resources/views/admin/material/add.blade.php --}}
@php
    // Siempre modo modal para el CRUD
    $isModal = request()->query('modal') == '1';

    $isEdit  = isset($material) && $material;

    // Agregamos ?modal=1 a la acción para que el controlador sepa que debe responder para modal
    $action  = $isEdit
        ? route('admin.material.update', $material->id) . '?modal=1'
        : route('admin.material.store') . '?modal=1';

    // Normaliza catálogos
    $divisions = $divisions ?? [];
    $classes   = $classes ?? [];
    $types     = $types ?? [];
    $units     = $units ?? [];
    $products  = $products ?? [];

    // Associated products (array)
    $assoc = old(
        'associated_products',
        isset($material) ? (json_decode($material->associated_products ?? '[]', true) ?: []) : []
    );
@endphp

{{-- ============================================================
     MODO MODAL → SOLO SE RENDERIZA EL FORM
     MODO NORMAL → SE RENDERIZA EL LAYOUT COMPLETO
=============================================================== --}}
@if(!$isModal)
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
                {{ $isEdit ? 'Editar Material' : 'Nuevo Material' }}
            </div>
            <div class="card-body">
@endif

{{--  ============================================================
        FORMULARIO PRINCIPAL
=============================================================== --}}
<form id="material-form" method="POST" action="{{ $action }}">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="row g-3">

        {{-- Name --}}
        <div class="col-md-6">
            <label class="form-label">Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control"
                   value="{{ old('name', $material->name ?? '') }}" required>
        </div>

        {{-- Default Unit --}}
        <div class="col-md-6">
            <label class="form-label">Default Unit <span class="text-danger">*</span></label>
            <input type="text" name="default_unit" class="form-control"
                   value="{{ old('default_unit', $material->default_unit ?? '') }}" required>
        </div>

        {{-- Division --}}
        <div class="col-md-4">
            <label class="form-label">Division <span class="text-danger">*</span></label>
            <select name="material_division_id" class="form-select" required>
                <option value="">-- Select --</option>
                @foreach($divisions as $id => $text)
                    <option value="{{ $id }}" @selected(old('material_division_id', $material->material_division_id ?? null) == $id)>{{ $text }}</option>
                @endforeach
            </select>
        </div>

        {{-- Class --}}
        <div class="col-md-4">
            <label class="form-label">Class <span class="text-danger">*</span></label>
            <select name="material_class_id" class="form-select" required>
                <option value="">-- Select --</option>
                @foreach($classes as $id => $text)
                    <option value="{{ $id }}" @selected(old('material_class_id', $material->material_class_id ?? null) == $id)>{{ $text }}</option>
                @endforeach
            </select>
        </div>

        {{-- Type --}}
        <div class="col-md-4">
            <label class="form-label">Type</label>
            <select name="material_type_id" class="form-select">
                <option value="">-- Select --</option>
                @foreach($types as $id => $text)
                    <option value="{{ $id }}" @selected(old('material_type_id', $material->material_type_id ?? null) == $id)>{{ $text }}</option>
                @endforeach
            </select>
        </div>

        {{-- Measurement Unit --}}
        <div class="col-md-4">
            <label class="form-label">Measurement Unit <span class="text-danger">*</span></label>
            <select name="measurement_unit" class="form-select" required>
                @foreach($units as $u => $label)
                    <option value="{{ $u }}" @selected(old('measurement_unit', $material->measurement_unit ?? '') == $u)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        {{-- Unit Measure Value --}}
        <div class="col-md-4">
            <label class="form-label">Unit Measure Value</label>
            <input type="number" step="any" name="unit_measure_value" class="form-control"
                   value="{{ old('unit_measure_value', $material->unit_measure_value ?? '') }}">
        </div>

        {{-- Description --}}
        <div class="col-md-12">
            <label class="form-label">Description</label>
            <textarea name="description" rows="2" class="form-control">{{ old('description', $material->description ?? '') }}</textarea>
        </div>

        {{-- Height --}}
        <div class="col-md-4">
            <label class="form-label">Height</label>
            <input type="number" step="any" name="height" class="form-control"
                   value="{{ old('height', $material->height ?? '') }}">
        </div>

        {{-- Width --}}
        <div class="col-md-4">
            <label class="form-label">Width</label>
            <input type="number" step="any" name="width" class="form-control"
                   value="{{ old('width', $material->width ?? '') }}">
        </div>

        {{-- Length --}}
        <div class="col-md-4">
            <label class="form-label">Length</label>
            <input type="number" step="any" name="length" class="form-control"
                   value="{{ old('length', $material->length ?? '') }}">
        </div>

        {{-- Waste --}}
        <div class="col-md-4">
            <label class="form-label">Waste</label>
            <input type="number" step="any" name="waste" class="form-control"
                   value="{{ old('waste', $material->waste ?? '') }}">
        </div>

        {{-- Production Rate --}}
        <div class="col-md-4">
            <label class="form-label">Production Rate</label>
            <input type="number" step="any" name="production_rate" class="form-control"
                   value="{{ old('production_rate', $material->production_rate ?? '') }}">
        </div>

        {{-- Subed Out Cost --}}
        <div class="col-md-4">
            <label class="form-label">Subed Out Cost</label>
            <input type="number" step="any" name="production_subed_out_cost" class="form-control"
                   value="{{ old('production_subed_out_cost', $material->production_subed_out_cost ?? '') }}">
        </div>

        {{-- Cleaning Cost --}}
        <div class="col-md-4">
            <label class="form-label">Cleaning Cost</label>
            <input type="number" step="any" name="cleaning_cost" class="form-control"
                   value="{{ old('cleaning_cost', $material->cleaning_cost ?? '') }}">
        </div>

        {{-- Cleaning Subed Out --}}
        <div class="col-md-4">
            <label class="form-label">Cleaning Subed Out</label>
            <input type="number" step="any" name="cleaning_subed_out" class="form-control"
                   value="{{ old('cleaning_subed_out', $material->cleaning_subed_out ?? '') }}">
        </div>

        {{-- Prices --}}
        <div class="col-md-12">
            <label class="form-label">Prices (JSON o texto)</label>
            <input type="text" name="prices" class="form-control"
                   value="{{ old('prices', $material->prices ?? '') }}">
        </div>

        {{-- Associated Products --}}
        <div class="col-md-12">
            <label class="form-label">Associated Products</label>
            <select name="associated_products[]" multiple class="form-select">
                @foreach($products as $pid => $pname)
                    <option value="{{ $pid }}" @selected(in_array($pid, $assoc))>{{ $pname }}</option>
                @endforeach
            </select>
        </div>

    </div>

    {{-- Botones solo si NO es modal --}}
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
