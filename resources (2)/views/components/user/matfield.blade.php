<div class="row mat-field py-3">
    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 mat_field_container">
        <div class="col-3">
            <label>Material</label>
            <select class="form-control other_material"
                name="associated_products[${x}][material_id]">
                <option value="">Material</option>
                @php
                    $other_materials = get_materials();
                @endphp
                @foreach ($other_materials as $other_material)
                    <option value="{{ $other_material->id }}"
                        data-unit="{{ $other_material->default_unit }}">
                        {{ $other_material->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-1">
            <label>Count</label>
            <input type="number" class="form-control" name="associated_products[${x}][required]"
                placeholder="0" >
        </div>
        <div class="col-1">
            <label>Unit</label>
            <input class="form-control other_material_unit" name="associated_products[${x}][unit]"
                placeholder="Unit" readonly>
        </div>
        
        <b>For every</b>
        <div class="col-1">
            <label>Count</label>
            <input type="number" class="form-control" name="associated_products[${x}][for]"
                placeholder="0" >
        </div>
        <div class="col-1">
            <label>Unit</label>
            <input class="form-control default_unit"
                placeholder="Unit" value="${DefaultUnit}" readonly>
        </div>
        <div class="col-2">
            <label>Material</label>
            <input class="form-control material_name"
                placeholder="Material Name" value="${MaterialName}" readonly>
        </div>
    </div>
    <div class="col-2 mt-2">
        <button type="button" class="btn btn-sm btn-danger remove">
            Remove <i class="fa fa-trash"></i>
        </button>
    </div>
</div>
