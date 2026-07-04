@extends('user.layouts.app')
@section('title', 'Labors')

@section('content')
    <div class="page-content-tab d-flex flex-column" style="background-color: #f8f9fa; height: calc(100vh - 65px); min-height: 0; overflow: hidden; padding: 15px 12px;">
        <div class="container-fluid d-flex flex-column flex-grow-1 p-0" style="max-width: 100%; height: 100%; min-height: 0;">
            
            <div class="project-page-header flex-shrink-0 mb-2">
                <div>
                    <h1 class="fw-bold m-0" style="font-size: 24px; color: #0f172a;">Labors</h1>
                </div>
                <div class="project-page-actions">
                    <div class="creat-project-btn">
                        <a href="{{ route('labor.create') }}"
                            class="btn btn-primary fw-bold d-flex align-items-center gap-2 px-3 py-2"
                            style="border-radius: 6px; background-color: #0052ff; border: none; font-size: 13px;">
                                <i class="fa fa-plus"></i>
                                Create New Labor
                        </a>
                    </div>
                </div>
            </div>

            <div class="card me3co-list-card border-0 shadow-sm mb-2 p-3"
                style="border-radius:16px;">
                <div class="card-body p-0 ">
                
                    <div class="me3co-table-wrapper flex-grow-1">
                        <table class="table align-middle me3co-table mb-0" id="projectTableId">
                            <thead>
                                <tr>
                                    <th scope="col" style="width: 50px; background-color: #ffffff; font-size: 13px;">#</th>
                                    <th scope="col" style="background-color: #ffffff; font-size: 13px;">Labor Id</th>
                                    <th scope="col" style="background-color: #ffffff; font-size: 13px;">Labor Class</th>
                                    <th scope="col" style="background-color: #ffffff; font-size: 13px;">Labor Type</th>
                                    <th scope="col" style="background-color: #ffffff; font-size: 13px;">Cost Per Hour</th>
                                    <th scope="col" style="background-color: #ffffff; font-size: 13px;">Burdens</th>
                                    <th scope="col" style="background-color: #ffffff; font-size: 13px;">Total Cost</th>
                                    <th scope="col" class="text-end pe-3" style="background-color: #ffffff; font-size: 13px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $i = 1; @endphp
                                @foreach ($labors as $labor)
                                    <tr>
                                        <td class="fw-bold text-muted ps-2" style="font-size: 13px;">{{ $i++ }}</td>
                                        <td class="fw-semibold text-dark" style="font-size: 13px;">{{ $labor->unique_id }}</td>
                                        <td style="font-size: 13px;">{{ $labor->labor_class->name }}</td>
                                        <td style="font-size: 13px;"><span class="badge bg-light text-dark border px-2 py-1">{{ $labor->labor_type }}</span></td>
                                        <td class="fw-medium" style="font-size: 13px;">{{ number_format($labor->cost_per_hour, 2) }}$</td>
                                        <td>
                                            @php $burdens = json_decode($labor->burdens); @endphp
                                            <div class="d-flex flex-column gap-1">
                                                @foreach($burdens as $burden)
                                                    <small class="text-muted">
                                                        <strong class="text-dark">{{ $burden->name }}</strong>
                                                        ({{ $burden->percentage ?: 0 }}%, {{ $burden->price ?: 0 }}$)
                                                    </small>
                                                @endforeach
                                            </div>
                                        </td>
                                        <td class="fw-bold text-dark" style="font-size: 13px;">{{ number_format($labor->total_cost, 2) }}$</td>
                                        <td class="text-end" style="width:80px;">
                                            <div class="dropdown">
                                                <button class="btn btn-light btn-sm border text-muted px-2"
                                                    type="button"
                                                    data-bs-toggle="dropdown"
                                                    aria-expanded="false">
                                                    <i class="fa fa-ellipsis-v"></i>
                                                </button>

                                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                                    <li>
                                                        <a class="dropdown-item"
                                                            href="{{ route('labor.edit', ['id' => $labor->id]) }}">
                                                            <i class="fa fa-pencil me-2 text-primary"></i>
                                                            Edit
                                                        </a>
                                                    </li>

                                                    <li>
                                                        <a class="dropdown-item text-danger"
                                                            href="{{ route('labor.delete', ['id' => $labor->id]) }}"
                                                            onclick="return confirm('Are you sure you want to delete this labor?')">
                                                            <i class="fa fa-trash me-2"></i>
                                                            Delete
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection()

