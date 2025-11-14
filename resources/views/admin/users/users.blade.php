@extends('admin.layouts.app')
@section('title','Users')

@section('content')
  @foreach(($css_files ?? []) as $f) <link rel="stylesheet" href="{{ $f }}"> @endforeach

  <main id="main" class="main">
    <div class="pagetitle">
      <h1>Users</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Home</a></li>
          <li class="breadcrumb-item active">Users</li>
        </ol>
      </nav>
    </div>

    <section class="section">
      <div class="card"><div class="card-body pt-3">
        {!! $output !!}
      </div></div>
    </section>
  </main>

  {{-- Modal con iframe (no tocamos tu details.blade) --}}
  <div class="modal fade" id="userViewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content border-0 shadow-lg">
        <div class="modal-header bg-light">
          <h5 class="modal-title">
            <i class="fa fa-user-circle me-2"></i> User details
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                  onclick="document.getElementById('userViewIframe').src='about:blank'"></button>
        </div>
        <div class="modal-body p-0" style="height: 75vh;">
          <iframe id="userViewIframe" src="about:blank"
                  style="border:0;width:100%;height:100%;display:block;"></iframe>
        </div>
      </div>
    </div>
  </div>

  <script>
  (function(){
    const modalEl = document.getElementById('userViewModal');
    const iframe  = document.getElementById('userViewIframe');
    let modal;

    document.addEventListener('click', function(e){
      const a = e.target.closest('a');
      if (!a) return;
      const href = a.getAttribute('href') || '';
      // Botón del ojo creado por Grocery CRUD → /admin/users/{id}/view
      if (!/\/admin\/users\/\d+\/view$/.test(href)) return;

      e.preventDefault();
      if (!modal) modal = new bootstrap.Modal(modalEl, {backdrop:'static'});
      iframe.src = href;   // cargamos tu details.blade tal cual
      modal.show();
    });

    // Limpia iframe al cerrar (opcional, ya lo hace el botón)
    modalEl.addEventListener('hidden.bs.modal', ()=> { iframe.src = 'about:blank'; });
  })();
  </script>


  @foreach(($js_files ?? []) as $f) <script src="{{ $f }}"></script> @endforeach
@endsection
