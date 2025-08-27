@if (Session::has('message'))
    <div class="toast custom show align-items-center position-fixed border-0" role="alert" aria-live="polite"
        aria-atomic="true" data-bs-delay="3000" data-bs-autohide="true">
        <div class="d-flex">
            <div class="toast-body">
                {!! Session::get("message") !!}
            </div>
            <button type="button" class="btn-close btn-close-dark me-2 m-auto" data-bs-dismiss="toast"
                aria-label="Close"></button>
        </div>
    </div>
@endif
