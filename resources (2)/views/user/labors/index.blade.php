@extends('user.layouts.app')
@section('title', 'Labors')

@section('content')
    <div class="page-content-tab">
        <div class="container-fluid">
            <!-- Page-Title -->
            {{-- <div class="row">
                <div class="col-sm-12">
                    <div class="page-title-box">
                        <div class="row">
                            <div class="col align-self-center">
                                <h4 class="page-title pb-md-0">My Labor</h4>

                            </div>
                            <!--end col-->
                            <div class="col-auto align-self-center">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="javascript:void(0);">Me3Co.com</a></li>
                                    <li class="breadcrumb-item active">Labor</li>
                                </ol>
                            </div>
                            <!--end col-->
                        </div>
                        <!--end row-->
                    </div>
                    <!--end page-title-box-->
                </div>
                <!--end col-->
            </div> --}}
            <!--end row-->

    <div class="row mt-3">
        <div class="col-10">
            <div class="w-100 d-inline-block">
                <h2 class="text-black fs-4 fw-bold">My Labor @if ($project!=null)
                   for {{$project->name}}
                @endif</h2>
            </div>
        </div>
        <div class="col-2">
            <div class="float-end d-inline-block">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">Me3Co.com</a></li>
                    <li class="breadcrumb-item active">Labor</li>
                </ol>
            </div>
            <div class="float-end d-inline-block creat-project-btn">
                <a 
                    href="{{ route('labor.create') }}" 
                    class="text-14 fw-bold d-inline-block btn-create-project"
                >
                <img src="{{ asset('projects') }}/images/plus-icon.svg">
                    Create Labor
                </a>
            </div>
        </div>
    </div>
     <div class="row">
            <div class="col-12">
                <div class="w-100 d-inline-block project-table mt-4">
                    <table class="table table-striped" id="projectTableId">
                        <thead class="thead-light">
                            <tr>
                                <th scope="col" data-sortable="" style="width: 5.58912%;"><a
                                        href="#" class="dataTable-sorter">#</a></th>
                                <th scope="col" data-sortable=""><a href="#"
                                        class="dataTable-sorter">Labor
                                        Id</a></th>
                                <th scope="col" data-sortable=""><a href="#"
                                        class="dataTable-sorter">Labor
                                        Class</a></th>
                                <th scope="col" data-sortable=""><a href="#"
                                        class="dataTable-sorter">Labor
                                        Type</a></th>
                                <th scope="col" data-sortable=""><a href="#"
                                        class="dataTable-sorter">Cost
                                        Per Hour</a></th>
                                <th scope="col" data-sortable=""><a href="#"
                                        class="dataTable-sorter">Burdens</a></th>
                                <th scope="col" data-sortable=""><a href="#"
                                        class="dataTable-sorter">Total
                                        Cost</a></th>
                                <th scope="col" data-sortable=""><a href="#"
                                        class="dataTable-sorter">Actions</a></th>
                            </tr>
                            <!--end tr-->
                        </thead>
                        <tbody>
                            @php
                                $i = 1;
                            @endphp
                            @foreach ($labors as $labor)
                                <tr>
                                    <td>{{ $i++ }}</td>
                                    <td>{{ $labor->unique_id }}</td>
                                    <td>{{ $labor->labor_class->name }}</td>
                                    <td>{{ $labor->labor_type }}</td>
                                    <td>{{ $labor->cost_per_hour }}$</td>
                                    <td>
                                        @php
                                            $burdens = json_decode($labor->burdens);
                                        @endphp
                                        <ul>
                                            @foreach ($burdens as $burden)
                                                <li><b>{{ $burden->name }}</b>({{ $burden->percentage ? $burden->percentage : '0' }}%,
                                                    {{ $burden->price ? $burden->price : '0' }}$)
                                                </li>
                                            @endforeach
                                        </ul>
                                    </td>
                                    <td>{{ $labor->total_cost }}$</td>
                                    <td class="text-nowrap">
                                        <a type="button" class="delete-button"
                                            href="{{ route('labor.delete', ['id' => $labor->id]) }}"
                                        >
                                        <img class="edit-icon" src="{{ asset('projects') }}/images/delete-icon.svg">
                                        </a>
                                        <a type="button" class="edit-button"
                                            href="{{ route('labor.edit', ['id' => $labor->id]) }}"
                                        >
                                            <img class="edit-icon me-2" src="{{ asset('projects') }}/images/edit-icon.svg">
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        </div><!-- container -->
    </div>

