<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <link rel="icon" type="image/svg+xml" href="/vite.svg" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="info" project="{{ $id }}" token="{{ auth()->user()->remember_token }}"/>    
    <title>Project Report</title>
    <script type="module" crossorigin src="{{ asset('template1') }}/assets/index-e5f2d6cd.js"></script>
    {{-- <script src="{{ asset('template1') }}/tinymce/tinymce.min.js"></script> --}}
    <link rel="stylesheet" href="{{ asset('template1') }}/assets/index-cb41e1c8.css">
    {{-- <script>
        tinymce.init({
            selector: "main",
            width: "100%",
            height: "100vh",
            extended_valid_elements: "*[*]",
            content_css: "{{ asset('template1') }}/assets/index-99f0f912.css"
        })
    </script> --}}
</head>

<body>
    <button type="button" class="btn-pdf">
        <img src="{{ asset('template1') }}/images/pdf-file.png" />
    </button>
    <button type="button" class="btn-email">
        <img src="{{ asset('template1') }}/images/mail.svg" />
    </button>
    <main>
        <div class="page-wrapper printing" contenteditable="true">
            <div class="page cover-page">
                <h2 class="text-primary fw-light">Project Report</h2>
                <h1 class="text-primary text-xl mt-2">{{ $project->name }}</h1>
                <h2 class="text-primary fw-light mt-2">{{ date('Y/m/d') }}</h2>
            </div>
            <div class="page p-4">
                <h2 class="text-primary fw-light">Material Costing</h2>
                <table>

                    <thead>
                        <tr>
                            <th>Class</th>
                            <th>Material Id</th>
                            <th>Name</th>
                            <th>Quantity</th>
                            <th>Unitary Cost</th>
                            <th>Cost</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($reports as $row)
                            <tr>
                                <td>{{ $row['class'] }}</td>
                                <td>{{ @$row['unique_id'] }}</td>
                                <td>{{ @$row['name'] }}</td>
                                <td>{{ round($row['totalUnits'], 2) }}</td>
                                <td>${{ @$row['unitPrice'] }}</td>
                                <td>${{ $row['cost'] }}</td>
                            </tr>
                        @endforeach
                        <tr>
                            <td>TOTALS</td>
                            <td>-</td>
                            <td>-</td>
                            <td>-</td>
                            <td>-</td>
                            <td>${{ $materialTotalCost }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="page p-4">
                <h2 class="text-primary fw-light">Crew Costing</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Total Days</th>
                            <th>Cost per day</th>
                            <th>Fringe and burden benefits</th>
                            <th>Cost</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ $crewReport['crewName'] }}</td>
                            <td>{{ $crewReport['totaldays'] }}</td>
                            <td>${{ $crewReport['crewPerDay'] }}</td>
                            <td>
                                @foreach ($crewReport['burdens'] as $burden)
                                    <b>{{ $burden->name }}({{ $burden->percentage ?? 0 }}%,
                                        ${{ $burden->price ?? 0 }})</b>
                                @endforeach
                            </td>
                            <td>${{ $crewReport['crewTotal'] }}</td>
                        </tr>
                    </tbody>
                </table>

            </div>
            <div class="page p-4">

                <h2 class="text-primary fw-light">Equipements Costing</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Id</th>
                            <th>Name</th>
                            <th>Cost per day</th>
                            <th>Total Days</th>
                            <th>Cost</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($equipmentReport['reports'] as $row)
                            <tr>
                                <td>{{ $row['id'] }}</td>
                                <td>{{ $row['name'] }}</td>
                                <td>${{ $row['cost_per_day'] }}</td>
                                <td>{{ $row['days'] }}</td>
                                <td>${{ $row['cost'] }}</td>
                            </tr>
                        @endforeach
                        <tr>
                            <td>TOTALS</td>
                            <td>-</td>
                            <td>-</td>
                            <td>-</td>
                            <td>${{ $equipmentReport['totalCost'] }}</td>
                        </tr>
                    </tbody>
                </table>

            </div>
            <div class="page p-4">
                <h2 class="text-primary fw-light">TOTALS</h2>
                <table>
                    <thead>
                        <tr>
                            <th></th>
                            <th>Material</th>
                            <th>Labor</th>
                            <th>Equipment</th>
                            <th>Project Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Total</td>
                            <td>${{ $totalReport['material'] }}</td>
                            <td>${{ $totalReport['crew'] }}</td>
                            <td>${{ $totalReport['equipment'] }}</td>
                            <td>${{ $totalReport['project_total'] }}</td>
                        </tr>
                        <tr>
                            <td>Tax</td>
                            <td>${{ $totalTax['material'] }}</td>
                            <td>{{ $totalTax['crew'] }}</td>
                            <td>{{ $totalTax['equipment'] }}</td>
                            <td>{{ $totalTax['project_total'] }}</td>
                        </tr>
                        <tr>
                            <td>Subtotal</td>
                            <td>${{ $totalReport['material'] }}</td>
                            <td>${{ $totalReport['crew'] }}</td>
                            <td>${{ $totalReport['equipment'] }}</td>
                            <td>${{ $totalReport['project_total'] }}</td>
                        </tr>
                        <tr>
                            <td>OH%</td>
                            <td>${{ $ohReport['material'] }}</td>
                            <td>${{ $ohReport['crew'] }}</td>
                            <td>${{ $ohReport['equipment'] }}</td>
                            <td>${{ $ohReport['project_total'] }}</td>
                        </tr>
                        <tr>
                            <td>Profit%</td>
                            <td>${{ $profitReport['material'] }}</td>
                            <td>${{ $profitReport['crew'] }}</td>
                            <td>${{ $profitReport['equipment'] }}</td>
                            <td>${{ $profitReport['project_total'] }}</td>
                        </tr>
                        <tr>
                            <td>TOTALS</td>
                            <td>${{ $finalReport['material'] }}</td>
                            <td>${{ $finalReport['crew'] }}</td>
                            <td>${{ $finalReport['equipment'] }}</td>
                            <td>${{ $finalReport['project_total'] }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            </div>
        </div>
    </main>

</body>

</html>
