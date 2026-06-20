@props([
    'id',
    'title' => 'Modal',
    'formTitle' => null,
    'action' => '#',
    'method' => 'POST',
    'submitText' => 'Save',
    'cancelText' => 'Cancel',
    'boxClass' => '',
    'innerClass' => '',
    'loading' => true,
])

<div
    id="{{ $id }}"
    class="panel-modal-overlay hidden"
    onclick="closePanelModalOnBackdrop(event, '{{ $id }}')"
>
    <div class="panel-modal-box {{ $boxClass }}">

        {{-- header --}}
        <div class="panel-modal-header">
            <h2 class="panel-modal-title">
                {{ $title }}
            </h2>

            <div class="panel-modal-progress-track">
                <div class="panel-modal-progress-fill" data-progress-bar></div>
            </div>
        </div>

        {{-- form --}}
<form
    method="POST"
    action="{{ $action }}"
    class="panel-modal-form"
    data-panel-form
    @if ($loading) data-loading="true" @endif
>
            @csrf

            @if (! in_array(strtoupper($method), ['GET', 'POST']))
                @method($method)
            @endif

            <div class="panel-form-inner {{ $innerClass }}">
                @if ($formTitle)
                    <h3 class="panel-form-title">
                        {{ $formTitle }}
                    </h3>
                @endif

                {{ $slot }}
            </div>

            {{-- footer --}}
            <div class="panel-modal-footer">
                <button
                    type="button"
                    class="panel-btn-cancel"
                    onclick="closePanelModal('{{ $id }}')"
                >
                    {{ $cancelText }}
                </button>

                <button
                    type="submit"
                    class="panel-btn-submit"
                >
                    {{ $submitText }}
                </button>
            </div>
        </form>
    </div>
</div>