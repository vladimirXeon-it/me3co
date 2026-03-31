@extends('admin.layouts.app')

@section('title', 'Labor')

@section('content')
@foreach ($css_files as $f) <link rel="stylesheet" href="{{ $f }}"> @endforeach

<main id="main" class="main">
  <div class="pagetitle">
    <h1>Labor Type</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Home</a></li>
        <li class="breadcrumb-item active">Labor Type</li>
      </ol>
    </nav>
  </div>

  <section class="section">
    <div class="row">
      <div class="col-12">
        <div class="card"><div class="card-body">
          {!! $output !!}
          @foreach ($js_files as $f) <script src="{{ $f }}"></script> @endforeach
          
          <script>
            window.BurdensManager = {
                instances: {},
                init: function(id) {
                    const wrapper = document.getElementById(id) || document.querySelector('.' + id);
                    if (!wrapper || wrapper.dataset.initialized) return;
                    
                    console.log("[BurdensManager] Initializing:", id);
                    wrapper.dataset.initialized = 'true';
                    
                    const container = wrapper.querySelector('.burdens-container');
                    const hiddenInput = wrapper.querySelector('.burdens-hidden-input');
                    const addBtn = wrapper.querySelector('.add-burden-btn');
                    
                    // Cargar datos iniciales
                    let data = [];
                    try {
                        const raw = hiddenInput.value || '[]';
                        const parsed = JSON.parse(raw);
                        data = Array.isArray(parsed) ? parsed : Object.values(parsed || {});
                    } catch(e) { console.error("JSON Error:", e); }

                    const instance = {
                        wrapper, container, hiddenInput, data,
                        render: function() {
                            container.innerHTML = '';
                            this.data.forEach((item, idx) => {
                                const row = document.createElement('div');
                                row.className = 'row burden-row align-items-center mb-2';
                                row.innerHTML = `
                                    <div class="col-sm-5"><input type="text" class="form-control form-control-sm b-name" placeholder="Name" value="${item.name||''}"></div>
                                    <div class="col-sm-3"><div class="input-group input-group-sm"><input type="number" step="0.01" class="form-control b-percentage" placeholder="%" value="${item.percentage||''}"></div></div>
                                    <div class="col-sm-3"><div class="input-group input-group-sm"><input type="number" step="0.01" class="form-control b-price" placeholder="$" value="${item.price||''}"></div></div>
                                    <div class="col-sm-1"><button type="button" class="btn btn-sm text-danger remove-btn"><i class="bi bi-trash"></i></button></div>
                                `;
                                container.appendChild(row);
                            });
                            this.sync();
                        },
                        sync: function() {
                            const rows = container.querySelectorAll('.burden-row');
                            this.data = Array.from(rows).map(row => ({
                                name: row.querySelector('.b-name').value,
                                percentage: row.querySelector('.b-percentage').value || null,
                                price: row.querySelector('.b-price').value || null
                            }));
                            hiddenInput.value = JSON.stringify(this.data);
                            window.calculateTotalCost();
                        }
                    };

                    addBtn.onclick = () => { instance.data.push({name:'', percentage:null, price:null}); instance.render(); };
                    container.oninput = () => instance.sync();
                    container.onclick = (e) => {
                        const btn = e.target.closest('.remove-btn');
                        if (btn) { btn.closest('.burden-row').remove(); instance.sync(); }
                    };

                    instance.render();
                    this.instances[id] = instance;
                }
            };

            function calculateTotalCost() {
                const hourlyInput = document.querySelector('input[name="cost_per_hour"]');
                const burdensInput = document.querySelector('input[name="burdens"]');
                const totalInput = document.querySelector('input[name="total_cost"]');
                if (!hourlyInput || !totalInput) return;

                const hourly = parseFloat(hourlyInput.value) || 0;
                let data = [];
                try { data = JSON.parse(burdensInput.value || '[]'); } catch(e) { data = []; }

                let extra = 0;
                data.forEach(b => {
                    if (b.percentage) extra += (hourly * parseFloat(b.percentage) / 100);
                    if (b.price) extra += parseFloat(b.price);
                });

                totalInput.value = (hourly + extra).toFixed(2);
            }

            // Integración global
            document.addEventListener('input', (e) => {
                if (e.target.name === 'cost_per_hour') window.calculateTotalCost();
            });
            window.calculateTotalCost = calculateTotalCost;
          </script>
        </div></div>
      </div>
    </div>
  </section>
</main>
@endsection
