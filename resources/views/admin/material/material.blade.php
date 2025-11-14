@extends('admin.layouts.app')

@section('title', 'Materials')

@section('content')
  {{-- CSS de Grocery CRUD --}}
  @if(!empty($css_files))
    @foreach ($css_files as $file)
      <link rel="stylesheet" href="{{ $file }}">
    @endforeach
  @endif

  <main id="main" class="main">
    <div class="pagetitle">
      <h1>Materials</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Home</a></li>
          <li class="breadcrumb-item active">Materials</li>
        </ol>
      </nav>
    </div>

    <section class="section">
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-body pt-3">
              {{-- Salida de Grocery CRUD --}}
              {!! $output ?? '' !!}
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>

  <!-- Modal Add/Edit Material -->
  <div class="modal fade" id="materialModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="materialModalTitle">Material</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body p-0">
          <div id="materialModalBody" class="p-3"></div>
        </div>
        <div class="modal-footer">
          <div class="me-auto text-danger small" id="materialModalErrors" style="display:none;"></div>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
          <button type="button" class="btn btn-primary" id="materialModalSubmit">Guardar</button>
        </div>
      </div>
    </div>
  </div>

  {{-- JS de Grocery CRUD --}}
  @if(!empty($js_files))
    @foreach ($js_files as $file)
      <script src="{{ $file }}"></script>
    @endforeach
  @endif

  <script>
(function () {
  // ===== Helpers de modal sin depender de jQuery =====
  const modalEl = document.getElementById('materialModal');
  let materialModal = null;

  function modalShow() {
    if (!modalEl) return;
    if (window.bootstrap && bootstrap.Modal) {
      materialModal = materialModal || new bootstrap.Modal(modalEl);
      materialModal.show();
    } else {
      console.warn('Bootstrap JS no está cargado: no puedo abrir el modal.');
    }
  }
  function modalHide() {
    if (!modalEl) return;
    if (window.bootstrap && bootstrap.Modal && materialModal) {
      materialModal.hide();
    }
  }

  const bodyEl  = document.getElementById('materialModalBody');
  const titleEl = document.getElementById('materialModalTitle');
  const errsEl  = document.getElementById('materialModalErrors');
  const btnSubmit = document.getElementById('materialModalSubmit');

  // RUTAS de tu form modal
  const FORM_CREATE_URL = @json(url('admin/material/form'));
  const FORM_EDIT_BASE  = @json(url('admin/material/form'));

  function setErrors(html) {
    if (!errsEl) return;
    errsEl.style.display = 'block';
    errsEl.innerHTML = html;
  }
  function clearErrors() {
    if (!errsEl) return;
    errsEl.style.display = 'none';
    errsEl.innerHTML = '';
  }

  function openForm(url, title) {
    clearErrors();
    if (titleEl) titleEl.textContent = title || 'Material';
    if (bodyEl) bodyEl.innerHTML = '<div class="p-4 text-center">Cargando...</div>';

    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(r => r.text())
      .then(html => {
        if (bodyEl) bodyEl.innerHTML = html;
        modalShow();
      })
      .catch(() => setErrors('No se pudo cargar el formulario.'));
  }

  function reloadCrud() { location.reload(); }

  // ============ Delegador de clicks (Add/Edit) ============
  document.addEventListener('click', function (e) {
    const el = e.target.closest('a,button');
    if (!el) return;

    const href = (el.getAttribute('href') || '').trim();
    const txt  = (el.textContent || '').trim().toLowerCase();

    // --- ADD ---
    const looksLikeAddBtn = /\b(add|añadir|crear|create)\b/.test(txt);
    const isAddLink =
      (href && /\/material\/add(\?.*)?$/i.test(href)) ||
      (href && /[?&]operation=add(&|$)/i.test(href)) ||
      (href && /[?&]action=add-form(&|$)/i.test(href)); // GC nativo

    if (isAddLink || looksLikeAddBtn) {
      e.preventDefault(); e.stopPropagation();
      openForm(FORM_CREATE_URL, 'Añadir Material');
      return;
    }

    // --- EDIT ---
    let id = null;
    if (href) {
      const m = href.match(/\/material\/edit\/(\d+)(\?.*)?$/i);
      if (m) id = m[1];

      if (!id) {
        try {
          const u = new URL(href, window.location.origin);
          if ((u.searchParams.get('action') || '').toLowerCase() === 'edit-form') {
            id = u.searchParams.get('id');
          }
          if (!id && (u.searchParams.get('operation') || '').toLowerCase() === 'edit') {
            id = u.searchParams.get('id');
          }
        } catch(_) {}
      }
    }

    const looksLikeEditBtn = /\b(edit|editar)\b/.test(txt);
    if (!id && looksLikeEditBtn) {
      const row = el.closest('tr');
      if (row) {
        const a = row.querySelector('a[href*="action=edit-form"], a[href*="/material/edit/"], a[href*="operation=edit"]');
        if (a) {
          const h2 = a.getAttribute('href') || '';
          const m2 = h2.match(/\/material\/edit\/(\d+)/i);
          if (m2) id = m2[1];
          if (!id) {
            try {
              const u2 = new URL(h2, window.location.origin);
              if ((u2.searchParams.get('action') || '').toLowerCase() === 'edit-form' ||
                  (u2.searchParams.get('operation') || '').toLowerCase() === 'edit') {
                id = u2.searchParams.get('id');
              }
            } catch(_) {}
          }
        }
      }
    }

    if (id) {
      e.preventDefault(); e.stopPropagation();
      openForm(FORM_EDIT_BASE + '/' + id, 'Editar Material');
    }
  });

  // ============ Submit del modal ============
  if (btnSubmit) {
    btnSubmit.addEventListener('click', function () {
      const form = bodyEl ? bodyEl.querySelector('form#material-form') : null;
      if (!form) return;

      clearErrors();
      const action = form.getAttribute('action');
      const method = (form.querySelector('input[name="_method"]')?.value || form.getAttribute('method') || 'POST').toUpperCase();
      const formData = new FormData(form);

      const prev = btnSubmit.innerHTML;
      btnSubmit.disabled = true;
      btnSubmit.innerHTML = 'Guardando...';

      fetch(action, {
        method: method,
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      })
      .then(async res => {
        if (res.ok) return true;
        if (res.status === 422) {
          const data = await res.json().catch(() => ({}));
          const errs = data.errors ? Object.values(data.errors).flat() : ['Validación fallida'];
          let html = '<ul class="mb-0">';
          errs.forEach(m => html += `<li>${m}</li>`);
          html += '</ul>';
          setErrors(html);
          return false;
        }
        setErrors('Ocurrió un error inesperado.');
        return false;
      })
      .then(ok => {
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = prev;
        if (ok) { modalHide(); reloadCrud(); }
      })
      .catch(() => {
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = prev;
        setErrors('No se pudo procesar la solicitud.');
      });
    });
  }
})();
</script>

@endsection
