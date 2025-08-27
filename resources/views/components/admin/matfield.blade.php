<div class="row mat-field py-3">
    <div class="col-11">
        <div class="form-control production_rate">
            <select class="other_material_name" name="associated_products[${x}][material_id]">
                <option value="">Material</option>
                @php
                    $other_materials = get_master_materials();
                @endphp
                @foreach ($other_materials as $other_material)
                    <option value="{{ $other_material->id }}" data-unit="{{ $other_material->default_unit }}">
                        {{ $other_material->name }}
                    </option>
                @endforeach
            </select>
            -
            <input type="number" class="mini-unit" name="associated_products[${x}][price][amount]" placeholder="Qnt">
            <span class="other_product_unit">Piece</span>&nbsp;
            For
            1
            Current Product
        </div>
    </div>
    <div class="col-1">
        <button type="button" class="btn btn-sm btn-danger remove">
            <i class="bi bi-trash"></i>
        </button>
    </div>
</div>
