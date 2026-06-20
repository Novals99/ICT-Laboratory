@extends('panel.content')

@section('title', 'Admin Dashboard')

@section('content')
    <div class="panel-page-card">

        {{-- header --}}
        <div class="mb-5 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <h2 class="panel-page-title">
                User List
            </h2>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                {{-- search --}}
                <x-button.search.modul-search :action="route('users.index')" name="search" :value="request('search')" placeholder="Search..." />

                {{-- Filter --}}
                <x-button.filter :action="route('users.index')">

                    {{-- Role --}}
                    <div class="filter-section">
                        <div class="filter-section-title">Role</div>

                        @foreach (['admin', 'assistant'] as $role)
                            <label class="filter-checkbox-row">
                                <input type="checkbox" name="role[]" value="{{ $role }}"
                                    {{ in_array($role, (array) request('role', [])) ? 'checked' : '' }}
                                    style="accent-color: #111B4C;">
                                <span>{{ ucwords($role) }}</span>
                            </label>
                        @endforeach
                    </div>

                    {{-- User Status --}}
                    <div class="filter-section">
                        <div class="filter-section-title">Status User</div>

                        <label class="filter-checkbox-row">
                            <input type="checkbox" name="status[]" value="1"
                                {{ in_array('1', (array) request('status', [])) ? 'checked' : '' }}
                                style="accent-color: #111B4C;">
                            <span>Active</span>
                        </label>

                        <label class="filter-checkbox-row">
                            <input type="checkbox" name="status[]" value="0"
                                {{ in_array('0', (array) request('status', [])) ? 'checked' : '' }}
                                style="accent-color: #111B4C;">
                            <span>Non-active</span>
                        </label>
                    </div>

                </x-button.filter>

                {{-- Export --}}
                <x-button.export.export
                    menuId="usersExportMenu"
                    pdfUrl="{{ route('users.export', 'pdf') }}"
                    excelUrl="{{ route('users.export', 'excel') }}"
                    csvUrl="{{ route('users.export', 'csv') }}"
                />
                
                {{-- Add User --}}
                <x-button.add type="button" onclick="openPanelModal('create-modal-user')">
                    Add User
                </x-button.add>
            </div>
        </div>

        {{-- Table --}}
        <x-table.index>
            <thead>
                <tr>
                    <x-table.th class="w-12">
                        <x-table.checkbox id="checkAll" />
                    </x-table.th>

                    <x-table.th>Name</x-table.th>
                    <x-table.th>NIM</x-table.th>
                    <x-table.th>Role</x-table.th>
                    <x-table.th>Lab</x-table.th>
                    <x-table.th>Username</x-table.th>
                    <x-table.th>Password</x-table.th>
                    <x-table.th>Status</x-table.th>
                    <x-table.th align="center">Action</x-table.th>
                </tr>
            </thead>

            <tbody>
                @forelse ($users as $user)
                    <tr class="panel-table-row">
                        <x-table.td>
                            <x-table.checkbox name="selected_users[]" :value="$user->id" class="row-check" />
                        </x-table.td>

                        <x-table.td>
                            {{ $user->name }}
                        </x-table.td>

                        <x-table.td>
                            {{ $user->nim }}
                        </x-table.td>

                        <x-table.td>
                            {{ ucwords($user->role) }}
                        </x-table.td>

                        <x-table.td>
                            {{ $user->labs->pluck('lab_name')->join(', ') ?: '-' }}
                        </x-table.td>

                        <x-table.td>
                            {{ $user->username }}
                        </x-table.td>

                        <x-table.td>
                            ********
                        </x-table.td>

                        <x-table.td>
                            @if ($user->status_user)
                                <span class="panel-badge panel-badge-green">Active</span>
                            @else
                                <span class="panel-badge panel-badge-red">Non-active</span>
                            @endif
                        </x-table.td>

                        <x-table.td align="center">
                            <div class="flex items-center justify-center gap-1">

                                {{-- detail --}}
                                {{-- <x-table.action
                                    href="{{ route('users.show', $user->id) }}"
                                    variant="view"
                                    title="Detail"
                                >
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                         stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                </x-table.action> --}}

                                {{-- edit --}}
                                <x-table.action type="button" variant="edit" title="Edit"
                                    onclick="openPanelModal('edit-modal-user-{{ $user->id }}')">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                                    </svg>
                                </x-table.action>

                                {{-- delete --}}
                                <form
                                    id="delete-user-{{ $user->id }}"
                                    method="POST"
                                    action="{{ route('users.destroy', $user->id) }}"
                                    data-loading="true"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <x-table.action
                                        type="button"
                                        variant="delete"
                                        title="Delete"
                                        onclick="window.dispatchEvent(new CustomEvent('open-confirm', {
                                            detail: {
                                                title: 'Delete User?',
                                                message: 'Are you sure want to delete {{ $user->name }}?',
                                                formId: 'delete-user-{{ $user->id }}'
                                            }
                                        }))"
                                    >
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="3 6 5 6 21 6" />
                                            <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" />
                                            <path d="M10 11v6M14 11v6" />
                                            <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2" />
                                        </svg>
                                    </x-table.action>
                                </form>
                            </div>
                        </x-table.td>
                    </tr>
                @empty
                    <x-table.empty colspan="8" message="Belum ada data user." />
                @endforelse
            </tbody>
        </x-table.index>

        {{-- pagination --}}
        <div class="mt-5">
            {{ $users->links() }}
        </div>
    </div>

    {{-- modal Create User --}}
    <x-user.modal-user mode="create" :laboratories="$laboratories" />

    {{-- modal Edit User --}}
    @foreach ($users as $user)
        <x-user.modal-user mode="edit" :user="$user" :laboratories="$laboratories" />
    @endforeach
