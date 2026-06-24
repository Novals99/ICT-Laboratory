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
                                    {{ $asset->asset_name }}{{ $asset->specification ? ' - ' . $asset->specification : '' }}
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
