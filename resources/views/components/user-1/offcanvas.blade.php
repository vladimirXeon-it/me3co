<div class="offcanvas offcanvas-end" tabindex="-1" id="Appearance" aria-labelledby="AppearanceLabel">
    <div class="offcanvas-header border-bottom">
        <h5 class="m-0 font-14" id="AppearanceLabel">Settings</h5>
        <button type="button" class="btn-close text-reset p-0 m-0 align-self-center" data-bs-dismiss="offcanvas"
            aria-label="Close"></button>
    </div>
    <form method="POST" action="{{ route('settings.update') }}" class="offcanvas-body" id="setting-form">
        @php
            $settings = get_user_settings();
        @endphp
        @csrf()
        <h6 class="d-none">Account Settings</h6>
        <div class="p-2 text-start mt-3 d-none">
            <div class="form-check form-switch mb-2">
                <input class="form-check-input setting-switch" type="checkbox" id="settings-switch1" name="auto_update"
                    @if ($settings->auto_update) checked @endif()>
                <label class="form-check-label" for="settings-switch1">Auto updates</label>
            </div>
            <!--end form-switch-->
            <div class="form-check form-switch mb-2">
                <input class="form-check-input setting-switch" type="checkbox" id="settings-switch2" value="location"
                    name="location" @if ($settings->location) checked @endif()>
                <label class="form-check-label" for="settings-switch2">Location Permission</label>
            </div>
            <!--end form-switch-->

            <!--end form-switch-->
        </div>
        <!--end /div-->
        <h6 class="d-none">General Settings</h6>
        <div class="p-2 text-start mt-3 d-none">
            <div class="form-check form-switch mb-2">
                <input class="form-check-input setting-switch" type="checkbox" id="settings-switch4" name="status"
                    @if ($settings->status) checked @endif()>
                <label class="form-check-label" for="settings-switch4">Show me Online</label>
            </div>
            <!--end form-switch-->
            <div class="form-check form-switch">
                <input class="form-check-input setting-switch" type="checkbox" id="settings-switch6" name="notification"
                    value="notification" @if ($settings->notification) checked @endif()>
                <label class="form-check-label" for="settings-switch6">Notifications Popup</label>
            </div>
            <!--end form-switch-->
        </div>
        <h6>Measurement Unit</h6>
        <div class="p-2 text-start mt-3">
            <!--end form-switch-->
            <div class="form-check form-switch">
                <input class="form-check-input setting-switch" type="checkbox" id="settings-switch7" name="measurement_system"
                    value="sytem" @if ($settings->measurement_system == 1) checked @endif()>
                <label class="form-check-label" for="settings-switch7"></label>
            </div>
            <!--end form-switch-->
        </div>
        <!--end /div-->
    </form>
    <!--end offcanvas-body-->
</div>
