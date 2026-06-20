@php
    $type = null;
    $message = null;

    if (session('success')) {
        $type = 'success';
        $message = session('success');
    } elseif (session('error')) {
        $type = 'error';
        $message = session('error');
    } elseif (session('warning')) {
        $type = 'warning';
        $message = session('warning');
    } elseif ($errors->any()) {
        $type = 'error';
        $message = 'Ada input yang belum valid. Coba cek lagi form-nya.';
    }
@endphp

<div
    x-data="{
        show: @js($message !== null),
        type: @js($type),
        message: @js($message),
        close() { this.show = false }
    }"
    x-init="if (show) setTimeout(() => show = false, 3500)"
    x-show="show"
    x-transition:enter="ft-enter"
    x-transition:enter-start="ft-enter-start"
    x-transition:enter-end="ft-enter-end"
    x-transition:leave="ft-leave"
    x-transition:leave-start="ft-leave-start"
    x-transition:leave-end="ft-leave-end"
    class="feedback-toast"
    :class="`feedback-toast--${type}`"
    style="display: none;"
>
    <div class="feedback-toast__icon-wrap">
        <span class="feedback-toast__icon-circle">
            {{-- Success --}}
            <svg x-show="type === 'success'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12"/>
            </svg>
            {{-- Error --}}
            <svg x-show="type === 'error'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
            {{-- Warning --}}
            <svg x-show="type === 'warning'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                <line x1="12" y1="9" x2="12" y2="13"/>
                <line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
        </span>
    </div>

    <div class="feedback-toast__body">
        <strong x-text="type === 'success' ? 'Berhasil' : type === 'error' ? 'Gagal' : 'Peringatan'"></strong>
        <p x-text="message"></p>
    </div>

    <button type="button" @click="close" class="feedback-toast__close" aria-label="Tutup notifikasi">
        &times;
    </button>
</div>