@endsection

@push('scripts')
    <script>
        function openPanelModal(id) {
            const modal = document.getElementById(id);

            if (!modal) {
                console.log('Modal tidak ketemu:', id);
                return;
            }

            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';

            initPanelFormModal(modal);
            initLiveValidation(modal);
        }

        function closePanelModal(id) {
            const modal = document.getElementById(id);

            if (!modal) return;

            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }

        function closePanelModalOnBackdrop(event, id) {
            if (event.target.id === id) {
                closePanelModal(id);
            }
        }

        function initPanelFormModal(modal) {
            const form = modal.querySelector('[data-panel-form]');
            const progressBar = modal.querySelector('[data-progress-bar]');

            if (!form || !progressBar) return;

            const fields = form.querySelectorAll('[data-progress-field]');

            function updateProgress() {
                let filled = 0;
                let total = 0;

                const groupedRadioNames = new Set();

                fields.forEach((field) => {
                    if (field.type === 'radio') {
                        if (groupedRadioNames.has(field.name)) return;

                        groupedRadioNames.add(field.name);
                        total++;

                        if (form.querySelector(`input[name="${field.name}"]:checked`)) {
                            filled++;
                        }

                        return;
                    }

                    if (field.type === 'checkbox') {
                        if (field.name === 'lab_ids[]') return;
                    }

                    total++;

                    if (field.value && field.value.trim() !== '') {
                        filled++;
                    }
                });

                const role = form.querySelector('input[name="role"]:checked')?.value;
                const checkedLabs = form.querySelectorAll('input[name="lab_ids[]"]:checked');

                if (role !== 'spv inventory') {
                    total++;

                    if (checkedLabs.length > 0) {
                        filled++;
                    }
                }

                const percent = total === 0 ? 0 : Math.round((filled / total) * 100);

                progressBar.style.width = percent + '%';
            }

            fields.forEach((field) => {
                field.addEventListener('input', updateProgress);
                field.addEventListener('change', updateProgress);
            });

            modal.querySelectorAll('.user-role-option').forEach((label) => {
                label.addEventListener('click', function() {
                    const group = this.closest('[data-role-group]');

                    if (!group) return;

                    group.querySelectorAll('.user-role-option').forEach(item => {
                        item.classList.remove('is-selected');
                    });

                    this.classList.add('is-selected');

                    setTimeout(updateProgress, 20);
                });
            });

            modal.querySelectorAll('.user-lab-option').forEach((label) => {
                label.addEventListener('click', function() {
                    setTimeout(() => {
                        const input = this.querySelector('input');

                        if (!input) return;

                        if (input.checked) {
                            this.classList.add('is-selected');
                        } else {
                            this.classList.remove('is-selected');
                        }

                        updateProgress();
                    }, 20);
                });
            });

            updateProgress();
        }

        function initLiveValidation(modal) {
            const form = modal.querySelector('[data-panel-form]');

            if (!form) return;

            const fields = form.querySelectorAll('[data-validate]');

            function showError(input, show) {
                const fieldName = input.dataset.validate;
                const errorText = form.querySelector(`[data-error-for="${fieldName}"]`);

                if (show) {
                    input.classList.add('is-invalid');

                    if (errorText) {
                        errorText.classList.remove('hidden');
                    }
                } else {
                    input.classList.remove('is-invalid');

                    if (errorText) {
                        errorText.classList.add('hidden');
                    }
                }
            }

            function validateInput(input) {
                const type = input.dataset.validate;
                const value = input.value.trim();

                if (type === 'nim') {
                    const invalid = value.length > 10;

                    showError(input, invalid);

                    return !invalid;
                }

                if (type === 'password') {
                    const isEditPasswordEmpty = !input.required && value.length === 0;
                    const invalid = !isEditPasswordEmpty && value.length > 0 && value.length < 6;

                    showError(input, invalid);

                    return !invalid;
                }

                if (type === 'email') {
                    const invalid = value.length > 0 && !input.checkValidity();

                    showError(input, invalid);

                    return !invalid;
                }

                return true;
            }

            fields.forEach((input) => {
                input.addEventListener('input', function() {
                    validateInput(input);
                });

                input.addEventListener('blur', function() {
                    validateInput(input);
                });
            });

            form.addEventListener('submit', function(event) {
                let formValid = true;

                fields.forEach((input) => {
                    if (!validateInput(input)) {
                        formValid = false;
                    }
                });

                if (!formValid) {
                    event.preventDefault();

                    const firstInvalid = form.querySelector('.panel-form-input.is-invalid');

                    if (firstInvalid) {
                        firstInvalid.focus();
                    }
                }
            });
        }

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                document.querySelectorAll('.panel-modal-overlay:not(.hidden)').forEach((modal) => {
                    modal.classList.add('hidden');
                });

                document.body.style.overflow = '';
            }
        });

        const checkAll = document.getElementById('checkAll');

        if (checkAll) {
            checkAll.addEventListener('change', function() {
                document.querySelectorAll('.row-check').forEach((checkbox) => {
                    checkbox.checked = this.checked;
                });
            });
        }
    </script>
@endpush
