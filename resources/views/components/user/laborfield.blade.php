<div class="labor-extra-row mt-4">

    <div class="row g-3">

        <div class="col-md-6">
            <div class="form-group input-project">
                <label class="form-label fw-bold">Labor Type</label>

                <select class="form-control"
                        name="labor_info[${x-1}][labor_type_id]"
                        required>

                    <option value="">Choose Labor</option>

                    @php
                        $labor_types = get_labor_names();
                    @endphp

                    @foreach ($labor_types as $labor_type)
                        <option value="{{ $labor_type->id }}">
                            {{ $labor_type->labor_type }}
                        </option>
                    @endforeach

                </select>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group input-project">
                <label class="form-label fw-bold">Quantity</label>

                <input
                    type="text"
                    class="form-control"
                    name="labor_info[${x-1}][quantity]"
                    placeholder="How many of this labor type"
                    required>
            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group input-project">
                <label class="form-label fw-bold">
                    Regular Hrs / Day
                </label>

                <input
                    type="text"
                    class="form-control"
                    name="labor_info[${x-1}][hours_per_day]"
                    placeholder="Regular hours"
                    required>
            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group input-project">
                <label class="form-label fw-bold">
                    Overtime / Day
                </label>

                <input
                    type="text"
                    class="form-control"
                    name="labor_info[${x-1}][overtime_per_day]"
                    placeholder="Overtime hours">
            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group input-project">
                <label class="form-label fw-bold">
                    Double Time / Day
                </label>

                <input
                    type="text"
                    class="form-control"
                    name="labor_info[${x-1}][doubletime_per_day]"
                    placeholder="Double time">
            </div>
        </div>

    </div>

    <div class="mt-3">
        <button
            type="button"
            class="btn btn-danger btn-sm remove">

            <i class="fa fa-trash me-1"></i>
            Remove
        </button>
    </div>

    <hr class="my-4">

</div>