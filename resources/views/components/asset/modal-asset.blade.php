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
    {{-- Pengaman: select tersembunyi (TANPA name, tidak ikut submit) supaya JS lama
         yang masih mereferensikan #create-asset-category tidak error. Aman dihapus
         jika asset JS-mu sudah tidak memakainya. --}}
    <select id="create-asset-category" aria-hidden="true" tabindex="-1"
            style="display:none;">
        <option value="electronic">Electronic</option>
        <option value="non-electronic">Non-Electronic</option>
        <option value="component-pc">PC Component</option>
        <option value="pc">PC</option>
    </select>

    <div class="asset-create-heading" style="justify-content:flex-end;">
        <x-button.add type="button" onclick="addAssetItem()">
            Add Item
        </x-button.add>
    </div>

    <div id="assetItemsWrapper" class="asset-items-wrapper">
        <div class="asset-item-card" data-asset-item>
            <button
                type="button"
                class="asset-remove-item-btn"
                onclick="removeAssetItem(this)"
                aria-label="Remove item"
            >
                &times;
            </button>

            <div class="asset-info-panel">
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
                        <select
                            name="items[0][asset_category]"
                            class="panel-form-input js-asset-category"
                            data-progress-field
                            required
                        >
                            <option value="" disabled {{ old('items.0.asset_category') ? '' : 'selected' }}>Choose category...</option>
                            <option value="electronic" {{ old('items.0.asset_category') === 'electronic' ? 'selected' : '' }}>Electronic</option>
                            <option value="non-electronic" {{ old('items.0.asset_category') === 'non-electronic' ? 'selected' : '' }}>Non-Electronic</option>
                            <option value="component-pc" {{ old('items.0.asset_category') === 'component-pc' ? 'selected' : '' }}>PC Component</option>
                            <option value="pc" {{ old('items.0.asset_category') === 'pc' ? 'selected' : '' }}>PC</option>
                        </select>

                        @error('items.0.asset_category')
                            <p class="panel-form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- (#17) Component Type — tampil hanya jika PC Component --}}
                    <div class="asset-field js-component-type-field" style="display:none;">
                        <label class="asset-field-label">Component Type:</label>
                        <select name="items[0][component_type]" class="panel-form-input">
                            <option value="">— Pilih tipe —</option>
                            <option value="processor" {{ old('items.0.component_type') === 'processor' ? 'selected' : '' }}>Processor</option>
                            <option value="ram" {{ old('items.0.component_type') === 'ram' ? 'selected' : '' }}>RAM</option>
                            <option value="ssd" {{ old('items.0.component_type') === 'ssd' ? 'selected' : '' }}>SSD</option>
                            <option value="hdd" {{ old('items.0.component_type') === 'hdd' ? 'selected' : '' }}>HDD</option>
                            <option value="vga" {{ old('items.0.component_type') === 'vga' ? 'selected' : '' }}>VGA</option>
                            <option value="powersupply" {{ old('items.0.component_type') === 'powersupply' ? 'selected' : '' }}>Power Supply</option>
                            <option value="motherboard" {{ old('items.0.component_type') === 'motherboard' ? 'selected' : '' }}>Motherboard</option>
                            <option value="cpu_fan" {{ old('items.0.component_type') === 'cpu_fan' ? 'selected' : '' }}>CPU Fan</option>
                        </select>
                    </div>

                    {{-- Spesifikasi — tampil hanya untuk PC Component --}}
                    <div class="asset-field js-spec-field" style="display:none;">
                        <label class="asset-field-label">Spesifikasi:</label>
                        <input
                            type="text"
                            name="items[0][specification]"
                            value="{{ old('items.0.specification') }}"
                            placeholder="Contoh: Intel Core i5-12400F, 16GB DDR4..."
                            class="panel-form-input"
                        >
                    </div>

                    <div class="asset-field asset-field-source">
                        <label class="asset-field-label">Source <span class="text-red-500">*</span></label>
                        <select
                            name="items[0][source]"
                            class="panel-form-input"
                            data-progress-field
                            required
                        >
                            <option value="Pengadaan" {{ old('items.0.source') === 'Pengadaan' ? 'selected' : '' }}>Pengadaan</option>
                            <option value="Pembelian" {{ old('items.0.source') === 'Pembelian' ? 'selected' : '' }}>Pembelian</option>
                        </select>

                        @error('items.0.source')
                            <p class="panel-form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Kode Inventaris — disembunyikan untuk PC Component, tampil untuk kategori lain --}}
                    <div class="asset-field js-serial-field" style="grid-column: span 2;">
                        <label class="asset-field-label">Kode Inventaris:</label>
                        <div class="js-serial-list" style="display:flex; flex-direction:column; gap:6px;"></div>
                        <button type="button" class="panel-btn-secondary js-add-serial" style="margin-top:6px;">+ Tambah Kode</button>
                        <p class="panel-form-help">Boleh dikosongkan — akan ter-generate otomatis.</p>
                    </div>

                    <div class="js-total-good-wrapper" style="display:contents;">
                        <div class="asset-field">
                            <label class="asset-field-label">Total <span class="text-red-500">*</span></label>
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
                                readonly
                                style="background:var(--bg-input-readonly, #f3f4f6); cursor:not-allowed;"
                                required
                            >
                            @error('items.0.total_asset')
                                <p class="panel-form-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="asset-field">
                            <label class="asset-field-label">Good <span class="text-red-500">*</span></label>
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
                                readonly
                                style="background:var(--bg-input-readonly, #f3f4f6); cursor:not-allowed;"
                                required
                            >
                            @error('items.0.total_good')
                                <p class="panel-form-error">{{ $message }}</p>
                            @enderror
                        </div>
                        <input type="hidden" name="items[0][total_damaged]" value="0" data-stock-damaged>
                        <input type="hidden" name="items[0][total_loss]" value="0">
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

            <div class="asset-info-panel">
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
                        <select
                            data-name="asset_category"
                            class="panel-form-input js-asset-category"
                            data-progress-field
                            required
                        >
                            <option value="" disabled selected>Choose category...</option>
                            <option value="electronic">Electronic</option>
                            <option value="non-electronic">Non-Electronic</option>
                            <option value="component-pc">PC Component</option>
                            <option value="pc">PC</option>
                        </select>
                    </div>

                    {{-- (#17) Component Type — tampil hanya jika PC Component --}}
                    <div class="asset-field js-component-type-field" style="display:none;">
                        <label class="asset-field-label">Component Type:</label>
                        <select data-name="component_type" class="panel-form-input">
                            <option value="">— Pilih tipe —</option>
                            <option value="processor">Processor</option>
                            <option value="ram">RAM</option>
                            <option value="ssd">SSD</option>
                            <option value="hdd">HDD</option>
                            <option value="vga">VGA</option>
                            <option value="powersupply">Power Supply</option>
                            <option value="motherboard">Motherboard</option>
                            <option value="cpu_fan">CPU Fan</option>
                        </select>
                    </div>

                    {{-- Spesifikasi — tampil hanya untuk PC Component --}}
                    <div class="asset-field js-spec-field" style="display:none;">
                        <label class="asset-field-label">Spesifikasi:</label>
                        <input
                            type="text"
                            data-name="specification"
                            placeholder="Contoh: Intel Core i5-12400F, 16GB DDR4..."
                            class="panel-form-input"
                        >
                    </div>

                    <div class="asset-field asset-field-source">
                        <label class="asset-field-label">Source <span class="text-red-500">*</span></label>
                        <select
                            data-name="source"
                            class="panel-form-input"
                            data-progress-field
                            required
                        >
                            <option value="Pengadaan">Pengadaan</option>
                            <option value="Pembelian">Pembelian</option>
                        </select>
                    </div>

                    {{-- Kode Inventaris — disembunyikan untuk PC Component --}}
                    <div class="asset-field js-serial-field" style="grid-column: span 2;">
                        <label class="asset-field-label">Kode Inventaris:</label>
                        <div class="js-serial-list" style="display:flex; flex-direction:column; gap:6px;"></div>
                        <button type="button" class="panel-btn-secondary js-add-serial" style="margin-top:6px;">+ Tambah Kode</button>
                        <p class="panel-form-help">Boleh dikosongkan — akan ter-generate otomatis.</p>
                    </div>

                    <div class="js-total-good-wrapper" style="display:contents;">
                        <div class="asset-field">
                            <label class="asset-field-label">Total <span class="text-red-500">*</span></label>
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
                                readonly
                                style="background:var(--bg-input-readonly, #f3f4f6); cursor:not-allowed;"
                                required
                            >
                        </div>

                        <div class="asset-field">
                            <label class="asset-field-label">Good <span class="text-red-500">*</span></label>
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
                                readonly
                                style="background:var(--bg-input-readonly, #f3f4f6); cursor:not-allowed;"
                                required
                            >
                        </div>
                        <input type="hidden" data-name="total_damaged" value="0" data-stock-damaged>
                        <input type="hidden" data-name="total_loss" value="0">
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
        </div>
    </template>

    @else
        @php
            $categoryLabels = [
                'electronic'     => 'Electronic',
                'non-electronic' => 'Non-Electronic',
                'component-pc'   => 'PC Component',
                'pc'             => 'PC',
            ];
            $componentTypeLabels = [
                'processor'   => 'Processor',
                'ram'         => 'RAM',
                'ssd'         => 'SSD',
                'hdd'         => 'HDD',
                'vga'         => 'VGA',
                'powersupply' => 'Power Supply',
                'motherboard' => 'Motherboard',
                'cpu_fan'     => 'CPU Fan',
            ];
            $currentCategory      = $asset->asset_category ?? '';
            $currentComponentType = $asset->component_type ?? '';
            $isComponentPc        = $currentCategory === 'component-pc';
        @endphp

        {{-- Category: tampilkan hanya nilai yang dimiliki asset (read-only, tidak bisa diubah) --}}
        <div class="panel-form-row">
            <label class="panel-form-label">Category:</label>
            <div class="panel-form-field">
                <input type="text" class="panel-form-input" value="{{ $categoryLabels[$currentCategory] ?? ucfirst($currentCategory) }}" readonly style="background:#f3f4f6; cursor:not-allowed; opacity:0.8;">
                <input type="hidden" name="asset_category" value="{{ $currentCategory }}">
                @error('asset_category')
                    <p class="panel-form-error">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Component Type — hanya untuk PC Component; tampilkan hanya tipe yang dimiliki asset --}}
        @if ($isComponentPc)
        <div class="panel-form-row">
            <label class="panel-form-label">Component Type:</label>
            <div class="panel-form-field">
                <input type="text" class="panel-form-input" value="{{ $componentTypeLabels[$currentComponentType] ?? ucfirst($currentComponentType) }}" readonly style="background:#f3f4f6; cursor:not-allowed; opacity:0.8;">
                <input type="hidden" name="component_type" value="{{ $currentComponentType }}">
            </div>
        </div>
        @endif

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

        {{-- Spesifikasi — hanya untuk PC Component --}}
        @if ($isComponentPc)
        <div class="panel-form-row">
            <label class="panel-form-label" for="{{ $modalId }}-specification">
                Spesifikasi:
            </label>
            <div class="panel-form-field">
                <input
                    id="{{ $modalId }}-specification"
                    type="text"
                    name="specification"
                    value="{{ old('specification', $asset->specification ?? '') }}"
                    placeholder="Contoh: Intel Core i5-12400F, 16GB DDR4..."
                    class="panel-form-input"
                    data-progress-field
                >
                @error('specification')
                    <p class="panel-form-error">{{ $message }}</p>
                @enderror
            </div>
        </div>
        @endif

        {{-- Kode Inventaris — disembunyikan untuk PC Component --}}
        @if (!$isComponentPc)
        <div class="panel-form-row js-edit-serial">
            <label class="panel-form-label">Kode Inventaris:</label>
            <div class="panel-form-field">
                <div class="js-serial-list" data-asset-id="{{ $asset->id ?? '' }}"
                     style="display:flex; flex-direction:column; gap:6px;"></div>
                <button type="button" class="panel-btn-secondary js-add-serial" style="margin-top:6px;">+ Kode</button>
                <p class="panel-form-help">Jumlah kode inventaris mengikuti Total unit. Kosongkan untuk generate otomatis.</p>
            </div>
        </div>
        @endif

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
                    @if(!$isComponentPc)
                    readonly
                    style="background:var(--bg-input-readonly, #f3f4f6); cursor:not-allowed;"
                    @endif
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
                    @if(!$isComponentPc)
                    readonly
                    style="background:var(--bg-input-readonly, #f3f4f6); cursor:not-allowed;"
                    @endif
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
            {{-- <label class="panel-form-label" for="{{ $modalId }}-asset-entry">
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
            </div> --}}

            <label class="panel-form-label">Source <span class="text-red-500">*</span></label>
            <div class="panel-form-field">
                <select
                    name="source"
                    class="panel-form-input"
                    data-progress-field
                    required
                >
                    <option value="Pengadaan" {{ old('source', $asset->source ?? '') === 'Pengadaan' ? 'selected' : '' }}>Pengadaan</option>
                    <option value="Pembelian" {{ old('source', $asset->source ?? '') === 'Pembelian' ? 'selected' : '' }}>Pembelian</option>
                </select>
                @error('source')
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

@once
    {{-- QR Scanner Overlay --}}
    <div id="modal-qr-scanner-overlay" onclick="if(event.target===this) stopModalQrScanner()" style="display:none;">
        <div class="qr-scanner-container" onclick="event.stopPropagation()">
            <div class="qr-scanner-header">
                <div class="qr-scanner-title">Scan QR Code</div>
                <button type="button" class="qr-scanner-close" onclick="stopModalQrScanner()">&times;</button>
            </div>
            <div id="modal-qr-reader"></div>
            <p style="font-size: .8rem; color: var(--text-muted); text-align: center; margin: 0;">
                Arahkan QR Code ke kamera untuk memindai otomatis.
            </p>
        </div>
    </div>

    @push('styles')
        <style>
            #modal-qr-scanner-overlay {
                position: fixed;
                top: 0; left: 0; width: 100vw; height: 100vh;
                background: rgba(0, 0, 0, 0.7);
                backdrop-filter: blur(8px);
                display: none;
                align-items: center;
                justify-content: center;
                z-index: 99999;
            }
            .qr-scanner-container {
                background: var(--bg-card, #fff);
                border: 1px solid var(--border-color, #e5e7eb);
                border-radius: 16px;
                width: 90%;
                max-width: 480px;
                padding: 24px;
                box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
                display: flex;
                flex-direction: column;
                gap: 16px;
            }
            .qr-scanner-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            .qr-scanner-title {
                font-size: 1.1rem;
                font-weight: 700;
                color: var(--text-primary, #111b4c);
            }
            .qr-scanner-close {
                background: none;
                border: none;
                font-size: 1.5rem;
                cursor: pointer;
                color: var(--text-muted, #9ca3af);
            }
            .qr-scanner-close:hover {
                color: var(--text-primary, #111b4c);
            }
            #modal-qr-reader {
                width: 100%;
                border-radius: 12px;
                overflow: hidden;
                background: #000;
                min-height: 280px;
            }
            #modal-qr-reader video {
                border-radius: 12px;
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            // (#16/#17) Logika serial number & component_type untuk modal Asset.
            // Dibuat DELEGATIF berbasis `name`, jadi TIDAK menyentuh JS cloning (addAssetItem) milikmu.
            (function () {
                /* ─── Cache daftar asset SPV ─── */
                let spvAssetsCache = null;

                async function fetchSpvAssets() {
                    if (spvAssetsCache) return spvAssetsCache;
                    try {
                        const res = await fetch('/api/spv-assets-with-serials');
                        spvAssetsCache = await res.json();
                    } catch (e) {
                        spvAssetsCache = [];
                    }
                    return spvAssetsCache;
                }

                /* ─── Populate dropdown asset SPV ─── */
                async function populateSpvAssetDropdown(selectEl) {
                    if (selectEl.dataset.spvLoaded) return;
                    selectEl.dataset.spvLoaded = '1';

                    const assets = await fetchSpvAssets();
                    assets.forEach(a => {
                        const opt = document.createElement('option');
                        opt.value = a.id;
                        opt.textContent = `${a.asset_name} (${a.available_count} available)`;
                        selectEl.appendChild(opt);
                    });
                }

                /* ─── Populate dropdown serial number berdasarkan asset yang dipilih ─── */
                async function populateSpvSerialDropdown(serialSelect, assetId) {
                    serialSelect.innerHTML = '<option value="">— Kode Inventaris —</option>';
                    if (!assetId) return;

                    try {
                        const res = await fetch(`/api/assets/${assetId}/available-serials`);
                        const data = await res.json();
                        (data.serials || []).forEach(s => {
                            const opt = document.createElement('option');
                            opt.value = s.id;
                            opt.textContent = s.serial_number;
                            serialSelect.appendChild(opt);
                        });
                    } catch (e) { /* silent */ }
                }

                /* ─── Init SPV picker untuk satu card ─── */
                function initSpvPicker(card) {
                    const assetSel = card.querySelector('.js-spv-asset-select');
                    const serialSel = card.querySelector('.js-spv-serial-select');
                    if (!assetSel || !serialSel) return;

                    populateSpvAssetDropdown(assetSel);

                    assetSel.addEventListener('change', () => {
                        populateSpvSerialDropdown(serialSel, assetSel.value);
                    });
                }

                function getStockInputs(el) {
                    const card = el.closest('.asset-item-card');
                    if (card) {
                        return {
                            total: card.querySelector('[data-stock-total]'),
                            good: card.querySelector('[data-stock-good]')
                        };
                    }
                    const form = el.closest('form');
                    if (form) {
                        return {
                            total: form.querySelector('[name="total_asset"]'),
                            good: form.querySelector('[name="total_good"]')
                        };
                    }
                    return { total: null, good: null };
                }

                function updateStockCount(el, delta) {
                    const card = el.closest('.asset-item-card');
                    if (card) {
                        const catEl = card.querySelector('[name$="[asset_category]"]');
                        if (catEl && catEl.value === 'component-pc') return;
                    }
                    const form = el.closest('form');
                    if (form && !card) {
                        const isComp = !!form.querySelector('[name="component_type"]');
                        if (isComp) return;
                    }
                    
                    const inputs = getStockInputs(el);
                    if (inputs.total) {
                        const currentTotal = parseInt(inputs.total.value) || 0;
                        inputs.total.value = Math.max(0, currentTotal + delta);
                        inputs.total.dispatchEvent(new Event('input', { bubbles: true }));
                        inputs.total.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                    if (inputs.good) {
                        const currentGood = parseInt(inputs.good.value) || 0;
                        inputs.good.value = Math.max(0, currentGood + delta);
                        inputs.good.dispatchEvent(new Event('input', { bubbles: true }));
                        inputs.good.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                }

                function addSerialInput(list, name, value, locked, serialId = null, condition = 'good', prefix = null, qr_code = null) {
                    const row = document.createElement('div');
                    row.style.cssText = 'display:flex; gap:6px; align-items:center; margin-bottom:6px;';

                    // Parse prefix & qrVal from separate columns or fallback
                    let prefixVal = prefix || '';
                    let qrVal = qr_code || value || '';
                    if (value && !prefix && !qr_code) {
                        const lastDash = value.lastIndexOf('-');
                        if (lastDash !== -1) {
                            prefixVal = value.substring(0, lastDash);
                            qrVal = value.substring(lastDash + 1);
                        }
                    }

                    const idx = list.children.length;
                    const baseName = name.endsWith('[]') ? name.slice(0, -2) + `[${idx}]` : name + `[${idx}]`;

                    // Hidden input that will actually be submitted to backend
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = `${baseName}[serial_number]`;
                    hiddenInput.value = value || '';
                    row.appendChild(hiddenInput);

                    // ID input
                    if (serialId) {
                        const idInput = document.createElement('input');
                        idInput.type = 'hidden';
                        idInput.name = `${baseName}[id]`;
                        idInput.value = serialId;
                        row.appendChild(idInput);
                    }

                    // Condition input
                    const condInput = document.createElement('input');
                    condInput.type = 'hidden';
                    condInput.name = `${baseName}[condition]`;
                    condInput.value = condition || 'good';
                    row.appendChild(condInput);

                    // Prefix / Template input
                    const prefixInput = document.createElement('input');
                    prefixInput.type = 'text';
                    prefixInput.name = `${baseName}[prefix]`;
                    prefixInput.value = prefixVal;
                    prefixInput.placeholder = 'Template / Prefix...';
                    prefixInput.className = 'panel-form-input';
                    prefixInput.style.width = '140px';
                    row.appendChild(prefixInput);

                    // Scanned QR code input
                    const qrInput = document.createElement('input');
                    qrInput.type = 'text';
                    qrInput.name = `${baseName}[qr_code]`;
                    qrInput.value = qrVal;
                    qrInput.placeholder = 'Scan QR Code...';
                    qrInput.className = 'panel-form-input';
                    qrInput.style.flex = '1';
                    row.appendChild(qrInput);

                    const updateFinalValue = () => {
                        const p = prefixInput.value.trim();
                        const q = qrInput.value.trim();
                        hiddenInput.value = p && q ? `${p}-${q}` : (p || q);
                    };

                    prefixInput.addEventListener('input', updateFinalValue);
                    qrInput.addEventListener('input', updateFinalValue);
                    qrInput.addEventListener('change', updateFinalValue);

                    // Camera button (always available, even if locked)
                    const qrBtn = document.createElement('button');
                    qrBtn.type = 'button';
                    qrBtn.className = 'panel-btn-secondary';
                    qrBtn.style.padding = '0 12px';
                    qrBtn.innerHTML = '📷';
                    qrBtn.onclick = () => startQrScannerForInput(qrInput);
                    row.appendChild(qrBtn);

                    // Condition selection round buttons (only if structured/edit mode)
                    if (isStructured) {
                        const condWrapper = document.createElement('div');
                        condWrapper.style.cssText = 'display:flex; gap:3px;';
                        
                        const conds = [
                            { key: 'good', label: 'G', color: '#22c55e' },
                            { key: 'damaged', label: 'D', color: '#ef4444' },
                            { key: 'lost', label: 'L', color: '#6b7280' }
                        ];
                        
                        conds.forEach(cOpt => {
                            const btn = document.createElement('button');
                            btn.type = 'button';
                            btn.textContent = cOpt.label;
                            btn.style.cssText = `width:26px; height:26px; border-radius:50%; border:1px solid ${cOpt.color}; font-size:10px; font-weight:bold; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:all 0.15s;`;
                            
                            const setBtnState = (isActive) => {
                                if (isActive) {
                                    btn.style.background = cOpt.color;
                                    btn.style.color = '#fff';
                                } else {
                                    btn.style.background = 'transparent';
                                    btn.style.color = cOpt.color;
                                }
                            };
                            
                            setBtnState(condition === cOpt.key);
                            
                            btn.onclick = () => {
                                condWrapper.querySelectorAll('button').forEach(b => {
                                    b.style.background = 'transparent';
                                    b.style.color = b.style.borderColor;
                                });
                                setBtnState(true);
                                const condInputEl = row.querySelector(`input[name="serials[${idx}][condition]"]`);
                                if (condInputEl) {
                                    condInputEl.value = cOpt.key;
                                    recalculateEditModalCounts(row.closest('form'));
                                }
                            };
                            
                            condWrapper.appendChild(btn);
                        });
                        row.appendChild(condWrapper);
                    }

                    // Delete button (only if not locked)
                    if (!locked) {
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'panel-btn-secondary';
                        btn.textContent = '×';
                        btn.style.padding = '0 12px';
                        btn.onclick = () => {
                            updateStockCount(row, -1);
                            row.remove();
                            recalculateEditModalCounts(row.closest('form'));
                        };
                        row.appendChild(btn);
                    }
                    list.appendChild(row);
                }

                function recalculateEditModalCounts(form) {
                    if (!form) return;
                    const rows = form.querySelectorAll('.js-serial-list > div');
                    let total = rows.length;
                    let good = 0;
                    let damaged = 0;
                    let lost = 0;
                    rows.forEach(r => {
                        const condInp = r.querySelector('input[name$="[condition]"]');
                        const cond = condInp ? condInp.value : 'good';
                        if (cond === 'good') good++;
                        else if (cond === 'damaged') damaged++;
                        else if (cond === 'lost') lost++;
                    });
                    
                    const totalInput = form.querySelector('[name="total_asset"]');
                    const goodInput = form.querySelector('[name="total_good"]');
                    const damagedInput = form.querySelector('[name="total_damaged"]');
                    const lossInput = form.querySelector('[name="total_loss"]');
                    
                    if (totalInput) totalInput.value = total;
                    if (goodInput) goodInput.value = good;
                    if (damagedInput) damagedInput.value = damaged;
                    if (lossInput) lossInput.value = lost;
                }

                // CREATE: tampil/sembunyi component_type, spec, dan kode inventaris per kartu.
                function toggleCard(card, cat) {
                    const isComp = cat === 'component-pc';
                    const ct     = card.querySelector('.js-component-type-field');
                    const spec   = card.querySelector('.js-spec-field');
                    const serial = card.querySelector('.js-serial-field');
                    const spv    = card.querySelector('.js-spv-serial-field');
                    if (ct)     ct.style.display     = isComp ? '' : 'none';
                    if (spec)   spec.style.display   = isComp ? '' : 'none';
                    if (serial) serial.style.display = isComp ? 'none' : '';
                    if (spv) {
                        spv.style.display = isComp ? 'none' : '';
                        if (!isComp) initSpvPicker(card);
                    }

                    // Dynamically move total-good wrapper
                    const wrapper = card.querySelector('.js-total-good-wrapper');
                    if (wrapper) {
                        if (isComp) {
                            if (spec) spec.after(wrapper);
                        } else {
                            if (serial) serial.after(wrapper);
                        }
                    }

                    // Toggle readonly on Total & Good inputs based on category
                    const inputs = getStockInputs(card);
                    if (inputs.total) {
                        if (isComp) {
                            inputs.total.removeAttribute('readonly');
                            inputs.total.style.background = '';
                            inputs.total.style.cursor = '';
                        } else {
                            inputs.total.setAttribute('readonly', 'true');
                            inputs.total.style.background = 'var(--bg-input-readonly, #f3f4f6)';
                            inputs.total.style.cursor = 'not-allowed';
                        }
                    }
                    if (inputs.good) {
                        if (isComp) {
                            inputs.good.removeAttribute('readonly');
                            inputs.good.style.background = '';
                            inputs.good.style.cursor = '';
                        } else {
                            inputs.good.setAttribute('readonly', 'true');
                            inputs.good.style.background = 'var(--bg-input-readonly, #f3f4f6)';
                            inputs.good.style.cursor = 'not-allowed';
                        }
                    }
                }

                function toggleEdit(form, cat) {
                }

                document.addEventListener('change', function (e) {
                    if (e.target.matches && e.target.matches('select.js-asset-category')) {
                        const card = e.target.closest('.asset-item-card');
                        if (card) toggleCard(card, e.target.value);
                    }
                    if (e.target.matches && e.target.matches('input[name="asset_category"]')) {
                        toggleEdit(e.target.closest('form'), e.target.value);
                    }
                });

                document.addEventListener('input', function (e) {
                    if (!e.target.matches) return;
                    const isTotal = e.target.matches('[name="total_asset"], [name$="[total_asset]"]');
                    const isDamaged = e.target.matches('[name="total_damaged"], [name$="[total_damaged]"]');
                    const isLoss = e.target.matches('[name="total_loss"], [name$="[total_loss]"]');
                    
                    if (isTotal || isDamaged || isLoss) {
                        const card = e.target.closest('.asset-item-card');
                        const form = e.target.closest('form');
                        
                        let totalInput, goodInput, damagedInput, lossInput;
                        if (card) {
                            totalInput = card.querySelector('[name$="[total_asset]"], [data-stock-total]');
                            goodInput = card.querySelector('[name$="[total_good]"], [data-stock-good]');
                            damagedInput = card.querySelector('[name$="[total_damaged]"], [data-stock-damaged]');
                            lossInput = card.querySelector('[name$="[total_loss]"]');
                        } else if (form) {
                            totalInput = form.querySelector('[name="total_asset"]');
                            goodInput = form.querySelector('[name="total_good"]');
                            damagedInput = form.querySelector('[name="total_damaged"]');
                            lossInput = form.querySelector('[name="total_loss"]');
                        }
                        
                        if (totalInput && goodInput && damagedInput && lossInput) {
                            const total = parseInt(totalInput.value) || 0;
                            let damaged = parseInt(damagedInput.value) || 0;
                            let loss = parseInt(lossInput.value) || 0;
                            
                            let good = total - damaged - loss;
                            if (good < 0) {
                                if (isDamaged) {
                                    damaged = total - loss;
                                    if (damaged < 0) damaged = 0;
                                    damagedInput.value = damaged;
                                } else if (isLoss) {
                                    loss = total - damaged;
                                    if (loss < 0) loss = 0;
                                    lossInput.value = loss;
                                } else if (isTotal) {
                                    damaged = total;
                                    loss = 0;
                                    damagedInput.value = damaged;
                                    lossInput.value = loss;
                                }
                                good = 0;
                            }
                            
                            goodInput.value = good;
                            goodInput.dispatchEvent(new Event('input', { bubbles: true }));
                        }
                    }
                });

                // Tombol "Add S/N" (create & edit).
                document.addEventListener('click', function (e) {
                    if (!e.target.matches || !e.target.matches('.js-add-serial')) return;
                    const field = e.target.closest('.asset-field, .panel-form-row');
                    const list = field && field.querySelector('.js-serial-list');
                    if (!list) return;

                    let name = 'serials[]';
                    const card = e.target.closest('.asset-item-card');
                    if (card) {
                        const catEl = card.querySelector('[name$="[asset_category]"]');
                        const nm = catEl && catEl.getAttribute('name');
                        const m = nm && nm.match(/items\[(\d+)\]/);
                        name = m ? `items[${m[1]}][serials][]` : 'items[0][serials][]';
                    }
                    addSerialInput(list, name, '', false);
                    updateStockCount(e.target, 1);
                });

                document.addEventListener('DOMContentLoaded', function () {
                    // Init create (kartu pertama bila ada old value).
                    document.querySelectorAll('select.js-asset-category').forEach(sel => {
                        const card = sel.closest('.asset-item-card');
                        if (card && sel.value) toggleCard(card, sel.value);
                    });
                    // Init edit (kategori sudah terpilih).
                    document.querySelectorAll('input[name="asset_category"]:checked').forEach(r => {
                        toggleEdit(r.closest('form'), r.value);
                    });
                    // For the Edit form:
                    const editForm = document.querySelector('form[action*="/asset/"]');
                    if (editForm) {
                        const isComp = !!editForm.querySelector('[name="component_type"]');
                        const totalInput = editForm.querySelector('[name="total_asset"]');
                        const goodInput = editForm.querySelector('[name="total_good"]');
                        if (totalInput && goodInput) {
                            if (isComp) {
                                totalInput.removeAttribute('readonly');
                                totalInput.style.background = '';
                                totalInput.style.cursor = '';
                            } else {
                                totalInput.setAttribute('readonly', 'true');
                                totalInput.style.background = 'var(--bg-input-readonly, #f3f4f6)';
                                totalInput.style.cursor = 'not-allowed';
                            }
                            if (isComp) {
                                goodInput.removeAttribute('readonly');
                                goodInput.style.background = '';
                                goodInput.style.cursor = '';
                            } else {
                                goodInput.setAttribute('readonly', 'true');
                                goodInput.style.background = 'var(--bg-input-readonly, #f3f4f6)';
                                goodInput.style.cursor = 'not-allowed';
                            }
                        }
                    }
                    // Preload serial existing untuk modal Edit yang relevan (electronic/component-pc).
                    document.querySelectorAll('.js-edit-serial').forEach(field => {
                        if (field.style.display === 'none') return;
                        const list = field.querySelector('.js-serial-list');
                        const assetId = list && list.dataset.assetId;
                        if (!assetId) return;
                        fetch(`/api/assets/${assetId}/serials`)
                            .then(r => r.json())
                            .then(d => {
                                list.innerHTML = '';
                                (d.serials || []).forEach(s => addSerialInput(list, 'serials[]', s.serial_number, s.locked, s.id, s.condition, s.prefix, s.qr_code));
                            })
                            .catch(() => {});
                    });
                });

                /* ─── QR Code Scanner ─── */
                let modalHtml5QrCode = null;
                let activeQrInput = null;

                window.startQrScannerForInput = async function(inputEl) {
                    activeQrInput = inputEl;
                    const overlay = document.getElementById('modal-qr-scanner-overlay');
                    if (!overlay) return;

                    overlay.style.display = 'flex';

                    try {
                        if (typeof Html5Qrcode === 'undefined') {
                            document.getElementById('modal-qr-reader').innerHTML = 
                                '<div style="color:#fff; display:flex; align-items:center; justify-content:center; height:280px; font-size:13px;">Loading camera library...</div>';
                            
                            await new Promise((resolve, reject) => {
                                const script = document.createElement('script');
                                script.src = "https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js";
                                script.onload = resolve;
                                script.onerror = () => reject(new Error("Gagal memuat library scanner."));
                                document.head.appendChild(script);
                            });
                            
                            document.getElementById('modal-qr-reader').innerHTML = '';
                        }

                        modalHtml5QrCode = new Html5Qrcode("modal-qr-reader");
                        const config = { fps: 10, qrbox: { width: 250, height: 250 } };

                        modalHtml5QrCode.start(
                            { facingMode: "environment" },
                            config,
                            (decodedText, decodedResult) => {
                                if (activeQrInput) {
                                    activeQrInput.value = decodedText;
                                    activeQrInput.dispatchEvent(new Event('input', { bubbles: true }));
                                    activeQrInput.dispatchEvent(new Event('change', { bubbles: true }));
                                }
                                stopModalQrScanner();
                            },
                            (errorMessage) => {
                                // ignore errors
                            }
                        ).catch(err => {
                            console.error("Unable to start scanner", err);
                            document.getElementById('modal-qr-reader').innerHTML = 
                                `<div style="color:#f87171; display:flex; align-items:center; justify-content:center; height:280px; font-size:13px; text-align:center; padding:16px;">Error: ${err.message || err}</div>`;
                        });
                    } catch (err) {
                        console.error("Scanner library load error", err);
                        document.getElementById('modal-qr-reader').innerHTML = 
                            `<div style="color:#f87171; display:flex; align-items:center; justify-content:center; height:280px; font-size:13px; text-align:center; padding:16px;">Gagal memuat library kamera. Pastikan koneksi internet aktif.</div>`;
                    }
                };

                window.stopModalQrScanner = function() {
                    const overlay = document.getElementById('modal-qr-scanner-overlay');
                    if (overlay) {
                        overlay.style.display = 'none';
                    }
                    if (modalHtml5QrCode) {
                        modalHtml5QrCode.stop().then(() => {
                            modalHtml5QrCode = null;
                            activeQrInput = null;
                        }).catch(err => {
                            console.error("Failed to stop scanner", err);
                            modalHtml5QrCode = null;
                            activeQrInput = null;
                        });
                    } else {
                        activeQrInput = null;
                    }
                };
            })();
        </script>
    @endpush
@endonce
