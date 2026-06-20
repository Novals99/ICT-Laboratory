<div
    x-data="{ loading: false }"
    x-on:start-loading.window="loading = true"
    x-on:stop-loading.window="loading = false"
    x-show="loading"
    x-transition:enter="fl-overlay-enter"
    x-transition:enter-start="fl-overlay-enter-start"
    x-transition:enter-end="fl-overlay-enter-end"
    x-transition:leave="fl-overlay-leave"
    x-transition:leave-start="fl-overlay-leave-start"
    x-transition:leave-end="fl-overlay-leave-end"
    class="feedback-loading-overlay"
    style="display: none;"
>
    <div class="feedback-loading-card">

        {{-- Conveyor stage --}}
        <div class="fl-stage">

            {{-- Kotak aset --}}
            <div class="fl-box">
                <div class="fl-box-body">
                    <div class="fl-box-tape fl-box-tape--h"></div>
                    <div class="fl-box-tape fl-box-tape--v"></div>
                </div>
            </div>

            {{-- Tiang scanner --}}
            <div class="fl-pole">
                <div class="fl-pole-head"></div>
            </div>

            {{-- Beam scan --}}
            <div class="fl-beam"></div>

            {{-- Centang sukses --}}
            <div class="fl-check" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
            </div>

            {{-- Conveyor belt --}}
            <div class="fl-track">
                <div class="fl-belt"></div>
            </div>
            <div class="fl-leg fl-leg--l"></div>
            <div class="fl-leg fl-leg--r"></div>

        </div>

        {{-- Teks --}}
        <div class="fl-text">
            <strong>Processing</strong>
            <p>Please wait a moment...</p>
        </div>

        {{-- Dot loader --}}
        <div class="fl-dots" aria-hidden="true">
            <span class="fl-dot"></span>
            <span class="fl-dot"></span>
            <span class="fl-dot"></span>
        </div>

    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('form[data-loading="true"]').forEach(function (form) {
            form.addEventListener('submit', function () {
                window.dispatchEvent(new CustomEvent('start-loading'));
            });
        });
    });
</script>