@section('script')
    <script>
        function goBack() {
            window.location.href = "/project";
        }

        // Función global de cálculo de costos
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
            // 1. Inicialización ÚNICA de DataTables corrigiendo duplicados
            /*if ($.fn.DataTable.isDataTable('#projectTableId')) {
                $('#projectTableId').DataTable().destroy();
            }*/

            $('#projectTableId').DataTable({
                paging: true,
                searching: true,
                ordering: true,
                info: true,
                lengthChange: true,

                pageLength: 10,
                lengthMenu: [10, 25, 50, 100],

                scrollY: 'calc(100vh - 430px)',
                scrollCollapse: false,

                dom:
                    '<"d-flex justify-content-between align-items-center mb-3"l f>' +
                    't' +
                    '<"d-flex justify-content-between align-items-center mt-3"i p>',

                language: {
                    search: "",
                    searchPlaceholder: "Search labor...",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    paginate: {
                        previous: "Previous",
                        next: "Next"
                    }
                }
            });

            $('.dataTables_filter input')
                .addClass('form-control form-control-sm me3co-search');

            $('.dataTables_filter label')
                .contents()
                .filter(function () {
                    return this.nodeType === 3;
                })
                .remove();

            // 2. Lógica para añadir inputs dinámicos de Burdens
            var max_fields = 50; 
            var wrapper = $(".more-burdon"); 
            var add_button = $(".btn-bur"); 
            var x = 1; 

            $(add_button).click(function(e) {
                e.preventDefault();
                if (x < max_fields) { 
                    x++; 
                    $(wrapper).append(
                        `<div class="form-group row"><div class="col-sm-4"><input type="text" name="burdens[${x+1}][name]" class="form-control" placeholder="Burden"></div><div class="col-sm-3"><input type="number" step="0.001" name="burdens[${x+1}][percentage]" class="form-control burden_percentage" placeholder="%"></div><div class="col-sm-3"><input type="number" step="0.001" name="burdens[${x+1}][price]" class="form-control burden_price" placeholder="$"></div><div class="col-md-1"><div class="form-group"><button class="btn btn-danger btn-sm remove"><i class="fa fa-trash"></i></button></div></div></div>`
                    ); 
                }
            });

            $(wrapper).on("click", ".remove", function(e) {
                e.preventDefault();
                $(this).parents(".form-group").remove();
                x--;
                calculateTotalCost();
            });

            // 3. Listeners para el cálculo automático en tiempo real
            $('.hourly_cost').on('input', function() {
                calculateTotalCost();
            });
            $('body').on('input', '.burden_percentage', function() {
                calculateTotalCost();
            });
            $('body').on('input', '.burden_price', function() {
                calculateTotalCost();
            });

            // Control de vistas manuales secundarias si existen
            $('#create-new').click(function() {
                $('.new-project').show();
                $('.my-project').hide();
            });
            $('#back').click(function() {
                $('.new-project').hide();
                $('.my-project').show();
            });

            // Listener asíncrono para importación
            let importButton = document.querySelector('.import_button');
            if(importButton) {
                importButton.addEventListener('click', async (e) => {
                    let importItems = document.querySelectorAll('.import-item');
                    let importCount = document.querySelectorAll('.import-item input:checked');
                    if(importCount.length == 0) {
                        alert('Please select an Item to import!');
                        return false;
                    }
                    for await(item of importItems) {
                        let checkStatus = item.querySelector('input:checked');
                        if(checkStatus) {
                            let url = item.querySelector('a').href;
                            await fetch(url);
                        }
                    }
                    window.location.reload();
                });
            }
        });
    </script>
@endsection()