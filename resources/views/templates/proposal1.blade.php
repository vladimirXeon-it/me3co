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
                <h2 class="text-primary fw-light">Project Proposal</h2>
                <h1 class="text-primary text-xl mt-2">{{ $project->name }}</h1>
                <h2 class="text-primary fw-light mt-2">{{ date('Y/m/d') }}</h2>
            </div>
            <div class="page p-4" >
                <h2 class="text-primary fw-light">{{ auth()->user()->company }}</h2>
                <div class="mt-2 w-100 letter-section">
                    <p>{{ date('Y/m/d') }}</p>
                    <b class="text-primary">Proposal submitted to:</b>
                    {{ $project->projectCustomer ? $project->projectCustomer->name : '[Customer Name]' }}
                    <br />
                    Work to be Performed at. {{ $project->name }}
                    We Hereby {{ auth()->user()->company }} propose to furnish all Materials, Equipment and perform all
                    labor necessary for
                    the
                    completion of the @foreach (get_material_divisions() as $trade){{ $trade->name }} ,@endforeach and concrete work at the above project

                    Following list generated from all materials used in the project and display it below:
                <ul style="padding: 12px">
                    @foreach ($reports as $row)
                        <li>{{ $row['name'] }} – {{ $row['class'] }}</li>
                    @endforeach
                </ul>
                General contractor shall Provide at no cost to {{ auth()->user()->company }} Electric hook up and
                power
                consumption,
                Temporary
                light Sanitary Facilities, and water hook up.
                Any alterations or deviations from the above specifications involving extra cost will be executed
                only
                upon
                written change orders and will become extra charge over and above the original estimate. All
                agreements
                contingent upon strikes, accidents, or delays beyond our control. Owner is to carry fire and tornado
                insurance.
                EXCLUDES: Winter conditions and requirements, Builder’s Risk insurance) and USER TO LIST ANY
                EXCLUSIONS
                All work is to be guaranteed and performed in accordance with the drawings and specifications
                submitted
                for the
                above project. All work to be completed in a substantial and workman like manner, all for the sum
                of.
                <p>${{ $finalReport['project_total'] }}</p>

                <h4>{{ auth()->user()->name }}</h4>
                <p>{{ auth()->user()->company }}</p>
                <p>{{ auth()->user()->phone }}</p>
                <p>{{ auth()->user()->email }}</p>

            </div>
            </div>
        </div>
    </main>

</body>

</html>
