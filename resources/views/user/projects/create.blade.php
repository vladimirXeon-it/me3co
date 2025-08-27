@extends('user.layouts.app')

@section('title', 'Edit Project')

@section('content')
    <section class="pt-pb-100">
        <div class="container">
            <div class="row">
                <div class="col-lg-10 offset-lg-1 col-md-10 offset-md-1 col-sm-12 col-xs-12 col-12">
                    <div class="row">
                        <div class="col-12 order-lg-1 order-md-1">
                            <div class="card">
                                <div class="card-body">       
                                    <div class="pt-3 new-project inner-tab-opt">
                                        <ul class="nav nav-tabs" id="myTab" role="tablist">
                                            <li role="presentation" class="nav-item w-50">
                                                <a href="#pro1" class="nav-link active" data-bs-toggle="tab" aria-controls="pro1"
                                                    role="tab" title="pro 1">Project Information</a>
                                            </li>
                                            <li role="presentation" class="nav-item w-50">
                                                <a href="#pro2" class="nav-link" data-bs-toggle="tab" aria-controls="pro2"
                                                    role="tab" title="pro 2">Other Project Information</a>
                                            </li>
                                        </ul>
                                        <form method="post" action="{{ route('project.create') }}">
                                            @csrf()
                                            <div class="tab-content">
                                                <div class="tab-pane active" role="tabpanel" id="pro1">
                                                    <div class="text-center">
                                                        <h3 class="text-dark text-center font-24 fw-bold line-height-lg">Project
                                                            Information</h3>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-lg-6 col-xl-4 col-md-6 col-sm-12 col-xs-12">
                                                            <div class="form-group">
                                                                <label>Project Name:<span class="text-danger">*</span></label>
                                                                <input type="text" class="form-control" name="name" required>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-6 col-xl-4 col-md-6 col-sm-12 col-xs-12">
                                                            <div class="form-group">
                                                                <label>Bid Date:<span class="text-danger">*</span></label>
                                                                <input type="date" class="form-control" name="bid_date" required>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-6 col-xl-4 col-md-6 col-sm-12 col-xs-12">
                                                            <div class="form-group">
                                                                <label>Bid Time:</label>
                                                                <input type="time" class="form-control" name="bid_time" value="14:00:00">
                                                            </div>
                                                        </div>

                                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                            <div class="form-group">
                                                                <label>Address:<span class="text-danger">*</span></label>
                                                                <input type="text" class="form-control" name="address" required>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-6 col-xl-3 col-md-6 col-sm-12 col-xs-12">
                                                            <div class="form-group">
                                                                <label>City:</label>
                                                                <input type="text" class="form-control" name="city">
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-6 col-xl-3 col-md-6 col-sm-12 col-xs-12">
                                                            <div class="form-group">
                                                                <label>State:</label>
                                                                <input type="text" class="form-control" name="state">
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-6 col-xl-3 col-md-6 col-sm-12 col-xs-12">
                                                            <div class="form-group">
                                                                <label>Postal Code:</label>
                                                                <input type="text" class="form-control" name="zip">
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-6 col-xl-3 col-md-6 col-sm-12 col-xs-12">
                                                            <div class="form-group">
                                                                <label>Country:</label>
                                                                <input type="text" class="form-control" name="country">
                                                            </div>
                                                        </div> 
                                                        <div class="col-lg-6 col-xl-3 col-md-6 col-sm-12 col-xs-12">
                                                            <div class="form-group">
                                                                <label>Currency</label>
                                                                <select class="form-control" name="estimator">
                                                                    <option value="" hidden>-- Select --</option>
        
                                                                    @php
                                                                        $currencies = [
                                                                            ['id' => 1, 'name' => 'USD'],
                                                                            ['id' => 2, 'name' => 'EUR'],
                                                                            ['id' => 3, 'name' => 'GBP'],
                                                                            ['id' => 4, 'name' => 'JPY'],
                                                                            ['id' => 5, 'name' => 'AUD']
                                                                        ];
                                                                    @endphp
        
                                                                    @foreach ($currencies as $currency)
                                                                        <option value="{{ $currency['id'] }}">{{ $currency['name'] }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>

                                                        <div class="col-lg-6 col-xl-3 col-md-6 col-sm-12 col-xs-12">
                                                            <div class="form-group">
                                                                <label>Job Walk Date<span class="text-danger">*</span></label>
                                                                <input type="date" class="form-control" name="job_walk_date" required>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-6 col-xl-3 col-md-6 col-sm-12 col-xs-12">
                                                            <div class="form-group">
                                                                <label>Job Walk Time</label>
                                                                <input type="time" class="form-control" name="job_walk_time" value="14:00:00">
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-6 col-xl-3 col-md-6 col-sm-12 col-xs-12">
                                                            <div class="form-group">
                                                                <label>RFI Due Date<span class="text-danger">*</span></label>
                                                                <input type="date" class="form-control" name="rfi_due_date" required>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-6 col-xl-3 col-md-6 col-sm-12 col-xs-12">
                                                            <div class="form-group">
                                                                <label>RFI Due Time</label>
                                                                <input type="time" class="form-control" name="rfi_due_time" value="14:00:00">
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-6 col-xl-3 col-md-6 col-sm-12 col-xs-12">
                                                            <div class="form-group">
                                                                <label>Expected Start Date</label>
                                                                <input type="date" class="form-control" name="start_date" required>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-6 col-xl-3 col-md-6 col-sm-12 col-xs-12">
                                                            <div class="form-group">
                                                                <label>Expected End Date</label>
                                                                <input type="date" class="form-control" name="end_date" required>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-6 col-xl-3 col-md-6 col-sm-12 col-xs-12">
                                                            <div class="form-group">
                                                                <label>Project Size</label>
                                                                <input type="text" class="form-control" name="size" required>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-6 col-xl-6 col-md-6 col-sm-12 col-xs-12">
                                                            <div class="form-group">
                                                                <label></label>
                                                                <select class="form-control" name="measurementUnit">
                                                                    
        
                                                                    @php
                                                                        $measurementUnits = [
                                                                        ['id' => 1, 'name' => 'Square Feet'],
                                                                        ['id' => 2, 'name' => 'Square Meters']
                                                                    ];
                                                                    @endphp
        
                                                                    @foreach ($measurementUnits as $unit)
                                                                        <option value="{{ $unit['id'] }}">{{ $unit['name'] }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-6 col-xl-6 col-md-6 col-sm-12 col-xs-12">
                                                            <div class="form-group">
                                                                <label>Architect</label>
                                                                <input type="text" class="form-control" name="architect" required>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-12 col-xl-12 col-md-12 col-sm-12 col-xs-12">
                                                    <div class="form-group">
                                                        <label>Description</label>
                                                        <textarea class="form-control" name="description" required></textarea>
                                                    </div>
                                                </div>
                                                <div class="form-group mb-2">
                                                    <label>Public</label>
                                                    </div>
                                                    <div class="public-checkbox ms-3 mb-2">
                                                    <input class="form-check-input" type="checkbox" value="1" id="checkbox_id" name="project_type">
                                                        <label class="form-check-label" for="checkbox_id">
                                                            Allow this project to be publicly shared
                                                        </label>
                                                    </div>
                                                    <label class="mb-2 d-inline-block">Budgeting</label>
                                                    <div class="form-check ms-4 mb-1">
                                                        <input class="form-check-input" type="radio" name="invitation_type" id="invitation_to_bid" value="bid" checked>
                                                        <label class="form-check-label" for="invitation_to_bid">
                                                            I want to send invitations to bid.
                                                        </label>
                                                    </div>
                                                    <div class="form-check ms-4 mb-2">
                                                        <input class="form-check-input" type="radio" name="invitation_type" id="request_for_budget" value="budgeting">
                                                        <label class="form-check-label" for="request_for_budget">
                                                            I want to send requests for budgeting.
                                                        </label>
                                                    </div>
                                                    <label class="mb-2 d-inline-block">Competitive Bidding</label>

                                                <div class="form-check ms-4 mb-1">
                                                        <input class="form-check-input" type="radio" name="project_type" id="competitive_bidding" value="competitive" checked>
                                                        <label class="form-check-label" for="competitive_bidding">
                                                            My company is competitively bidding for this project.
                                                        </label>
                                                    </div>
                                                    <div class="form-check ms-4 mb-2">
                                                        <input class="form-check-input" type="radio" name="project_type" id="negotiated_work" value="negotiated">
                                                        <label class="form-check-label" for="negotiated_work">
                                                            This project is negotiated work.
                                                        </label>
                                                    </div>
                                                    <div class="col-lg-6 col-xl-4 col-md-6 col-sm-12 col-xs-12">
                                                            <div class="form-group">
                                                                <label>Client</label>
                                                                <input type="text" class="form-control" name="client" required>
                                                            </div>
                                                        </div>
                                                            <div class="col-lg-6 col-xl-4 col-md-6 col-sm-12 col-xs-12">
                                                            <div class="form-group">
                                                                <label>Bid Due to Client Date:<span class="text-danger">*</span></label>
                                                                <input type="date" class="form-control" name="bid_to_client_date" required>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-6 col-xl-4 col-md-6 col-sm-12 col-xs-12">
                                                            <div class="form-group">
                                                                <label>Bid Due to Client Time:</label>
                                                                <input type="time" class="form-control" name="bid_to_client_time" value="14:00:00">
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-6 col-xl-4 col-md-6 col-sm-12 col-xs-12">
                                                        <div class="form-group">
                                                                <label>Account Manager</label>
                                                                <input type="text" class="form-control" name="account_manager" required>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-6 col-xl-4 col-md-6 col-sm-12 col-xs-12">
                                                            <div class="form-group">
                                                                <label>Project Value</label>
                                                                <input type="number" class="form-control" name="project_value" required
                                                                placeholder="$">
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-6 col-xl-4 col-md-6 col-sm-12 col-xs-12">
                                                            <div class="form-group">
                                                                <label>Fee Percentage</label>
                                                                <input type="number" class="form-control" name="fee_percentage" required
                                                                placeholder="%">
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-6 col-xl-4 col-md-6 col-sm-12 col-xs-12">
                                                            <div class="form-group">
                                                                <label>Market Sector</label>
                                                                <input type="text" class="form-control" name="market_sector" required>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-6 col-xl-4 col-md-6 col-sm-12 col-xs-12">
                                                            <div class="form-group">
                                                                <label>Relevant Certificates</label>
                                                                <select class="form-control" name="certificate">
                                                                    <option value="" hidden>-- Select --</option>
        
                                                                    @php
                                                                        $certificates = [
                                                                        ['id' => 1, 'name' => 'Airport Concessions Disadvantaged Business Enterprise (ACDBE)'],
                                                                        ['id' => 2, 'name' => 'Disadvantaged Business Enterprise (DBE)'],
                                                                        ['id' => 3, 'name' => 'Small Business Administration 8(a) Program'],
                                                                        ['id' => 4, 'name' => 'Economically Disabled Women Owned Small Business (EDWOSB)'],
                                                                        ['id' => 5, 'name' => 'Disabled Veteran-owned Business Enterprise (DVBE)']
                                                                        ];
                                                                    @endphp
        
                                                                    @foreach ($certificates as $certificate)
                                                                        <option value="{{ $certificate['id'] }}">{{ $certificate['name'] }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-6 col-xl-4 col-md-6 col-sm-12 col-xs-12">
                                                            <div class="form-group">
                                                                <label>Certifiying Agency</label>
                                                                <select class="form-control" name="certifiying_agency">
                                                                    <option value="" hidden>-- Select --</option>
        
                                                                    @php
                                                                        $certifiying_agencies = [
                                                                        ['id' => 1, 'name' => 'Airport Concessions Disadvantaged Business Enterprise (ACDBE)'],
                                                                        ['id' => 2, 'name' => 'Disadvantaged Business Enterprise (DBE)'],
                                                                        ['id' => 3, 'name' => 'Small Business Administration 8(a) Program'],
                                                                        ['id' => 4, 'name' => 'Economically Disabled Women Owned Small Business (EDWOSB)'],
                                                                        ['id' => 5, 'name' => 'Disabled Veteran-owned Business Enterprise (DVBE)']
                                                                    ];
                                                                    @endphp
        
                                                                    @foreach ($certifiying_agencies as $certifiying_agency)
                                                                        <option value="{{ $certifiying_agency['id'] }}">{{ $certifiying_agency['name'] }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                                <label>Notes</label>
                                                                <textarea class="form-control" name="notes" required></textarea>
                                                            </div>
                                                            <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                                            <div class="form-group">
                                                                <label>Owning Office</label>
                                                                <select class="form-control" name="owning_office">
                                                                    <option value="" hidden>-- Select --</option>
        
                                                                    @php
                                                                        $owning_offices = [
        ['id' => 1, 'name' => 'Nashville'],
        ['id' => 2, 'name' => 'Dubai']
    ];
                                                                    @endphp
        
                                                                    @foreach ($owning_offices as $owning_office)
                                                                        <option value="{{ $owning_office['id'] }}">{{ $owning_office['name'] }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <button type="button" class="btn btn-primary next-step" id="n-next">Continue</button>
                                                </div>
                                                <div class="tab-pane" role="tabpanel" id="pro2">
                                                    <div class="text-center">
                                                        <h3 class="text-dark text-center font-24 fw-bold line-height-lg">Other
                                                            Project Cost</h3>
                                                    </div>
                                                    <div class="item-field">
                                                        <div class="form-group row">
                                                            <div class="col-sm-5">
                                                                <input type="text" class="form-control" name="items[0][item]" placeholder="Item"
                                                                    >
                                                            </div>
                                                            <div class="col-sm-7">
                                                                <input type="number" step="0.001" class="form-control" name="items[0][cost]" placeholder="$">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <button type="button" class="btn btn-warning item-add">Add More</button>
        
                                                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                        <div class="form-group">
                                                            <h5>Other Project Information</h5>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="form-group">
                                                            <label>Adjust for weather conditions(%):</label>
                                                            <div class="input-group">
                                                                <input type="number" class="form-control" placeholder="%" name="weather_adjustment">
                                                                <div class="input-group-append">%</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="row">
                                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                            <div class="form-group">
                                                                <h5>Project Oh and profit</h5>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                            <div class="form-group">
                                                                <label>Materials</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                                            <div class="form-group">
                                                                <label>Oh:</label>
                                                                <div class="input-group">
                                                                    <input type="text" placeholder="%" class="form-control" name="material_profit_info[oh]" >
                                                                    <div class="input-group-append">%</div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                                            <div class="form-group">
                                                                <label>Profit:</label>
                                                                <div class="input-group">
                                                                    <input type="text" placeholder="%" class="form-control" name="material_profit_info[profit]" >
                                                                    <div class="input-group-append">%</div>
                                                                </div>                 
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                            <div class="form-group">
                                                                <label>Labour</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                                            <div class="form-group">
                                                                <label>Oh:</label>
                                                                <div class="input-group">
                                                                    <input type="text" placeholder="%" class="form-control" name="labor_profit_info[oh]" >
                                                                    <div class="input-group-append">%</div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                                            <div class="form-group">
                                                                <label>Profit:</label>
                                                                <div class="input-group">
                                                                    <input type="text" placeholder="%" class="form-control" name="labor_profit_info[profit]" >
                                                                    <div class="input-group-append">%</div>
                                                                </div>                 
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                            <div class="form-group">
                                                                <label>Equipment</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                                            <div class="form-group">
                                                                <label>Oh:</label>
                                                                <div class="input-group">
                                                                    <input type="text" placeholder="%" class="form-control" name="equipment_profit_info[oh]" >
                                                                    <div class="input-group-append">%</div>
                                                                </div>                                              
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                                            <div class="form-group">
                                                                <label>Profit:</label>
                                                                <div class="input-group">
                                                                    <input type="text" placeholder="%" class="form-control" name="equipment_profit_info[profit]" >
                                                                    <div class="input-group-append">%</div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                            <div class="form-group">
                                                                <label>Subcontractor</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                                            <div class="form-group">
                                                                <label>Oh:</label>
                                                                <div class="input-group">
                                                                    <input type="text" placeholder="%" class="form-control" name="subcontractor_profit_info[oh]" >
                                                                    <div class="input-group-append">%</div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                                            <div class="form-group">
                                                                <label>Profit:</label>
                                                                <div class="input-group">
                                                                    <input type="text" placeholder="%" class="form-control" name="subcontractor_profit_info[profit]" >
                                                                    <div class="input-group-append">%</div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                            <div class="form-group">
                                                                <label>Other</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                                            <div class="form-group">
                                                                <label>Oh:</label>
                                                                <div class="input-group">
                                                                    <input type="text" placeholder="%" class="form-control" name="other_profit_info[oh]" >
                                                                    <div class="input-group-append">%</div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                                            <div class="form-group">
                                                                <label>Profit:</label>
                                                                <div class="input-group">
                                                                    <input type="text" placeholder="%" class="form-control" name="other_profit_info[profit]" >
                                                                    <div class="input-group-append">%</div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <button type="button" class="btn btn-secondary prev-step"
                                                        id="p-prev">Previous</button>
                                                </div>
                                                <div class="clearfix"></div>
                                            </div>
                                            <div class="card-footer">
                                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                    <div class="form-group text-center">
                                                        <a href="javascript:void(0);" onclick="history.back()" class="btn btn-warning"
                                                            id="back">Back</a>
                                                        <button class="btn btn-primary">Create</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <!--end card-body-->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection()

@section('script')
    <script src="{{ asset('fronts') }}/assets/pages/projects-index.init.js"></script>
    <script>
        $("#n-next").click(function() {
            $('#myTab li:nth-child(2) a').tab('show');
        });

        $("#p-prev").click(function() {
            $('#myTab li:nth-child(1) a').tab('show');
        });
    </script>
    <script>
        function openCity(evt, cityName) {
            var i, tabcontent, tablinks;
            tabcontent = document.getElementsByClassName("tabcontent");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].style.display = "none";
            }
            tablinks = document.getElementsByClassName("tablinks");
            for (i = 0; i < tablinks.length; i++) {
                tablinks[i].className = tablinks[i].className.replace(" active", "");
            }
            document.getElementById(cityName).style.display = "block";
            evt.currentTarget.className += " active";
        }
    </script>
    <script>
        let i = 1;
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
@endsection()
