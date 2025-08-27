<div class="form-row row">
    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
        <div class="form-group"><label>Choose Labor Type:</label><select class="form-control"
                name="labor_info[${x-1}][labor_type_id]" required>
                <option value="">Choose Labor</option>
                @php
                    $labor_types = get_labor_names();
                @endphp
                @foreach ($labor_types as $labor_type)
                    <option value="{{ $labor_type->id }}">{{ $labor_type->labor_type }}</option>
                @endforeach
            </select></div>
    </div>
    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
        <div class="form-group"><label>How many of this labor type:</label><input type="text" class="form-control"
                name="labor_info[${x-1}][quantity]" required></div>
    </div>
    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
        <div class="form-group"><label>How many regular Hrs per day:</label><input type="text" class="form-control"
                name="labor_info[${x-1}][hours_per_day]" required></div>
    </div>
    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
        <div class="form-group"><label>How many hours Overtime per day:</label><input type="text"
                class="form-control" name="labor_info[${x-1}][overtime_per_day]"></div>
    </div>
    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
        <div class="form-group"><label>How many double time per day:</label><input type="text" class="form-control"
                name="labor_info[${x-1}][doubletime_per_day]"></div>
    </div>
    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
        <div class="form-group"><button class="btn btn-danger btn-sm remove" style=""><i class="fa fa-trash"></i>
                Remove</button></div>
    </div>
</div>
