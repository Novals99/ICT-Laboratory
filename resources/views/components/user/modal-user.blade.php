@props([
    'mode' => 'create',
    'user' => null,
    'laboratories' => collect(),
])

@php
    $isEdit = $mode === 'edit';

    $modalId = $isEdit
        ? 'edit-modal-user-' . $user->id
        : 'create-modal-user';

    $title = $isEdit ? 'Edit User' : 'Create User';

    $submitText = $isEdit ? 'Update' : 'Create';

    $action = $isEdit
        ? route('users.update', $user->id)
        : route('users.store');

    $selectedRole = old('role', $user->role ?? '');

    $selectedLabs = old(
        'lab_ids',
        $user ? $user->labs->pluck('id')->toArray() : []
    );

    $selectedStatus = old('status_user', $user->status_user ?? 1);
@endphp

<x-modal.index
    :id="$modalId"
    :title="$title"
    form-title="User Information"
    :action="$action"
    :method="$isEdit ? 'PUT' : 'POST'"
    :submit-text="$submitText"
>
    {{-- Name --}}
    <div class="panel-form-row">
        <label class="panel-form-label" for="{{ $modalId }}-name">
            Name:
        </label>

        <div class="panel-form-field">
            <input
                id="{{ $modalId }}-name"
                type="text"
                name="name"
                value="{{ old('name', $user->name ?? '') }}"
                placeholder="Enter here..."
                class="panel-form-input"
                data-progress-field
                required
            >

            @error('name')
                <p class="panel-form-error">{{ $message }}</p>
            @enderror
        </div>
    </div>

    {{-- NIM --}}
    <div class="panel-form-row">
        <label class="panel-form-label" for="{{ $modalId }}-nim">
            NIM:
        </label>

        <div class="panel-form-field">
            <input
                id="{{ $modalId }}-nim"
                type="text"
                name="nim"
                value="{{ old('nim', $user->nim ?? '') }}"
                placeholder="Enter here..."
                class="panel-form-input"
                data-progress-field
                data-validate="nim"
                maxlength="10"
                required
            >

            <p class="panel-form-live-error hidden" data-error-for="nim">
                Maximum 10 characters for NIM.
            </p>

            @error('nim')
                <p class="panel-form-error">{{ $message }}</p>
            @enderror
        </div>
    </div>

    {{-- Role --}}
    <div class="panel-form-row">
        <label class="panel-form-label">
            Role:
        </label>

        <div class="panel-form-field">
            <div class="user-role-options" data-role-group>
                @foreach (['spv inventory', 'staff'] as $role)
                    @php
                        $roleId = $modalId . '-role-' . str_replace(' ', '-', $role);
                    @endphp

                    <label
                        for="{{ $roleId }}"
                        class="user-role-option {{ $selectedRole === $role ? 'is-selected' : '' }}"
                    >
                        <input
                            id="{{ $roleId }}"
                            type="radio"
                            name="role"
                            value="{{ $role }}"
                            class="hidden"
                            data-progress-field
                            {{ $selectedRole === $role ? 'checked' : '' }}
                            required
                        >

                        <span>{{ ucwords($role) }}</span>
                    </label>
                @endforeach
            </div>

            @error('role')
                <p class="panel-form-error">{{ $message }}</p>
            @enderror
        </div>
    </div>

    {{-- Laboratory --}}
    <div class="panel-form-row">
        <label class="panel-form-label">
            Laboratory:
        </label>

        <div class="panel-form-field">
            <div class="user-lab-options">
                @forelse ($laboratories as $lab)
                    @php
                        $labId = $modalId . '-lab-' . $lab->id;
                    @endphp

                    <label
                        for="{{ $labId }}"
                        class="user-lab-option {{ in_array($lab->id, $selectedLabs) ? 'is-selected' : '' }}"
                    >
                        <input
                            id="{{ $labId }}"
                            type="checkbox"
                            name="lab_ids[]"
                            value="{{ $lab->id }}"
                            class="hidden"
                            data-progress-field
                            {{ in_array($lab->id, $selectedLabs) ? 'checked' : '' }}
                        >

                        <span>{{ $lab->lab_name }}</span>
                    </label>
                @empty
                    <p class="panel-form-help">
                        No lab data found.
                    </p>
                @endforelse
            </div>

            <p class="panel-form-help">
                Staff must select 1 lab. SPV can be left blank.
            </p>

            @error('lab_ids')
                <p class="panel-form-error">{{ $message }}</p>
            @enderror

            @error('lab_ids.*')
                <p class="panel-form-error">{{ $message }}</p>
            @enderror
        </div>
    </div>

    {{-- Username --}}
    <div class="panel-form-row">
        <label class="panel-form-label" for="{{ $modalId }}-username">
            Username:
        </label>

        <div class="panel-form-field">
            <input
                id="{{ $modalId }}-username"
                type="text"
                name="username"
                value="{{ old('username', $user->username ?? '') }}"
                placeholder="Enter here..."
                class="panel-form-input"
                data-progress-field
                required
            >

            @error('username')
                <p class="panel-form-error">{{ $message }}</p>
            @enderror
        </div>
    </div>

    {{-- Password --}}
    <div class="panel-form-row">
        <label class="panel-form-label" for="{{ $modalId }}-password">
            Password:
        </label>

        <div class="panel-form-field">
            <input
                id="{{ $modalId }}-password"
                type="password"
                name="password"
                placeholder="{{ $isEdit ? 'Leave blank if not changing...' : 'Enter here...' }}"
                class="panel-form-input"
                data-progress-field
                data-validate="password"
                minlength="6"
                {{ $isEdit ? '' : 'required' }}
            >

            <p class="panel-form-live-error hidden" data-error-for="password">
                Password must be at least 6 characters.
            </p>

            @error('password')
                <p class="panel-form-error">{{ $message }}</p>
            @enderror
        </div>
    </div>

    {{-- Email --}}
    <div class="panel-form-row">
        <label class="panel-form-label" for="{{ $modalId }}-email">
            Email:
        </label>

        <div class="panel-form-field">
            <input
                id="{{ $modalId }}-email"
                type="email"
                name="email"
                value="{{ old('email', $user->email ?? '') }}"
                placeholder="Enter here..."
                class="panel-form-input"
                data-progress-field
                data-validate="email"
                required
            >

            <p class="panel-form-live-error hidden" data-error-for="email">
                Invalid email format.
            </p>

            @error('email')
                <p class="panel-form-error">{{ $message }}</p>
            @enderror
        </div>
    </div>

    {{-- Status --}}
    <div class="panel-form-row">
        <label class="panel-form-label">
            Status:
        </label>

        <div class="panel-form-field">
            <div class="user-role-options" data-role-group>
                <label
                    for="{{ $modalId }}-status-active"
                    class="user-role-option {{ $selectedStatus == 1 ? 'is-selected' : '' }}"
                >
                    <input
                        id="{{ $modalId }}-status-active"
                        type="radio"
                        name="status_user"
                        value="1"
                        class="hidden"
                        data-progress-field
                        {{ $selectedStatus == 1 ? 'checked' : '' }}
                    >
                    <span>Active</span>
                </label>

                <label
                    for="{{ $modalId }}-status-inactive"
                    class="user-role-option {{ $selectedStatus == 0 ? 'is-selected' : '' }}"
                >
                    <input
                        id="{{ $modalId }}-status-inactive"
                        type="radio"
                        name="status_user"
                        value="0"
                        class="hidden"
                        data-progress-field
                        {{ $selectedStatus == 0 ? 'checked' : '' }}
                    >
                    <span>Non-active</span>
                </label>
            </div>

            @error('status_user')
                <p class="panel-form-error">{{ $message }}</p>
            @enderror
        </div>
    </div>
</x-modal.index>