@endsection()

@section('script')
    <script>
        $(document).ready(function() {
            $('#projectTableId').DataTable({
                "paging": true,  // Enable pagination
                "searching": true,  // Enable search
                // You can customize further options here
            });

        });
    </script>
    <script>
        let totalBurdens = 0;
        let totalBurdenPrice = 0;
        const calculateTotalCost = () => {
            let cost_per_hour = parseFloat($('.hourly_cost').val() || 0);
            let burdens = 0;
            let burdenPrice = 0;
            document.querySelectorAll('.burden_percentage').forEach(burden => {
                burdens += parseFloat(burden.value || 0);
                totalBurdens = burdens;
            });
            document.querySelectorAll('.burden_price').forEach((price) => {
                burdenPrice += parseFloat(price.value || 0);
                totalBurdenPrice = burdenPrice;
            })
            let totalCost = cost_per_hour + (cost_per_hour * totalBurdens / 100) + totalBurdenPrice;
            $('.total_cost').val(totalCost.toFixed(2))
        }
        $(document).ready(function() {
            var max_fields = 50; //maximum input boxes allowed
            var wrapper = $(".more-burdon"); //Fields wrapper
            var add_button = $(".btn-bur"); //Add button ID

            var x = 1; //initlal box count

            $(add_button).click(function(e) { //on add input button click
                e.preventDefault();
                if (x < max_fields) { //max input box allowed
                    x++; //text box increment
                    $(wrapper).append(
                        `<div class="form-group row"><div class="col-sm-4"><input type="text" name="burdens[${x+1}][name]" class="form-control" placeholder="Burden"></div><div class="col-sm-3"><input type="number" step="0.001" name="burdens[${x+1}][percentage]" class="form-control burden_percentage" placeholder="%"></div><div class="col-sm-3"><input type="number" step="0.001" name="burdens[${x+1}][price]" class="form-control burden_price" placeholder="$"></div><div class="col-md-1"><div class="form-group"><button class="btn btn-danger btn-sm remove" style=""><i class="fa fa-trash"></i></button></div></div></div>`
                    ); //add input box
                }
            });

            $(wrapper).on("click", ".remove", function(e) { //user click on remove text
                e.preventDefault();
                $(this).parents(".form-group").remove();
                x--;
                calculateTotalCost();
            })
        });
        $('#create-new').click(function() {
            $('.new-project').show();
            $('.my-project').hide();
        });
        $('#back').click(function() {
            $('.new-project').hide();
            $('.my-project').show();
        });

        $(document).ready(function() {
            $('.hourly_cost').on('input', function() {
                calculateTotalCost();
            })
            $('body').on('input', '.burden_percentage', function() {
                calculateTotalCost();
            });
            $('body').on('input', '.burden_price', function() {
                calculateTotalCost();
            })
        })
        let importButton = document.querySelector('.import_button');
        importButton.addEventListener('click',async (e) => {
            let importItems = document.querySelectorAll('.import-item');
            let importCount = document.querySelectorAll('.import-item  input:checked');
            if(importCount.length == 0) {
                alert('Please select an Item to import!')
                return false;
            }
            for await(item of importItems) {
                let checkStatus = item.querySelector('input:checked');
                if(checkStatus) {
                    let url = item.querySelector('a').href;
                    await fetch(url);
                }
            }
            window.location.reload()
        })
    </script>
@endsection()
