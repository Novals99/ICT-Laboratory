<div
x-data="{
    open: false,
    title: '',
    message: '',
    form: null,
    type: 'delete',

confirm() {
    if (this.form) {
        this.open = false;
        window.dispatchEvent(new CustomEvent('start-loading'));
        this.form.submit();
    }
}
}"
x-on:open-confirm.window="
open = true;
title = $event.detail.title;
message = $event.detail.message;
form = $event.detail.form;
type = $event.detail.type || 'delete';
"
x-show="open"
x-transition:enter="fc-overlay-enter"
x-transition:enter-start="fc-overlay-enter-start"
x-transition:enter-end="fc-overlay-enter-end"
x-transition:leave="fc-overlay-leave"
x-transition:leave-start="fc-overlay-leave-start"
x-transition:leave-end="fc-overlay-leave-end"
class="feedback-confirm-wrapper"
style="display: none;"

>

{{-- Backdrop --}}

<div class="feedback-confirm-backdrop" @click="open = false"></div>

{{-- Modal --}}
<div
    class="feedback-confirm-modal"
    x-transition:enter="fc-modal-enter"
    x-transition:enter-start="fc-modal-enter-start"
    x-transition:enter-end="fc-modal-enter-end"
    x-transition:leave="fc-modal-leave"
    x-transition:leave-start="fc-modal-leave-start"
    x-transition:leave-end="fc-modal-leave-end"
    @click.stop
>
    {{-- Icon --}}
    <div class="fc-icon-row">
        <div class="fc-icon-dot" :class="type === 'delete' ? '' : 'fc-icon-dot--restore'" aria-hidden="true">
            
            <!-- DELETE ICON -->
            <template x-if="type === 'delete'">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="3 6 5 6 21 6"/>
                    <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                    <path d="M10 11v6M14 11v6"/>
                    <path d="M9 6V4h6v2"/>
                </svg>
            </template>

            <!-- RESTORE ICON -->
            <template x-if="type === 'restore'">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="1 4 1 10 7 10" />
                    <path d="M3.51 15a9 9 0 1 0 .49-9" />
                </svg>
            </template>

        </div>
    </div>

    {{-- Text --}}
    <h3 x-text="title"></h3>
    <p x-text="message"></p>

    {{-- Actions --}}
    <div class="fc-actions">
        <button type="button" class="fc-btn fc-btn--cancel" @click="open = false">
            Cancel
        </button>

        <button
            type="button"
            class="fc-btn"
            :class="type === 'delete' ? 'fc-btn--delete' : 'fc-btn--restore'"
            @click="confirm"
        >
            <span x-text="type === 'delete' ? 'Delete' : 'Restore'"></span>
        </button>
    </div>
</div>
</div>
