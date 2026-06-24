@extends('panel.content')

@section('title', auth()->user()->role === 'spv inventory' ? 'SPV Dashboard' : 'Staff Dashboard')

@section('content')
    <div class="panel-page-card">

        {{-- header --}}
        <div class="mb-5 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <h2 class="panel-page-title">
                Inventory List
            </h2>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                {{-- search --}}
                <x-button.search.modul-search :action="route('asset.index')" name="search" :value="request('search')"
                    placeholder="Search..." />

                {{-- filter --}}
                <x-button.filter :action="route('asset.index')">

                    <div class="filter-section">
                        <div class="filter-section-title">Category</div>
                        @foreach(['electronic', 'non-electronic', 'component-pc'] as $category)
                            <label class="filter-checkbox-row">
                                <input type="checkbox" name="category[]" value="{{ $category }}" {{ in_array($category, (array) request('category', [])) ? 'checked' : '' }} style="accent-color: #111B4C;">
                                <span>{{ ucwords($category) }}</span>
                            </label>
                        @endforeach
                    </div>
                </x-button.filter>


                {{-- export --}}
                <x-button.export.export menuId="assetExportMenu" pdfUrl="{{ route('asset.export', 'pdf') }}"
                    excelUrl="{{ route('asset.export', 'excel') }}" csvUrl="{{ route('asset.export', 'csv') }}" />


                {{-- Add Asset --}}
                <x-button.add type="button" onclick="openAssetCreateModal()">
                    Add Asset
                </x-button.add>
            </div>
        </div>

        {{-- table --}}
        <x-table.index>
            <thead>
                <tr>
                    <x-table.th class="w-12">
                        <x-table.checkbox id="checkAll" />
                    </x-table.th>

                    <x-table.th>Nama Barang</x-table.th>
                    <x-table.th>Kategori</x-table.th>
                    <x-table.th>Total</x-table.th>
                    <x-table.th>Good</x-table.th>
                    <x-table.th>Damaged</x-table.th>
                    <x-table.th>Loss</x-table.th>
                    {{-- <x-table.th>Entry Date</x-table.th> --}}
                    <x-table.th align="center">Action</x-table.th>
                </tr>
            </thead>

            <tbody>
                @forelse ($assets as $asset)
                            <tr class="panel-table-row">
                                <x-table.td>
                                    <x-table.checkbox name="selected_assets[]" :value="$asset->id" class="row-check" />
                                </x-table.td>

                                <x-table.td>
                                    {{ $asset->asset_name }}
                                </x-table.td>

                                <x-table.td>
                                    {{ match ($asset->asset_category) {
                        'electronic' => 'Electronic',
                        'non-electronic' => 'Non-Electronic',
                        'component-pc' => 'PC Component',
                        default => $asset->asset_category,
                    } }}
                                </x-table.td>

                                <x-table.td>
                                    {{ number_format($asset->total_asset) }}
                                </x-table.td>

                                <x-table.td>
                                    {{ number_format($asset->total_good) }}
                                </x-table.td>

                                <x-table.td>
                                    {{ number_format($asset->total_damaged) }}
                                </x-table.td>

                                <x-table.td>
                                    {{ number_format($asset->total_loss) }}
                                </x-table.td>

                                {{-- <x-table.td>
                                    {{ $asset->asset_entry ? \Carbon\Carbon::parse($asset->asset_entry)->format('d-m-Y') : '-' }}
                                </x-table.td> --}}

                                <x-table.td align="center">
                                    <div class="flex items-center justify-center gap-1">

                                        {{-- detail --}}
                                        {{-- <x-table.action href="{{ route('asset.show', $asset->id) }}" variant="view" title="Detail">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z" />
                                                <circle cx="12" cy="12" r="3" />
                                            </svg>
                                        </x-table.action> --}}

                                        {{-- qr code --}}
                                        <x-table.action type="button" variant="view" title="QR Code"
                                            onclick="openQrModal({{ $asset->id }}, '{{ addslashes($asset->asset_name) }}', '{{ $asset->sku }}', '{{ $asset->asset_category }}')"
                                            style="color:#2563eb;">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                <rect x="3" y="3" width="7" height="7" rx="1"/>
                                                <rect x="14" y="3" width="7" height="7" rx="1"/>
                                                <rect x="3" y="14" width="7" height="7" rx="1"/>
                                                <rect x="5" y="5" width="3" height="3" fill="currentColor" stroke="none"/>
                                                <rect x="16" y="5" width="3" height="3" fill="currentColor" stroke="none"/>
                                                <rect x="5" y="16" width="3" height="3" fill="currentColor" stroke="none"/>
                                                <path d="M14 14h3v3h-3zM17 17h3v3h-3zM14 20h3"/>
                                            </svg>
                                        </x-table.action>

                                        {{-- edit --}}
                                        <x-table.action type="button" variant="edit" title="Edit"
                                            onclick="openPanelModal('edit-modal-asset-{{ $asset->id }}')">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                                            </svg>
                                        </x-table.action>

                                        {{-- delete --}}
                                        <form method="POST" action="{{ route('asset.destroy', $asset->id) }}"
                                            onsubmit="return confirm('Hapus asset ini?')">
                                            @csrf
                                            @method('DELETE')

                                            <x-table.action type="submit" variant="delete" title="Delete">
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
                    <x-table.empty colspan="9" message="Asset data not found." />
                @endforelse
            </tbody>
        </x-table.index>

        {{-- pagination --}}
        <div class="mt-5">
            {{ $assets->links() }}
        </div>
    </div>

    {{-- modal create asset --}}
    <x-asset.modal-asset mode="create" />

    {{-- modal edit asset --}}
    @foreach ($assets as $asset)
        <x-asset.modal-asset mode="edit" :asset="$asset" />
    @endforeach

    {{-- ── QR Code Modal ── --}}
    <div id="qr-modal-overlay" class="panel-modal-overlay hidden"
        onclick="if(event.target===this) closeQrModal()">
        <div style="
            background: var(--bg-card);
            border-radius: 20px;
            padding: 0;
            width: 420px;
            max-width: 95vw;
            max-height: 88vh;
            overflow: hidden;
            box-shadow: 0 8px 40px rgba(0,0,0,0.18);
            display: flex; flex-direction: column;
            position: relative;
        ">
            {{-- Modal Header --}}
            <div style="
                padding: 20px 24px 16px;
                border-bottom: 1px solid var(--border-color);
                display: flex; align-items: flex-start; justify-content: space-between; gap: 12px;
                flex-shrink: 0;
            ">
                <div>
                    <div style="font-size:1rem; font-weight:700; color:var(--text-primary);" id="qr-modal-asset-name">—</div>
                    <div style="font-size:.75rem; color:var(--text-muted); font-family:monospace; margin-top:2px;" id="qr-modal-asset-sku">—</div>
                </div>
                <button onclick="closeQrModal()" style="
                    background:none; border:none; cursor:pointer;
                    color:var(--text-muted); font-size:1.4rem; line-height:1;
                    flex-shrink:0; padding:2px 4px;
                " aria-label="Close">&times;</button>
            </div>

            {{-- Panel A: Serial Number List --}}
            <div id="qr-panel-serials" style="flex:1; overflow-y:auto; padding:16px 20px; display:flex; flex-direction:column; gap:8px;">
                {{-- filled by JS --}}
            </div>

            {{-- Panel B: QR Display (hidden initially) --}}
            <div id="qr-panel-qr" style="display:none; flex-direction:column; align-items:center; gap:16px; padding:24px; flex:1; overflow-y:auto;">
                {{-- Back button --}}
                <button onclick="showSerialList()" style="
                    align-self:flex-start;
                    background:none; border:none; cursor:pointer;
                    color:var(--text-muted); font-size:.82rem; font-weight:600;
                    display:flex; align-items:center; gap:6px; padding:0;
                ">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="15 18 9 12 15 6"/>
                    </svg>
                    Kembali ke daftar
                </button>

                {{-- Serial label --}}
                <div style="text-align:center;">
                    <div style="font-size:.7rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:var(--text-muted); margin-bottom:4px;">Serial Number</div>
                    <div style="font-size:1rem; font-weight:700; font-family:monospace; color:var(--text-primary);" id="qr-display-serial">—</div>
                </div>

                {{-- QR canvas wrapper --}}
                <div style="
                    background:#fff; padding:14px;
                    border-radius:14px; border:1px solid var(--border-color);
                    display:flex; align-items:center; justify-content:center;
                    min-width:200px; min-height:200px;
                " id="qr-canvas-wrapper">
                    <canvas id="qr-canvas"></canvas>
                </div>

                <p style="font-size:.73rem; color:var(--text-muted); text-align:center; margin:0;">
                    QR Code berisi serial number unit ini.<br>
                    Scan di menu <strong>Scan Code</strong> untuk melihat detail barang.
                </p>

                {{-- Download --}}
                <button onclick="downloadQr()" style="
                    width:100%; padding:10px 0; border-radius:10px;
                    background: linear-gradient(135deg,#111B4C,#2563eb);
                    color:#fff; font-size:.85rem; font-weight:600;
                    border:none; cursor:pointer;
                    display:flex; align-items:center; justify-content:center; gap:7px;
                    transition: opacity .18s;
                " onmouseover="this.style.opacity='.88'" onmouseout="this.style.opacity='1'">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                        <polyline points="7 10 12 15 17 10"/>
                        <line x1="12" y1="15" x2="12" y2="3"/>
                    </svg>
                    Download PNG
                </button>
            </div>

        </div>
    </div>
@endsection


@push('scripts')
    <script>
        let assetItemIndex = 1;

        const assetCategoryLabels = {
            electronic: 'Electronic',
            'non-electronic': 'Non-Electronic',
            'component-pc': 'PC Component',
        };

        function getSelectedAssetCategoryLabel() {
            const select = document.getElementById('create-asset-category');
            return (select && assetCategoryLabels[select.value]) || 'Choose category first';
        }

        function openAssetCreateModal() {
            openPanelModal('create-modal-asset');
            bindAllAssetTabs();
        }

        document.addEventListener('change', function (event) {
            if (event.target && event.target.id === 'create-asset-category') {
                document.querySelectorAll('[data-category-display]').forEach((input) => {
                    input.value = getSelectedAssetCategoryLabel();
                });
            }
        });

        function updateAssetItemIndexes() {
            const items = document.querySelectorAll('#assetItemsWrapper [data-asset-item]');

            items.forEach((item, index) => {
                item.querySelectorAll('[data-name]').forEach((input) => {
                    input.name = `items[${index}][${input.dataset.name}]`;
                });

                item.querySelectorAll('input[name^="items["], textarea[name^="items["], select[name^="items["]').forEach((input) => {
                    const match = input.name.match(/\]\[(.*?)\]/);

                    if (match && match[1]) {
                        input.name = `items[${index}][${match[1]}]`;
                    }
                });
            });

            assetItemIndex = items.length;
        }

        function addAssetItem() {
            const wrapper = document.getElementById('assetItemsWrapper');
            const template = document.getElementById('assetItemTemplate');

            if (!wrapper || !template) return;

            const clone = template.content.cloneNode(true);
            const item = clone.querySelector('[data-asset-item]');

            item.querySelectorAll('[data-name]').forEach((input) => {
                input.name = `items[${assetItemIndex}][${input.dataset.name}]`;
            });

            item.querySelectorAll('[data-category-display]').forEach((input) => {
                input.value = getSelectedAssetCategoryLabel();
            });

            wrapper.appendChild(item);

            assetItemIndex++;

            const modal = document.getElementById('create-modal-asset');

            if (modal) {
                initPanelFormModal(modal);
                initLiveValidation(modal);
                initStockAutoCalc(modal);
                initAssetTabs(modal);
            }
        }

        function removeAssetItem(button) {
            const wrapper = document.getElementById('assetItemsWrapper');
            const item = button.closest('[data-asset-item]');

            if (!wrapper || !item) return;

            const totalItems = wrapper.querySelectorAll('[data-asset-item]').length;

            if (totalItems <= 1) {
                alert('At least one asset item is required.');
                return;
            }

            item.remove();
            updateAssetItemIndexes();

            const modal = document.getElementById('create-modal-asset');

            if (modal) {
                initPanelFormModal(modal);
            }
        }

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
            initStockAutoCalc(modal);
            initAssetTabs(modal);
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

                    total++;

                    if (field.value && field.value.trim() !== '') {
                        filled++;
                    }
                });

                const percent = total === 0 ? 0 : Math.round((filled / total) * 100);

                progressBar.style.width = percent + '%';
            }

            fields.forEach((field) => {
                field.addEventListener('input', updateProgress);
                field.addEventListener('change', updateProgress);
            });

            modal.querySelectorAll('.asset-category-option').forEach((label) => {
                label.addEventListener('click', function () {
                    const group = this.closest('[data-asset-category-group]');

                    if (!group) return;

                    group.querySelectorAll('.asset-category-option').forEach(item => {
                        item.classList.remove('is-selected');
                    });

                    this.classList.add('is-selected');

                    setTimeout(updateProgress, 20);
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

                if (type === 'asset-number') {
                    const invalid = value !== '' && Number(value) < 0;

                    showError(input, invalid);

                    return !invalid;
                }

                return true;
            }

            fields.forEach((input) => {
                input.addEventListener('input', function () {
                    validateInput(input);
                });

                input.addEventListener('blur', function () {
                    validateInput(input);
                });
            });

            form.addEventListener('submit', function (event) {
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

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                document.querySelectorAll('.panel-modal-overlay:not(.hidden)').forEach((modal) => {
                    modal.classList.add('hidden');
                });

                document.body.style.overflow = '';
            }
        });

        const checkAll = document.getElementById('checkAll');

        if (checkAll) {
            checkAll.addEventListener('change', function () {
                document.querySelectorAll('.row-check').forEach((checkbox) => {
                    checkbox.checked = this.checked;
                });
            });
        }

        function initStockAutoCalc(scope) {
            // scope bisa modal element atau document
            const items = scope.querySelectorAll('[data-asset-item]');

            // Create mode: per item
            if (items.length > 0) {
                items.forEach(item => bindStockCalc(item));
            } else {
                // Edit mode: langsung di modal
                bindStockCalc(scope);
            }
        }

        function bindStockCalc(scope) {
            const totalInput = scope.querySelector('[data-stock-total]');
            const goodInput = scope.querySelector('[data-stock-good]');
            const damagedInput = scope.querySelector('[data-stock-damaged]');

            if (!totalInput || !goodInput || !damagedInput) return;

            // klo total diubah → recalc yg terakhir diisi
            totalInput.addEventListener('input', () => recalcFromTotal(totalInput, goodInput, damagedInput));
            goodInput.addEventListener('input', () => recalcFromPart(totalInput, goodInput, damagedInput, 'damaged'));
            damagedInput.addEventListener('input', () => recalcFromPart(totalInput, goodInput, damagedInput, 'good'));
        }

        function recalcFromTotal(totalInput, goodInput, damagedInput) {
            const total = parseInt(totalInput.value) || 0;
            const good = parseInt(goodInput.value) || 0;
            const damaged = parseInt(damagedInput.value) || 0;

            // good diisi → hitung damaged. auto calculate klo semisal dibalik juga isinya
            if (good > 0 || damaged === 0) {
                damagedInput.value = Math.max(0, total - good);
            } else {
                goodInput.value = Math.max(0, total - damaged);
            }
        }

        function recalcFromPart(totalInput, goodInput, damagedInput, calcWhich) {
            const total = parseInt(totalInput.value) || 0;
            const good = parseInt(goodInput.value) || 0;
            const damaged = parseInt(damagedInput.value) || 0;

            if (calcWhich === 'damaged') {
                damagedInput.value = Math.max(0, total - good);
            } else {
                goodInput.value = Math.max(0, total - damaged);
            }
        }

        function bindAssetTabs(item) {
            if (!item || item.dataset.tabsBound === 'true') return;

            item.dataset.tabsBound = 'true';

            const buttons = item.querySelectorAll('[data-asset-tab-target]');
            const panels = item.querySelectorAll('[data-asset-tab-panel]');

            buttons.forEach((button) => {
                button.addEventListener('click', function () {
                    const target = this.dataset.assetTabTarget;

                    buttons.forEach((btn) => {
                        btn.classList.remove('is-active');
                    });

                    panels.forEach((panel) => {
                        panel.classList.toggle('is-active', panel.dataset.assetTabPanel === target);
                    });

                    this.classList.add('is-active');
                });
            });
        }

        function bindAllAssetTabs() {
            document.querySelectorAll('#assetItemsWrapper [data-asset-item]').forEach((item) => {
                bindAssetTabs(item);
            });
        }

        function initAssetTabs(scope) {
            const tabsWrappers = scope.querySelectorAll('[data-asset-tabs]');

            tabsWrappers.forEach((tabsWrapper) => {
                const card = tabsWrapper.closest('[data-asset-item]');

                if (!card) return;

                const buttons = tabsWrapper.querySelectorAll('[data-asset-tab-target]');

                buttons.forEach((btn) => {
                    btn.addEventListener('click', function () {
                        const target = this.dataset.assetTabTarget;

                        // reset semua tab btn
                        buttons.forEach(b => b.classList.remove('is-active'));
                        this.classList.add('is-active');

                        // reset semua panel
                        card.querySelectorAll('[data-asset-tab-panel]').forEach(panel => {
                            panel.classList.remove('is-active');
                        });

                        // aktifin panel yang dituju
                        const activePanel = card.querySelector(`[data-asset-tab-panel="${target}"]`);
                        if (activePanel) activePanel.classList.add('is-active');
                    });
                });
            });
        }
    </script>
@endpush

{{-- ════ QR CODE MODAL SCRIPTS ════ --}}
@push('scripts')
<script>
    /* ────────────────────────────────────────────────────────────
       QR CODE MODAL — Serial Number
       QR image is generated by Laravel backend /qr?text=...
       No JS library needed at all.
    ──────────────────────────────────────────────────────────── */
    let _currentSerial  = '';
    let _currentAssetId = null;

    const _STATUS = { available:'Available', in_use:'In Use', 'in-use':'In Use', damaged:'Rusak', lost:'Hilang' };
    const _COND   = { good:'Baik', damaged:'Rusak', lost:'Hilang' };

    /* ── Open modal, fetch serial list ── */
    async function openQrModal(assetId, assetName, assetSku, assetCategory) {
        _currentAssetId = assetId;

        document.getElementById('qr-modal-asset-name').textContent = assetName;
        document.getElementById('qr-modal-asset-sku').textContent  = assetSku;

        showSerialList();

        document.getElementById('qr-modal-overlay').classList.remove('hidden');
        document.body.style.overflow = 'hidden';

        const panel = document.getElementById('qr-panel-serials');
        panel.innerHTML = '<p style="color:var(--text-muted);font-size:.85rem;padding:8px 0;">Memuat data serial...</p>';

        if (!['electronic','component-pc','pc'].includes(assetCategory)) {
            panel.innerHTML = `
                <div style="text-align:center;padding:28px 0;color:var(--text-muted);">
                    <div style="font-weight:600;font-size:.9rem;color:var(--text-primary);margin-bottom:4px;">Tidak ada Serial Number</div>
                    <div style="font-size:.78rem;">Aset <strong>Non-Electronic</strong> tidak menggunakan serial number.</div>
                </div>`;
            return;
        }

        try {
            const res  = await fetch(`/api/assets/${assetId}/serials`);
            const data = await res.json();
            _renderSerialList(panel, data.serials || []);
        } catch(e) {
            panel.innerHTML = '<p style="color:#b91c1c;font-size:.85rem;">Gagal memuat data serial.</p>';
        }
    }

    /* ── Render list of serial numbers ── */
    function _renderSerialList(panel, serials) {
        if (!serials.length) {
            panel.innerHTML = `
                <div style="text-align:center;padding:28px 0;color:var(--text-muted);">
                    <div style="font-weight:600;font-size:.9rem;color:var(--text-primary);margin-bottom:4px;">Belum ada Serial Number</div>
                    <div style="font-size:.78rem;">Tambahkan serial number saat edit aset.</div>
                </div>`;
            return;
        }

        panel.innerHTML = `<div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-muted);margin-bottom:6px;">${serials.length} unit — klik untuk lihat QR</div>`;

        serials.forEach(s => {
            const isAvail = s.status === 'available';
            const pill    = `<span style="display:inline-block;padding:1px 9px;border-radius:20px;font-size:.67rem;font-weight:700;background:${isAvail?'rgba(22,163,74,.12)':'rgba(37,99,235,.1)'};color:${isAvail?'#16a34a':'#2563eb'}">${_STATUS[s.status]??s.status}</span>`;

            const row = document.createElement('button');
            row.type  = 'button';
            row.style.cssText = 'display:flex;align-items:center;justify-content:space-between;gap:12px;width:100%;padding:12px 14px;background:var(--bg-input);border:1px solid var(--border-color);border-radius:10px;cursor:pointer;text-align:left;transition:border-color .15s,background .15s;';
            row.onmouseover = ()=>{ row.style.borderColor='#2563eb'; row.style.background='rgba(37,99,235,.04)'; };
            row.onmouseout  = ()=>{ row.style.borderColor='var(--border-color)'; row.style.background='var(--bg-input)'; };
            row.innerHTML   = `
                <div>
                    <div style="font-family:monospace;font-weight:700;font-size:.9rem;color:var(--text-primary);">${_esc(s.serial_number)}</div>
                    <div style="font-size:.72rem;color:var(--text-muted);margin-top:2px;">Kondisi: ${_COND[s.condition]??s.condition}</div>
                </div>
                <div style="display:flex;align-items:center;gap:8px;">${pill}<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="opacity:.5"><polyline points="9 18 15 12 9 6"/></svg></div>`;
            row.onclick = () => _showQr(s.serial_number);
            panel.appendChild(row);
        });
    }

    /* ── Show QR panel for one serial ── */
    function _showQr(serialNumber) {
        _currentSerial = serialNumber;

        document.getElementById('qr-panel-serials').style.display = 'none';
        document.getElementById('qr-panel-qr').style.display = 'flex';
        document.getElementById('qr-display-serial').textContent = serialNumber;

        const wrapper = document.getElementById('qr-canvas-wrapper');

        // Loading state
        wrapper.innerHTML = '<div style="color:var(--text-muted);font-size:.82rem;">Generating QR...</div>';

        // Backend-generated QR image — guaranteed to render
        const url = '/qr?size=220&text=' + encodeURIComponent(serialNumber);
        const img  = new Image();
        img.id     = 'qr-image';
        img.alt    = 'QR Code ' + serialNumber;
        img.style.cssText = 'width:220px;height:220px;display:block;border-radius:4px;';

        img.onload = () => {
            wrapper.innerHTML = '';
            wrapper.appendChild(img);
        };
        img.onerror = () => {
            wrapper.innerHTML = `<div style="color:#b91c1c;font-size:.8rem;text-align:center;padding:12px;">
                Gagal generate QR Code.<br>Pastikan server berjalan.
            </div>`;
        };

        img.src = url;
    }

    /* ── Back to serial list ── */
    function showSerialList() {
        document.getElementById('qr-panel-serials').style.display = 'flex';
        document.getElementById('qr-panel-qr').style.display = 'none';
    }

    /* ── Close modal ── */
    function closeQrModal() {
        document.getElementById('qr-modal-overlay').classList.add('hidden');
        document.body.style.overflow = '';
        showSerialList();
    }

    /* ── Download QR PNG ── */
    async function downloadQr() {
        const img = document.getElementById('qr-image');
        if (!img) return;

        try {
            // Fetch via backend (same origin — no CORS issue)
            const res   = await fetch(img.src);
            const blob  = await res.blob();
            const objUrl = URL.createObjectURL(blob);
            const a     = document.createElement('a');
            a.href      = objUrl;
            a.download  = 'QR-SN-' + _currentSerial.replace(/[^a-zA-Z0-9_-]/g, '_') + '.png';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            setTimeout(() => URL.revokeObjectURL(objUrl), 1000);
        } catch(e) {
            alert('Download gagal: ' + e.message);
        }
    }

    function _esc(s) { return String(s??'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeQrModal(); });
</script>
@endpush
