@extends('admin.layouts.app')

@section('title', 'Materials')

@section('content')

{{-- Grocery CRUD CSS --}}
@if(!empty($css_files))
    @foreach ($css_files as $file)
        <link rel="stylesheet" href="{{ $file }}">
    @endforeach
@endif

<main id="main" class="main">

    <div class="pagetitle d-flex justify-content-between align-items-center">
        <div>
            <h1>Materials</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Home</a></li>
                    <li class="breadcrumb-item active">Materials</li>
                </ol>
            </nav>
        </div>

        {{-- 🔥 Botón ADD MATERIAL --}}
        <button class="btn btn-primary" id="btnAddMaterial">
            <i class="fa fa-plus"></i> Add Material
        </button>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-12">
                <div class="card p-3">
                    {!! $output ?? '' !!}
                </div>
            </div>
        </div>
    </section>

</main>

{{-- ===========================
   BOOTSTRAP MODAL
=========================== --}}
<div class="modal fade" id="materialModal" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="materialModalTitle">Material</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body p-0">
        <div id="materialModalBody" class="p-3"></div>
      </div>

      <div class="modal-footer">
        <div class="me-auto text-danger small" id="materialModalErrors" style="display:none;"></div>
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        <button class="btn btn-primary" id="materialModalSubmit">Guardar</button>
      </div>

    </div>
  </div>
</div>

{{-- Grocery CRUD JS --}}
@if(!empty($js_files))
    @foreach ($js_files as $file)
        <script src="{{ $file }}"></script>
    @endforeach
@endif


<script>
document.addEventListener("DOMContentLoaded", () => {

    const modalEl = document.getElementById("materialModal");
    const modal = new bootstrap.Modal(modalEl);

    const bodyEl  = document.getElementById("materialModalBody");
    const titleEl = document.getElementById("materialModalTitle");
    const errsEl  = document.getElementById("materialModalErrors");
    const btnSave = document.getElementById("materialModalSubmit");

    const URL_CREATE = @json(route('admin.material.form.create'));
    const URL_EDIT   = @json(url('admin/material/form'));

    function showError(html) {
        errsEl.style.display = 'block';
        errsEl.innerHTML = html;
    }
    function clearError() {
        errsEl.style.display = 'none';
        errsEl.innerHTML = '';
    }

    // ==========================
    // Cargar formulario en modal
    // ==========================
    function openForm(url, title) {
        clearError();
        titleEl.textContent = title;
        bodyEl.innerHTML = "<div class='p-3 text-center'>Cargando...</div>";

        fetch(url, { headers: { "X-Requested-With": "XMLHttpRequest" } })
            .then(r => r.text())
            .then(html => {
                bodyEl.innerHTML = html;
                modal.show();
            })
            .catch(() => showError("No se pudo cargar el formulario."));
    }

    // ==========================
    // Guardar formulario
    // ==========================
    btnSave.addEventListener("click", () => {
        const form = bodyEl.querySelector("#material-form");
        if (!form) return;

        clearError();

        const action = form.getAttribute("action");
        const formData = new FormData(form);

        btnSave.disabled = true;
        btnSave.innerHTML = "Guardando...";

        fetch(action, {
            method: "POST",
            body: formData,
            headers: { "X-Requested-With": "XMLHttpRequest" }
        })
        .then(async res => {
            if (res.ok) return true;

            if (res.status === 422) {
                const data = await res.json();
                let html = "<ul>";
                Object.values(data.errors).flat().forEach(e => html += `<li>${e}</li>`);
                html += "</ul>";
                showError(html);
                return false;
            }

            showError("Error inesperado.");
            return false;
        })
        .then(success => {
            btnSave.disabled = false;
            btnSave.innerHTML = "Guardar";

            if (success) {
                modal.hide();
                location.reload();
            }
        })
        .catch(() => {
            btnSave.disabled = false;
            btnSave.innerHTML = "Guardar";
            showError("No se pudo enviar la solicitud.");
        });
    });

    // ==========================
    // CLICK: ADD MATERIAL
    // ==========================
    document.getElementById("btnAddMaterial").addEventListener("click", () => {
        openForm(URL_CREATE + "?modal=1", "Add Material");
    });

    // ==========================
    // CLICK: EDIT desde Grocery CRUD
    // ==========================
    document.addEventListener("click", e => {
        const a = e.target.closest("a");
        if (!a) return;

        const href = a.getAttribute("href");

        if (!href) return;

        // Detecta enlaces de edición
        if (href.includes("operation=edit") || href.includes("action=edit-form") || /\/edit\/\d+/.test(href)) {

            // intenta extraer el id
            let id = null;

            const m = href.match(/\/(\d+)(\?|$)/);
            if (m) id = m[1];

            if (!id) {
                try {
                    const urlObj = new URL(href, window.location.origin);
                    id = urlObj.searchParams.get("id");
                } catch (_) {}
            }

            if (id) {
                e.preventDefault();
                openForm(`${URL_EDIT}/${id}?modal=1`, "Edit Material");
            }
        }
    });

});
</script>

@endsection
