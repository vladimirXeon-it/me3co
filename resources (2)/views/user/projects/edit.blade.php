@extends('user.layouts.app')

@section('title', 'Edit Project')

@section('content')
    <section class="pt-pb-100">
        <div class="container">
            <div class="row">
                <div class="col-lg-10 offset-lg-1 col-md-10 offset-md-1 col-sm-12 col-xs-12 col-12">
                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 col-12">
                            <div class="inner-tab-opt">
                                <ul class="nav nav-tabs" id="myTab" role="tablist">

                                    <li role="presentation" class="nav-item">
                                        <a href="#step1" class="nav-link active" data-bs-toggle="tab" aria-controls="step1"
                                            role="tab" title="Step 1" aria-selected="false">Project Information</a>
                                    </li>

                                    <li role="presentation" class="nav-item">
                                        <a href="#step2" class="nav-link" data-bs-toggle="tab" aria-controls="step2"
                                            role="tab" title="Step 2" aria-selected="false">Project Materials</a>
                                    </li>
                                    <li role="presentation" class="nav-item">
                                        <a href="#step3" class="nav-link" data-bs-toggle="tab" aria-controls="step3"
                                            role="tab" title="Step 3" aria-selected="false">Project Crew</a>
                                    </li>
                                    <li role="presentation" class="nav-item">
                                        <a href="#step4" class="nav-link" data-bs-toggle="tab" aria-controls="step4"
                                            role="tab" title="Step 4" aria-selected="false">Project Equipment</a>
                                    </li>
                                    <li role="presentation" class="nav-item">
                                        <a href="#step5" class="nav-link" data-bs-toggle="tab" aria-controls="step5"
                                            role="tab" title="Step 5" aria-selected="true">Other Project Information</a>
                                    </li>
                                </ul>

                                <form role="form" method="POST"
                                    action="{{ route('project.update', ['id' => $project->id]) }}">
                                    @csrf()
                                    <div class="tab-content">
                                        <div class="tab-pane active" role="tabpanel" id="step1">
                                            <div class="text-center">
                                                <h3 class="text-dark text-center font-24 fw-bold line-height-lg">Project
                                                    Information</h3>
                                            </div>
                                            <div class="row">
                                                <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                                    <div class="form-group">
                                                        <label>Project Name:<span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control" name="name"
                                                            value="{{ $project->name }}" required>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                                    <div class="form-group">
                                                        <label>Estimator assigned:</label>
                                                        <select class="form-control" name="estimator">
                                                            <option value="" hidden>-- Select --</option>

                                                            @php
                                                                $contacts = get_user_contacts()
                                                            @endphp

                                                            @foreach ($contacts as $contact)
                                                                <option value="{{ $contact->id }}" @if($contact->id == $project->estimator) selected @endif>{{ $contact->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                    <div class="form-group">
                                                        <label>Address:<span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control" name="address"
                                                            value="{{ $project->address }}" required>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                                    <div class="form-group">
                                                        <label>City:</label>
                                                        <input type="text" class="form-control" name="city"
                                                            value="{{ $project->city }}">
                                                    </div>
                                                </div>
                                                <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                                    <div class="form-group">
                                                        <label>State:</label>
                                                        <input type="text" class="form-control" name="state"
                                                            value="{{ $project->state }}">
                                                    </div>
                                                </div>
                                                <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                                    <div class="form-group">
                                                        <label>Postal Code:</label>
                                                        <input type="text" class="form-control" name="zip"
                                                            value="{{ $project->zip }}">
                                                    </div>
                                                </div>
                                                <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                                    <div class="form-group">
                                                        <label>Country:</label>
                                                        <input type="text" class="form-control" name="country"
                                                            value="{{ $project->country }}">
                                                    </div>
                                                </div>
                                                <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                                    <div class="form-group">
                                                        <label>Customer:</label>
                                                        <select class="form-control" name="customer">
                                                            <option value="" hidden>-- Select --</option>

                                                            @php
                                                                $contacts = get_user_contacts()
                                                            @endphp

                                                            @foreach ($contacts as $contact)
                                                                <option value="{{ $contact->id }}" @if($contact->id == $project->customer) selected @endif>{{ $contact->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                                    <div class="form-group">
                                                        <label>Bid Number:</label>
                                                        <input type="text" class="form-control" name="bid_number"
                                                            value="{{ $project->bid_number }}" readonly>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                                    <div class="form-group">
                                                        <label>Bid Date:<span class="text-danger">*</span></label>
                                                        <input type="date" class="form-control" name="bid_date"
                                                            value="{{ $project->bid_date }}" required>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                                    <div class="form-group">
                                                        <label>Bid Time:<span class="text-danger">*</span></label>
                                                        <input type="time" class="form-control" name="bid_time"
                                                            value="{{ $project->bid_time }}">
                                                    </div>
                                                </div>
                                            </div>
                                            <button type="button" class="btn btn-primary next-step"
                                                id="n-btn1">Continue</button>
                                        </div>
                                        <div class="tab-pane" role="tabpanel" id="step2">
                                            <div class="text-center">
                                                <h3 class="text-dark text-center font-24 fw-bold line-height-lg">Project
                                                    Materials</h3>
                                            </div>
                                            <div class="row">
                                                {{-- @php
                                                    $materials = json_decode($project->materials);
                                                    $material_divisions = get_material_divisions();
                                                    $all_materials = get_materials();
                                                @endphp
                                                @foreach ($materials as $key => $material)
                                                    <div class="form-group row">
                                                        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">

                                                            <select name="materials[{{ $key }}][division]"
                                                                class="form-control material_divisions">
                                                                <option value="">Select</option>
                                                                @foreach ($material_divisions as $material_division)
                                                                    <option value="{{ $material_division->name }}"
                                                                        @if ($material->division == $material_division->name) selected @endif()>
                                                                        {{ $material_division->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-sm-6">
                                                            <select class="form-control"
                                                                name="materials[{{ $key }}][material]">
                                                                <option value="">Select</option>
                                                                @foreach ($all_materials as $all_material)
                                                                    <option value="{{ $all_material->name }}"
                                                                        @if ($material->material == $all_material->name) selected @endif()>
                                                                        {{ $all_material->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                @endforeach --}}
                                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                    <div class="form-group">
                                                        @php
                                                            $materials = json_decode($project->materials);
                                                            $all_materials = get_materials();
                                                        @endphp
                                                        <label>Project Materials:</label>
                                                        <select class="test form-control hidden" multiple="multiple"
                                                            name="materials[]">
                                                            <option value="">Select</option>
                                                            @foreach ($all_materials as $material)
                                                                <option value="{{ $material->id }}"
                                                                    @if (@in_array($material->id, $materials ?? [])) selected @endif>
                                                                    {{ $material->name }}
                                                                </option>
                                                            @endforeach()
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <button type="button" class="btn btn-secondary prev-step"
                                                id="p-btn1">Previous</button>
                                            <button type="button" class="btn btn-primary next-step"
                                                id="n-btn2">Continue</button>
                                        </div>
                                        <div class="tab-pane" role="tabpanel" id="step3">
                                            <div class="text-center">
                                                <h3 class="text-dark text-center font-24 fw-bold line-height-lg">Project
                                                    Crew</h3>
                                            </div>
                                            <div class="row">
                                                <div class="col-12">
                                                    @php
                                                        $all_crews = get_crews();
                                                        $crews = json_decode($project->crews);
                                                    @endphp

                                                    <div class="form-group">
                                                        <label>Project Crews:</label>
                                                        <select class="test form-control hidden"
                                                            name="crews[]">
                                                            <option value="" hidden="">Select</option>
                                                            @foreach ($all_crews as $crew)
                                                                <option value="{{ $crew->id }}"
                                                                    @if (@in_array($crew->id, $crews ?? [])) selected @endif>
                                                                    {{ $crew->name }}
                                                                </option>
                                                            @endforeach()
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <button type="button" class="btn btn-secondary prev-step"
                                                id="p-btn2">Previous</button>

                                            <button type="button" class="btn btn-primary next-step"
                                                id="n-btn3">Continue</button>
                                        </div>
                                        <div class="tab-pane" role="tabpanel" id="step4">
                                            <div class="text-center">
                                                <h3 class="text-dark text-center font-24 fw-bold line-height-lg">Project
                                                    Equipment</h3>
                                            </div>
                                            <div class="row">
                                                <div class="col-12">
                                                    @php
                                                        $equipments = json_decode($project->equipments);
                                                        $all_equipments = get_user_equipments();
                                                    @endphp

                                                    <div class="form-group">
                                                        <label>Project Equipments:</label>
                                                        <select class="test form-control hidden" multiple="multiple"
                                                            name="equipments[]">
                                                            <option value="">Select</option>
                                                            @foreach ($all_equipments as $equipment)
                                                                <option value="{{ $equipment->id }}"
                                                                    @if (@in_array($equipment->id, $equipments ?? [])) selected @endif>
                                                                    {{ $equipment->name }}
                                                                </option>
                                                            @endforeach()
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <button type="button" class="btn btn-secondary prev-step"
                                                id="p-btn3">Previous</button>

                                            <button type="button" class="btn btn-primary next-step"
                                                id="n-btn4">Continue</button>
                                        </div>
                                        <div class="tab-pane" role="tabpanel" id="step5">
                                            <div class="text-center">
                                                <h3 class="text-dark text-center font-24 fw-bold line-height-lg">Other
                                                    Project Cost</h3>
                                            </div>
                                            <div class="row">
                                                @php
                                                    $items = json_decode($project->items);
                                                @endphp
                                                <div class="item-field">
                                                 @if (!empty($items))
                                                    @foreach ($items as $key => $item)
                                                        <div class="form-group row">
                                                            <div class="col-sm-5">
                                                                <input type="text" class="form-control"
                                                                    name="items[{{ $key }}][item]"
                                                                    placeholder="Item" value="{{ $item->item }}">
                                                            </div>
                                                            <div class="col-sm-5">
                                                                <input type="number" step="0.001" class="form-control"
                                                                    placeholder="$"
                                                                    name="items[{{ $key }}][cost]"
                                                                    value="{{ $item->cost }}">
                                                            </div>
                                                            @if ($key > 0)
                                                                <div class="col-sm-2">
                                                                    <button type="button"
                                                                        class="btn btn-danger item-remove">
                                                                        <i class="fa fa-trash"></i>
                                                                    </button>
                                                                </div>
                                                            @endif()
                                                        </div>
                                                    @endforeach
                                                @else
                                                    <p>No items found.</p>
                                                @endif
                                                </div>
                                                <button type="button" class="btn col-md-2 col-6 btn-warning item-add">Add More</button>

                                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                    <div class="form-group">
                                                        <h5>Other Project Information</h5>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label>Adjust for weather conditions(%):</label>
                                                        <input type="number" class="form-control"
                                                            value="{{ $project->weather_adjustment }}" placeholder="%"
                                                            name="weather_adjustment">
                                                    </div>
                                                </div>
                                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                    <div class="form-group">
                                                        <h5>Project Oh and profit</h5>
                                                    </div>
                                                </div>
                                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                    <div class="form-group">
                                                        <label>Materials</label>
                                                    </div>
                                                    @php
                                                        $material_profit_info = json_decode($project->material_profit_info);
                                                    @endphp
                                                </div>
                                                <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                                    <div class="form-group">
                                                        <label>Oh:</label>
                                                        <input type="text" placeholder="%" class="form-control"
                                                            name="material_profit_info[oh]"
                                                            value="{{ $material_profit_info->oh }}">
                                                    </div>
                                                </div>
                                                <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                                    <div class="form-group">
                                                        <label>Profit:</label>
                                                        <input type="text" placeholder="%" class="form-control"
                                                            name="material_profit_info[profit]"
                                                            value="{{ $material_profit_info->profit }}">
                                                    </div>
                                                </div>
                                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                    <div class="form-group">
                                                        <label>Labour</label>
                                                        @php
                                                            $labor_profit_info = json_decode($project->labor_profit_info);
                                                        @endphp
                                                    </div>
                                                </div>
                                                <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                                    <div class="form-group">
                                                        <label>Oh:</label>
                                                        <input type="text" placeholder="%" class="form-control"
                                                            name="labor_profit_info[oh]"
                                                            value="{{ $labor_profit_info->oh }}">
                                                    </div>
                                                </div>
                                                <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                                    <div class="form-group">
                                                        <label>Profit:</label>
                                                        <input type="text" placeholder="%" class="form-control"
                                                            name="labor_profit_info[profit]"
                                                            value="{{ $labor_profit_info->profit }}">
                                                    </div>
                                                </div>
                                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                    <div class="form-group">
                                                        <label>Equipment</label>
                                                        @php
                                                            $equipment_profit_info = json_decode($project->equipment_profit_info);
                                                        @endphp
                                                    </div>
                                                </div>
                                                <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                                    <div class="form-group">
                                                        <label>Oh:</label>
                                                        <input type="text" placeholder="%" class="form-control"
                                                            name="equipment_profit_info[oh]"
                                                            value="{{ $equipment_profit_info->oh }}">
                                                    </div>
                                                </div>
                                                <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                                    <div class="form-group">
                                                        <label>Profit:</label>
                                                        <input type="text" placeholder="%" class="form-control"
                                                            name="equipment_profit_info[profit]"
                                                            value="{{ $equipment_profit_info->profit }}">
                                                    </div>
                                                </div>
                                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                    <div class="form-group">
                                                        <label>Subcontractor</label>
                                                        @php
                                                            $subcontractor_profit_info = json_decode($project->subcontractor_profit_info);
                                                        @endphp
                                                    </div>
                                                </div>
                                                <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                                    <div class="form-group">
                                                        <label>Oh:</label>
                                                        <input type="text" placeholder="%" class="form-control"
                                                            name="subcontractor_profit_info[oh]"
                                                            value="{{ $subcontractor_profit_info->oh }}">
                                                    </div>
                                                </div>
                                                <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                                    <div class="form-group">
                                                        <label>Profit:</label>
                                                        <input type="text" placeholder="%" class="form-control"
                                                            name="subcontractor_profit_info[profit]"
                                                            value="{{ $subcontractor_profit_info->profit }}">
                                                    </div>
                                                </div>
                                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                    <div class="form-group">
                                                        <label>Other</label>
                                                        @php
                                                            $other_profit_info = json_decode($project->other_profit_info);
                                                        @endphp
                                                    </div>
                                                </div>
                                                <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                                    <div class="form-group">
                                                        <label>Oh:</label>
                                                        <input type="text" placeholder="%" class="form-control"
                                                            name="other_profit_info[oh]"
                                                            value="{{ $other_profit_info->oh }}">
                                                    </div>
                                                </div>
                                                <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                                    <div class="form-group">
                                                        <label>Profit:</label>
                                                        <input type="text" placeholder="%" class="form-control"
                                                            name="other_profit_info[profit]"
                                                            value="{{ $other_profit_info->profit }}">
                                                    </div>
                                                </div>
                                            </div>
                                            <button type="button" class="btn btn-secondary prev-step"
                                                id="p-btn4">Previous</button>
                                            <button type="submit" class="btn btn-primary">save</button>
                                        </div>
                                        <div class="clearfix"></div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection()

@section('script')
    <script>
        $(document).ready(function(x) {
            window.fs_test = $('.test').fSelect();
        })
    </script>
    <script>
        let i = parseInt({{ $key }});
        $('.item-add').on('click', function() {
            let content = `@include('components.user.item')`
            $('.item-field').append(content)
            i++;
        });
        $('.item-field').on('click', '.item-remove', function() {
            $(this).parents('.form-group.row').remove()
            i--;
        })
    </script>
    <script>
        $("#n-btn1").click(function() {
            $('#myTab li:nth-child(2) a').tab('show');
        });
        $("#n-btn2").click(function() {
            $('#myTab li:nth-child(3) a').tab('show');
        });
        $("#n-btn3").click(function() {
            $('#myTab li:nth-child(4) a').tab('show');
        });
        $("#n-btn4").click(function() {
            $('#myTab li:nth-child(5) a').tab('show');
        });

        $("#p-btn1").click(function() {
            $('#myTab li:nth-child(1) a').tab('show');
        });
        $("#p-btn2").click(function() {
            $('#myTab li:nth-child(2) a').tab('show');
        });
        $("#p-btn3").click(function() {
            $('#myTab li:nth-child(3) a').tab('show');
        });
        $("#p-btn4").click(function() {
            $('#myTab li:nth-child(4) a').tab('show');
        });
    </script>
@endsection()
