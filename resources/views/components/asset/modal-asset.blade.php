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


                    {{-- Kode Inventaris — disembunyikan untuk PC Component, tampil untuk kategori lain --}}
                    <div class="asset-field js-serial-field">
                        <label class="asset-field-label">Kode Inventaris:</label>
                        <div class="js-serial-list" style="display:flex; flex-direction:column; gap:6px;"></div>
                        <button type="button" class="panel-btn-secondary js-add-serial" style="margin-top:6px;">+ Tambah Kode</button>
                        <p class="panel-form-help">Boleh dikosongkan — akan ter-generate otomatis.</p>
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

                    {{-- (#17) Damaged & Loss disembunyikan — aset baru pasti good --}}
                    <input type="hidden" name="items[0][total_damaged]" value="0" data-stock-damaged>
                    <input type="hidden" name="items[0][total_loss]" value="0">
                </div>

                <p class="asset-stock-helper">
                    Good akan mengikuti Total. Aset baru otomatis berkondisi good.
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
                        <label class="asset-field-label">Source:</label>
                        <input
                            type="text"
                            data-name="source"
                            placeholder="Pembelian / Hibah / Lab"
                            class="panel-form-input"
                            data-progress-field
                        >
                    </div>


                    {{-- Kode Inventaris — disembunyikan untuk PC Component --}}
                    <div class="asset-field js-serial-field">
                        <label class="asset-field-label">Kode Inventaris:</label>
                        <div class="js-serial-list" style="display:flex; flex-direction:column; gap:6px;"></div>
                        <button type="button" class="panel-btn-secondary js-add-serial" style="margin-top:6px;">+ Tambah Kode</button>
                        <p class="panel-form-help">Boleh dikosongkan — akan ter-generate otomatis.</p>
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

                    {{-- (#17) Damaged & Loss disembunyikan — aset baru pasti good --}}
                    <input type="hidden" data-name="total_damaged" value="0" data-stock-damaged>
                    <input type="hidden" data-name="total_loss" value="0">
                </div>

                <p class="asset-stock-helper">
                    Good akan mengikuti Total. Aset baru otomatis berkondisi good.
                </p>
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

            <label class="panel-form-label">Source</label>
            <input
                type="text"
                name="source"
                value="{{ old('source') }}"
                class="panel-form-input"
                placeholder="Pembelian / Hibah / Lab"
            >

            @error('source')
                <p class="panel-form-error">{{ $message }}</p>
            @enderror

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

                function addSerialInput(list, name, value, locked) {
                    const row = document.createElement('div');
                    row.style.cssText = 'display:flex; gap:6px; align-items:center;';
                    const input = document.createElement('input');
                    input.type = 'text';
                    input.name = name;
                    input.value = value || '';
                    input.placeholder = 'Kode inventaris...';
                    input.className = 'panel-form-input';
                    input.style.flex = '1';
                    if (locked) input.readOnly = true;
                    row.appendChild(input);
                    if (!locked) {
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'panel-btn-secondary';
                        btn.textContent = '×';
                        btn.style.padding = '0 12px';
                        btn.onclick = () => row.remove();
                        row.appendChild(btn);
                    }
                    list.appendChild(row);
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
                                (d.serials || []).forEach(s => addSerialInput(list, 'serials[]', s.serial_number, s.locked));
                            })
                            .catch(() => {});
                    });
                });
            })();
        </script>
    @endpush
@endonce
