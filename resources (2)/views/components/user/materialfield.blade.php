<div class="row my-3">
    <div class="col-md-12">
        <div class="form-group">
            <label>Material</label>
            <select class="form-control" name="materials[${x}][name]" id="">
                <option value="" hidden>Select</option>
                @php
                    $materials = get_materials();
                @endphp
                @foreach ($materials as $material)
                    <option value="{{ $material->name }}">{{ $material->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
        <div class="form-group">
            <label>Length
            {{-- ({{ $units->symbol }}): --}}
            </label>
            <input type="number" step="0.01" class="form-control mat_length_${x} measurement" data-disable=".mat_quantity_${x}" name="materials[${x}][length]">
        </div>
    </div>
    <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
        <div class="form-group">
            <label>Quantity:</label>
            <input type="number" step="0.01" class="form-control mat_quantity_${x}" data-disable=".mat_length_${x}" name="materials[${x}][quantity]" readonly>
        </div>
    </div>
    <button type="button" class="col-md-3 col-4 mx-2 btn btn-sm btn-danger remove">
        <i class="fa fa-trash"></i> Remove
    </button>
</div>
