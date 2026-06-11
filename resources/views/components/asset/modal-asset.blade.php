@props([
    'mode' => 'create',
    'asset' => null,
])

@php
    $isEdit = $mode === 'edit';

    $modalId = $isEdit
        ? 'edit-modal-asset-' . $asset->id
        : 'create-modal-asset';

    $title = $isEdit ? 'Edit Asset' : 'Create Stock';

    $submitText = $isEdit ? 'Update' : 'Create';

    $action = $isEdit
        ? route('asset.update', $asset->id)
        : route('asset.store');

    $selectedCategory = old('asset_category', $asset->asset_category ?? '');
@endphp

<x-modal.index
    :id="$modalId"
    :title="$title"
    :form-title="$isEdit ? 'Asset Information' : ''"
    :action="$action"
    :method="$isEdit ? 'PUT' : 'POST'"
    :submit-text="$submitText"
    box-class="asset-modal-box"
    inner-class="asset-modal-inner"
>

@if (! $isEdit)
    <input
        type="hidden"
        name="asset_category"
        id="create-asset-category"
        value="{{ old('asset_category') }}"
        data-progress-field
        required
    >

    <div class="asset-create-heading">
        <div>
            <h3 class="asset-create-title" id="create-asset-category-title">
                Choose Category
            </h3>
        </div>

        <x-button.add type="button" onclick="addAssetItem()">
            Add Item
        </x-button.add>
    </div>

    @error('asset_category')
        <p class="panel-form-error">{{ $message }}</p>
    @enderror

    <div id="assetItemsWrapper" class="asset-items-wrapper">
        <div class="asset-item-card" data-asset-item>
            {{-- TODO: sejajarin tabs sama x --}}
            <button
                type="button"
                class="asset-remove-item-btn"
                onclick="removeAssetItem(this)"
                aria-label="Remove item"
            >
                &times;
            </button>

            {{-- tabs --}}
            <div class="asset-tabs" data-asset-tabs>
                <button type="button" class="asset-tab-btn is-active" data-asset-tab-target="info">
                    Asset Information
                </button>

                <button type="button" class="asset-tab-btn" data-asset-tab-target="stock">
                    Quantity & Stock
                </button>
            </div>

            {{-- tab: asset information --}}
            <div class="asset-tab-panel is-active" data-asset-tab-panel="info">
                <div class="asset-item-grid">
                    <div class="asset-field asset-field-name">
                        <label class="asset-field-label">Asset Name:</label>
                        <input
                            type="text"
                            name="items[0][asset_name]"
                            value="{{ old('items.0.asset_name') }}"
                            placeholder="Enter here..."
                            class="panel-form-input"
                            data-progress-field
                            required
                        >

                        @error('items.0.asset_name')
                            <p class="panel-form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="asset-field asset-field-category">
                        <label class="asset-field-label">Category:</label>
                        <input
                            type="text"
                            value="Choose category first"
                            class="panel-form-input asset-category-display"
                            data-category-display
                            readonly
                        >
                    </div>

                    <div class="asset-field asset-field-entry">
                        <label class="asset-field-label">Entry Date:</label>
                        <input
                            type="date"
                            name="items[0][asset_entry]"
                            value="{{ old('items.0.asset_entry') }}"
                            class="panel-form-input"
                            data-progress-field
                        >

                        @error('items.0.asset_entry')
                            <p class="panel-form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="asset-field asset-field-source">
                        <label class="asset-field-label">Source:</label>
                        <input
                            type="text"
                            name="items[0][source]"
                            value="{{ old('items.0.source') }}"
                            placeholder="Pembelian / Hibah / Lab"
                            class="panel-form-input"
                            data-progress-field
                        >

                        @error('items.0.source')
                            <p class="panel-form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="asset-field asset-field-notes">
                        <label class="asset-field-label">Notes:</label>
                        <textarea
                            name="items[0][notes]"
                            placeholder="Optional notes..."
                            class="panel-form-input asset-form-textarea"
                            data-progress-field
                        >{{ old('items.0.notes') }}</textarea>

                        @error('items.0.notes')
                            <p class="panel-form-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- tab: quantity & stock --}}
            <div class="asset-tab-panel" data-asset-tab-panel="stock">
                <div class="asset-stock-grid">
                    <div class="asset-field">
                        <label class="asset-field-label">Total:</label>
                        <input
                            type="number"
                            name="items[0][total_asset]"
                            value="{{ old('items.0.total_asset', 0) }}"
                            placeholder="0"
                            class="panel-form-input"
                            data-progress-field
                            data-validate="asset-number"
                            data-stock-total
                            min="0"
                            required
                        >

                        @error('items.0.total_asset')
                            <p class="panel-form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="asset-field">
                        <label class="asset-field-label">Good:</label>
                        <input
                            type="number"
                            name="items[0][total_good]"
                            value="{{ old('items.0.total_good', 0) }}"
                            placeholder="0"
                            class="panel-form-input"
                            data-progress-field
                            data-validate="asset-number"
                            data-stock-good
                            min="0"
                            required
                        >

                        @error('items.0.total_good')
                            <p class="panel-form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="asset-field">
                        <label class="asset-field-label">Damaged:</label>
                        <input
                            type="number"
                            name="items[0][total_damaged]"
                            value="{{ old('items.0.total_damaged', 0) }}"
                            placeholder="0"
                            class="panel-form-input"
                            data-progress-field
                            data-validate="asset-number"
                            data-stock-damaged
                            min="0"
                            required
                        >

                        @error('items.0.total_damaged')
                            <p class="panel-form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="asset-field">
                        <label class="asset-field-label">Loss:</label>
                        <input
                            type="number"
                            name="items[0][total_loss]"
                            value="{{ old('items.0.total_loss', 0) }}"
                            placeholder="0"
                            class="panel-form-input"
                            data-progress-field
                            data-validate="asset-number"
                            min="0"
                            required
                        >

                        @error('items.0.total_loss')
                            <p class="panel-form-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <p class="asset-stock-helper">
                    Good and Damaged will auto-calculate based on Total. Loss must be entered manually.
                </p>
            </div>
        </div>
    </div>

    {{-- input form ke-n --}}
    <template id="assetItemTemplate" class="asset-items-wrapper">
        <div class="asset-item-card" data-asset-item>
            <button
                type="button"
                class="asset-remove-item-btn"
                onclick="removeAssetItem(this)"
                aria-label="Remove item"
            >
                &times;
            </button>

            <div class="asset-tabs" data-asset-tabs>
                <button type="button" class="asset-tab-btn is-active" data-asset-tab-target="info">
                    Asset Information
                </button>

                <button type="button" class="asset-tab-btn" data-asset-tab-target="stock">
                    Quantity & Stock
                </button>
            </div>

            <div class="asset-tab-panel is-active" data-asset-tab-panel="info">
                <div class="asset-item-grid">
                    <div class="asset-field asset-field-name">
                        <label class="asset-field-label">Asset Name:</label>
                        <input
                            type="text"
                            data-name="asset_name"
                            placeholder="Enter here..."
                            class="panel-form-input"
                            data-progress-field
                            required
                        >
                    </div>

                    <div class="asset-field asset-field-category">
                        <label class="asset-field-label">Category:</label>
                        <input
                            type="text"
                            value="Choose category first"
                            class="panel-form-input asset-category-display"
                            data-category-display
                            readonly
                        >
                    </div>

                    <div class="asset-field asset-field-entry">
                        <label class="asset-field-label">Entry Date:</label>
                        <input
                            type="date"
                            data-name="asset_entry"
                            class="panel-form-input"
                            data-progress-field
                        >
                    </div>

                    <div class="asset-field asset-field-source">
                        <label class="asset-field-label">Source:</label>
                        <input
                            type="text"
                            data-name="source"
                            placeholder="Pembelian / Hibah / Lab"
                            class="panel-form-input"
                            data-progress-field
                        >
                    </div>

                    <div class="asset-field asset-field-notes">
                        <label class="asset-field-label">Notes:</label>
                        <textarea
                            data-name="notes"
                            placeholder="Optional notes..."
                            class="panel-form-input asset-form-textarea"
                            data-progress-field
                        ></textarea>
                    </div>
                </div>
            </div>

            <div class="asset-tab-panel" data-asset-tab-panel="stock">
                <div class="asset-stock-grid">
                    <div class="asset-field">
                        <label class="asset-field-label">Total:</label>
                        <input
                            type="number"
                            data-name="total_asset"
                            value="0"
                            placeholder="0"
                            class="panel-form-input"
                            data-progress-field
                            data-validate="asset-number"
                            data-stock-total
                            min="0"
                            required
                        >
                    </div>

                    <div class="asset-field">
                        <label class="asset-field-label">Good:</label>
                        <input
                            type="number"
                            data-name="total_good"
                            value="0"
                            placeholder="0"
                            class="panel-form-input"
                            data-progress-field
                            data-validate="asset-number"
                            data-stock-good
                            min="0"
                            required
                        >
                    </div>

                    <div class="asset-field">
                        <label class="asset-field-label">Damaged:</label>
                        <input
                            type="number"
                            data-name="total_damaged"
                            value="0"
                            placeholder="0"
                            class="panel-form-input"
                            data-progress-field
                            data-validate="asset-number"
                            data-stock-damaged
                            min="0"
                            required
                        >
                    </div>

                    <div class="asset-field">
                        <label class="asset-field-label">Loss:</label>
                        <input
                            type="number"
                            data-name="total_loss"
                            value="0"
                            placeholder="0"
                            class="panel-form-input"
                            data-progress-field
                            data-validate="asset-number"
                            min="0"
                            required
                        >
                    </div>
                </div>

                <p class="asset-stock-helper">
                    Good and Damaged will auto-calculate based on Total. Loss must be entered manually.
                </p>
            </div>
        </div>
    </template>
    
    @else
        <div class="panel-form-row">
            <label class="panel-form-label">
                Category:
            </label>

            <div class="panel-form-field">
                <div class="asset-category-options" data-asset-category-group>
                    @foreach ([
                        'electronic' => 'Electronic',
                        'non-electronic' => 'Non-Electronic',
                        'component-pc' => 'PC Component',
                    ] as $value => $label)
                        @php
                            $categoryId = $modalId . '-category-' . $value;
                        @endphp

                        <label
                            for="{{ $categoryId }}"
                            class="asset-category-option {{ $selectedCategory === $value ? 'is-selected' : '' }}"
                        >
                            <input
                                id="{{ $categoryId }}"
                                type="radio"
                                name="asset_category"
                                value="{{ $value }}"
                                class="hidden"
                                data-progress-field
                                {{ $selectedCategory === $value ? 'checked' : '' }}
                                required
                            >

                            <span>{{ $label }}</span>
                        </label>
                    @endforeach
                </div>

                @error('asset_category')
                    <p class="panel-form-error">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="panel-form-row">
            <label class="panel-form-label" for="{{ $modalId }}-asset-name">
                Name:
            </label>

            <div class="panel-form-field">
                <input
                    id="{{ $modalId }}-asset-name"
                    type="text"
                    name="asset_name"
                    value="{{ old('asset_name', $asset->asset_name ?? '') }}"
                    placeholder="Enter here..."
                    class="panel-form-input"
                    data-progress-field
                    required
                >

                @error('asset_name')
                    <p class="panel-form-error">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="panel-form-row">
            <label class="panel-form-label" for="{{ $modalId }}-total-asset">
                Total:
            </label>

            <div class="panel-form-field">
                <input
                    id="{{ $modalId }}-total-asset"
                    type="number"
                    name="total_asset"
                    value="{{ old('total_asset', $asset->total_asset ?? 0) }}"
                    placeholder="Enter here..."
                    class="panel-form-input"
                    data-progress-field
                    data-validate="asset-number"
                    min="0"
                    required
                >

                @error('total_asset')
                    <p class="panel-form-error">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="panel-form-row">
            <label class="panel-form-label" for="{{ $modalId }}-total-good">
                Good:
            </label>

            <div class="panel-form-field">
                <input
                    id="{{ $modalId }}-total-good"
                    type="number"
                    name="total_good"
                    value="{{ old('total_good', $asset->total_good ?? 0) }}"
                    placeholder="Enter here..."
                    class="panel-form-input"
                    data-progress-field
                    data-validate="asset-number"
                    min="0"
                    required
                >

                @error('total_good')
                    <p class="panel-form-error">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="panel-form-row">
            <label class="panel-form-label" for="{{ $modalId }}-total-damaged">
                Damaged:
            </label>

            <div class="panel-form-field">
                <input
                    id="{{ $modalId }}-total-damaged"
                    type="number"
                    name="total_damaged"
                    value="{{ old('total_damaged', $asset->total_damaged ?? 0) }}"
                    placeholder="Enter here..."
                    class="panel-form-input"
                    data-progress-field
                    data-validate="asset-number"
                    min="0"
                    required
                >

                @error('total_damaged')
                    <p class="panel-form-error">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="panel-form-row">
            <label class="panel-form-label" for="{{ $modalId }}-total-loss">
                Loss:
            </label>

            <div class="panel-form-field">
                <input
                    id="{{ $modalId }}-total-loss"
                    type="number"
                    name="total_loss"
                    value="{{ old('total_loss', $asset->total_loss ?? 0) }}"
                    placeholder="Enter here..."
                    class="panel-form-input"
                    data-progress-field
                    data-validate="asset-number"
                    min="0"
                    required
                >

                @error('total_loss')
                    <p class="panel-form-error">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="panel-form-row">
            <label class="panel-form-label" for="{{ $modalId }}-asset-entry">
                Entry Date:
            </label>

            <div class="panel-form-field">
                <input
                    id="{{ $modalId }}-asset-entry"
                    type="date"
                    name="asset_entry"
                    value="{{ old('asset_entry', $asset->asset_entry ?? '') }}"
                    class="panel-form-input"
                    data-progress-field
                >

                @error('asset_entry')
                    <p class="panel-form-error">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="panel-form-row">
            <label class="panel-form-label" for="{{ $modalId }}-notes">
                Notes:
            </label>

            <div class="panel-form-field">
                <textarea
                    id="{{ $modalId }}-notes"
                    name="notes"
                    placeholder="Optional notes..."
                    class="panel-form-input asset-form-textarea"
                    data-progress-field
                >{{ old('notes') }}</textarea>

                @error('notes')
                    <p class="panel-form-error">{{ $message }}</p>
                @enderror
            </div>
        </div>
    @endif
</x-modal.index>