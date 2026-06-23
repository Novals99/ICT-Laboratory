@extends('panel.content')
@section('title', auth()->user()->role === 'spv inventory' ? 'SPV Dashboard' : 'Staff Dashboard')

@section('content')

@php
$isSPV = auth()->user()->role === 'spv inventory';
$electronicAssets    = $allAssets->filter(fn($a) => $a->asset_category === 'electronic')->values();
$nonElectronicAssets = $allAssets->filter(fn($a) => $a->asset_category !== 'electronic')->values();
$componentPcAssets    = $allAssets->filter(fn($a) => $a->asset_category === 'component-pc')->values(); // ← tambahan

// (#9) Komponen gudang + serial yang available (belum masuk lab) untuk wizard Create Lab
$wizardComponents = \App\Models\Asset::where('asset_category', 'component-pc')
    ->whereHas('serialNumbers', fn($q) => $q->where('status', 'available')->whereNull('lab_id'))
    ->with(['serialNumbers' => fn($q) => $q->where('status', 'available')->whereNull('lab_id')->orderBy('serial_number')])
    ->orderBy('asset_name')
    ->get()
    ->groupBy('component_type')
    ->map(fn($assets) => $assets->map(fn($a) => [
        'asset_id'   => $a->id,
        'asset_name' => $a->asset_name,
        'serials'    => $a->serialNumbers->map(fn($s) => ['id' => $s->id, 'serial_number' => $s->serial_number])->values(),
    ])->values());
@endphp

<div class="panel-page-card">

    {{-- header --}}
    <div class="mb-5 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <h2 class="panel-page-title">
            Laboratory List
        </h2>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
             {{-- search --}}
            <x-button.search.modul-search
                :action="route('laboratory.index')"
                name="search"
                :value="request('search')"
                placeholder="Search..."
            />

            {{-- Export --}}
            <x-button.export.export
                menuId="labExportMenu"
                pdfUrl="{{ route('laboratory.export', 'pdf') }}"
                excelUrl="{{ route('laboratory.export', 'excel') }}"
                csvUrl="{{ route('laboratory.export', 'csv') }}"
            />

            {{-- Add Lab --}}
            @if($isSPV)
                <x-button.add type="button" onclick="openCreateModal()">
                    Add Lab
                </x-button.add>
            @endif
        </div>
    </div>

    {{-- alert --}}
    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-100 px-4 py-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    {{-- Table --}}
    <div id="labTableWrapper">
        <x-table.index>
            <thead>
                <tr>
                    <x-table.th class="w-12">
                        <x-table.checkbox id="checkAll" />
                    </x-table.th>

                    <x-table.th>Name</x-table.th>
                    <x-table.th>PC Capacity</x-table.th>
                    <x-table.th>Staff</x-table.th>
                    <x-table.th>Active</x-table.th>
                    <x-table.th>Inactive</x-table.th>
                    <x-table.th align="center">Action</x-table.th>
                </tr>
            </thead>

            <tbody>
                @forelse($laboratories as $lab)
                @php
                    $isMyLab    = in_array($lab->id, $myLabIds);
                    $staffUsers = $lab->users->where('role', 'staff')->values();
                @endphp

                    <tr class="panel-table-row">
                        <x-table.td>
                            <x-table.checkbox class="row-check" />
                        </x-table.td>

                        <x-table.td>
                            <span class="font-semibold">
                                {{ $lab->lab_name }}
                            </span>
                        </x-table.td>

                        <x-table.td>
                            {{ $lab->capacity }} PC
                        </x-table.td>

                        <x-table.td>
                            @forelse($staffUsers as $s)
                                <span class="m-0.5 inline-block rounded-md bg-indigo-50 px-2 py-0.5 text-xs font-semibold text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300">
                                    {{ $s->name }}
                                </span>
                            @empty
                                <span class="text-gray-400">—</span>
                            @endforelse
                        </x-table.td>

                        <x-table.td>
                            <span class="font-semibold text-green-600">
                                {{ $lab->total_pc_active ?? 0 }}
                            </span>
                        </x-table.td>

                        <x-table.td>
                            <span class="font-semibold text-red-600">
                                {{ $lab->total_pc_inactive ?? 0 }}
                            </span>
                        </x-table.td>

                        <x-table.td align="center">
                            <div class="flex items-center justify-center gap-1">
                                @if($isSPV || $isMyLab)
                                    {{-- Edit --}}
                                    <x-table.action
                                        href="{{ route('laboratory.show', $lab->id) }}"
                                        variant="edit"
                                        title="Edit"
                                    >
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                             stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                        </svg>
                                    </x-table.action>

                                    {{-- Delete (#11: hanya SPV) --}}
                                    @if($isSPV)
                                    <form
                                        method="POST"
                                        action="{{ route('laboratory.destroy', $lab->id) }}"
                                        onsubmit="return confirm('Pindahkan lab {{ addslashes($lab->lab_name) }} ke Recycle Bin?')"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <x-table.action
                                            type="submit"
                                            variant="delete"
                                            title="Delete"
                                        >
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                 stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="3 6 5 6 21 6"/>
                                                <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                                                <path d="M10 11v6M14 11v6"/>
                                                <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                                            </svg>
                                        </x-table.action>
                                    </form>
                                    @endif
                                @else
                                    {{-- View --}}
                                    <x-table.action
                                        href="{{ route('laboratory.show', $lab->id) }}"
                                        variant="view"
                                        title="View"
                                    >
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                             stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                            <circle cx="12" cy="12" r="3"/>
                                        </svg>
                                    </x-table.action>
                                @endif
                            </div>
                        </x-table.td>
                    </tr>
                @empty
                    <x-table.empty
                        colspan="7"
                        message="Laboratory data not found."
                    />
                @endforelse
            </tbody>
        </x-table.index>
    </div>

    {{-- pagination --}}
    {{-- @if($laboratories->hasPages())
        <div class="mt-5">
            {{ $laboratories->links() }}
        </div>
    @endif --}}
</div>

{{-- Form bulk delete tersembunyi --}}
@if($isSPV)
<form id="bulkDeleteForm" method="POST" action="{{ route('laboratory.bulkDestroy') }}" style="display:none;">
    @csrf @method('DELETE')
</form>
@endif

{{-- ══ MODAL CREATE (SPV only) ══ --}}
@if($isSPV)
<div id="modal-create" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45); z-index:50; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:16px; width:100%; max-width:560px; margin:0 16px; box-shadow:0 20px 60px rgba(0,0,0,0.15);">

        <div style="display:flex; align-items:center; justify-content:space-between; padding:20px 24px; border-bottom:1px solid #e5e7eb;">
            <h3 style="font-size:16px; font-weight:700; color:#111827; margin:0;">Create Laboratory</h3>
            <button onclick="closeCreateModal()" style="background:none; border:none; cursor:pointer; color:#9ca3af; font-size:22px;">&times;</button>
        </div>

        <div style="display:flex; padding:16px 24px 0; gap:6px;">
            <div id="clab-bar-1" style="flex:1; height:4px; border-radius:2px; background:#111B4C;"></div>
            <div id="clab-bar-2" style="flex:1; height:4px; border-radius:2px; background:#e5e7eb;"></div>
            <div id="clab-bar-3" style="flex:1; height:4px; border-radius:2px; background:#e5e7eb;"></div>
        </div>

        <form method="POST" action="{{ route('laboratory.store') }}" id="createLabForm">
            @csrf

            {{-- Step 1: General --}}
            <div id="clab-step-1" style="padding:24px;">
                <h4 style="font-size:15px; font-weight:600; color:#374151; margin:0 0 20px; text-align:center;">General Information</h4>
                <div style="margin-bottom:16px;">
                    <label style="font-size:13px; font-weight:500; color:#374151; display:block; margin-bottom:6px;">Laboratory Name:</label>
                    <input type="text" name="lab_name" id="clab_name" placeholder="Enter here..."
                           style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px 14px; font-size:14px; outline:none; box-sizing:border-box;" required>
                </div>
                <div style="margin-bottom:16px;">
                    <label style="font-size:13px; font-weight:500; color:#374151; display:block; margin-bottom:6px;">PC Capacity:</label>
                    @php $pcStock = \App\Models\Asset::where('asset_category','pc')->sum('total_good'); @endphp
                    <input type="number" name="capacity" id="clab_capacity" placeholder="Enter here..." min="1"
                           @if($pcStock > 0) max="{{ $pcStock }}" @endif
                           style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px 14px; font-size:14px; outline:none; box-sizing:border-box;" required>
                    @if($pcStock > 0)
                    <small style="display:block; margin-top:6px; color:#6b7280;">Maksimal {{ $pcStock }} unit (stok PC di Inventory &amp; Stock).</small>
                    @endif
                </div>
                <div style="display:flex; justify-content:flex-end; gap:8px; margin-top:24px;">
                    <button type="button" onclick="closeCreateModal()"
                            style="border:1px solid #d1d5db; background:#fff; border-radius:8px; padding:9px 20px; font-size:13px; cursor:pointer;">Cancel</button>
                    <button type="button" onclick="clabGoStep(2)"
                            style="background:#111B4C; color:#fff; border:none; border-radius:8px; padding:9px 20px; font-size:13px; cursor:pointer; font-weight:600;">Next</button>
                </div>
            </div>

            {{-- Step 2: PC --}}
            <div id="clab-step-2" style="display:none; padding:24px; max-height:55vh; overflow-y:auto;">
                <h4 style="font-size:15px; font-weight:600; color:#374151; margin:0 0 16px; text-align:center;">PC Information</h4>
                <div id="clab-pc-list"></div>
                <div style="display:flex; justify-content:space-between; gap:8px; margin-top:24px;">
                    <button type="button" onclick="clabGoStep(1)"
                            style="border:1px solid #d1d5db; background:#fff; border-radius:8px; padding:9px 20px; font-size:13px; cursor:pointer;">Back</button>
                    <button type="button" onclick="clabGoStep(3)"
                            style="background:#111B4C; color:#fff; border:none; border-radius:8px; padding:9px 20px; font-size:13px; cursor:pointer; font-weight:600;">Next</button>
                </div>
            </div>

            {{-- Step 3: Asset --}}
            <div id="clab-step-3" style="display:none; padding:24px; max-height:55vh; overflow-y:auto;">
                <h4 style="font-size:15px; font-weight:600; color:#374151; margin:0 0 16px; text-align:center;">Asset Information</h4>

                <div style="border:1px solid #e5e7eb; border-radius:10px; margin-bottom:12px; overflow:hidden;">
                    <div style="display:flex; align-items:center; justify-content:space-between; padding:10px 14px; background:#f9fafb; border-bottom:1px solid #e5e7eb;">
                        <span style="font-size:13px; font-weight:600; color:#374151;">Electronic Category</span>
                        <button type="button" onclick="addCreateAssetRow('electronic')"
                                style="background:#111B4C; color:#fff; border:none; border-radius:6px; padding:5px 12px; font-size:13px; cursor:pointer;">+</button>
                    </div>
                    <div id="clab-electronic-rows" style="padding:12px; display:flex; flex-direction:column; gap:10px;"></div>
                </div>

                <div style="border:1px solid #e5e7eb; border-radius:10px; overflow:hidden;">
                    <div style="display:flex; align-items:center; justify-content:space-between; padding:10px 14px; background:#f9fafb; border-bottom:1px solid #e5e7eb;">
                        <span style="font-size:13px; font-weight:600; color:#374151;">Non-Electronic Category</span>
                        <button type="button" onclick="addCreateAssetRow('non-electronic')"
                                style="background:#111B4C; color:#fff; border:none; border-radius:6px; padding:5px 12px; font-size:13px; cursor:pointer;">+</button>
                    </div>
                    <div id="clab-non-electronic-rows" style="padding:12px; display:flex; flex-direction:column; gap:10px;"></div>
                </div>

                <div style="display:flex; justify-content:space-between; gap:8px; margin-top:24px;">
                    <button type="button" onclick="clabGoStep(2)"
                            style="border:1px solid #d1d5db; background:#fff; border-radius:8px; padding:9px 20px; font-size:13px; cursor:pointer;">Back</button>
                    <button type="submit"
                            style="background:#111B4C; color:#fff; border:none; border-radius:8px; padding:9px 20px; font-size:13px; cursor:pointer; font-weight:600;">Create</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
const electronicOptions   = @json($electronicAssets->map(fn($a) => ['id'=>$a->id,'name'=>$a->asset_name])->values());
const nonElecOptions      = @json($nonElectronicAssets->map(fn($a) => ['id'=>$a->id,'name'=>$a->asset_name])->values());
let   createAssetCounter  = 0;

function filterTable() {
    const searchInput = document.getElementById('searchInput');
    const wrapper = document.getElementById('labTableWrapper');

    if (!searchInput || !wrapper) return;

    const q = searchInput.value.toLowerCase();

    wrapper.querySelectorAll('tbody tr').forEach((row) => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
}

const checkAll = document.getElementById('checkAll');

if (checkAll) {
    checkAll.addEventListener('change', function () {
        document.querySelectorAll('.row-check').forEach((checkbox) => {
            checkbox.checked = this.checked;
        });
    });
}

function submitBulkDelete() {
    const checked = document.querySelectorAll('.row-check:checked');
    if (checked.length === 0) {
        alert('Pilih minimal satu lab.');
        return;
    }
    if (!confirm('Pindahkan ' + checked.length + ' lab yang dipilih ke Recycle Bin?')) {
        return;
    }

    const form = document.getElementById('bulkDeleteForm');
    // Hapus input lama
    form.querySelectorAll('input[name="ids[]"]').forEach(i => i.remove());
    // Tambah input baru
    checked.forEach(cb => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'ids[]';
        input.value = cb.value;
        form.appendChild(input);
    });

    form.submit();
}

@if($isSPV)
// Create Modal
function openCreateModal() {
    document.getElementById('modal-create').style.display = 'flex';
    clabGoStep(1);
}
function closeCreateModal() {
    document.getElementById('modal-create').style.display = 'none';
    document.getElementById('createLabForm').reset();
    document.getElementById('clab-pc-list').innerHTML = '';
    document.getElementById('clab-electronic-rows').innerHTML = '';
    document.getElementById('clab-non-electronic-rows').innerHTML = '';
    createAssetCounter = 0;
}

function clabGoStep(step) {
    [1,2,3].forEach(s => {
        document.getElementById(`clab-step-${s}`).style.display = s === step ? 'block' : 'none';
        document.getElementById(`clab-bar-${s}`).style.background =
            s < step ? '#98083D' : s === step ? '#111B4C' : '#e5e7eb';
    });
    if (step === 2) {
        const cap = parseInt(document.getElementById('clab_capacity').value);
        if (!cap || cap < 1) { alert('Isi kapasitas terlebih dahulu.'); clabGoStep(1); return; }
        buildCreatePcList(cap);
    }
}
// (#9) Komponen + serial number untuk wizard Create Lab
const wizardComponents = @json($wizardComponents);
const wizSlots = [
    { slot: 'processor',   label: 'Processor',        type: 'processor' },
    { slot: 'ram',         label: 'RAM',              type: 'ram' },
    { slot: 'ram2',        label: 'RAM 2 (opsional)', type: 'ram' },
    { slot: 'ssd',         label: 'SSD',              type: 'ssd' },
    { slot: 'motherboard', label: 'Motherboard',      type: 'motherboard' },
    { slot: 'vga',         label: 'VGA',              type: 'vga' },
    { slot: 'cpu_fan',     label: 'CPU Fan',          type: 'cpu_fan' },
    { slot: 'powersupply', label: 'Power Supply',     type: 'powersupply' },
];

function wizAssetOptions(type) {
    const list = wizardComponents[type] || [];
    return '<option value="">— Pilih komponen —</option>' +
        list.map(a => `<option value="${a.asset_id}">${a.asset_name}</option>`).join('');
}

function buildCreatePcList(cap) {
    const c = document.getElementById('clab-pc-list');
    c.innerHTML = '';

    for (let i = 0; i < cap; i++) {
        const slotsHtml = wizSlots.map(s => `
            <div data-slot-row>
                <label style="font-size:12px; color:#6b7280; display:block; margin-bottom:4px;">${s.label}</label>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px;">
                    <select class="wiz-asset" data-slot="${s.slot}" data-type="${s.type}"
                            style="width:100%; border:1px solid #d1d5db; border-radius:6px; padding:8px 10px; font-size:13px; box-sizing:border-box;">
                        ${wizAssetOptions(s.type)}
                    </select>
                    <select name="pcs[${i}][${s.slot}_serial_id]" class="wiz-serial" data-slot="${s.slot}"
                            style="width:100%; border:1px solid #d1d5db; border-radius:6px; padding:8px 10px; font-size:13px; box-sizing:border-box;">
                        <option value="">— Serial Number —</option>
                    </select>
                </div>
            </div>`).join('');

        c.innerHTML += `
        <details style="border:1px solid #e5e7eb; border-radius:10px; margin-bottom:10px;" ${i === 0 ? 'open' : ''}>
            <summary style="padding:10px 14px; font-size:13px; font-weight:600; cursor:pointer; background:#f9fafb;">
                PC-${String(i).padStart(2,'0')}
            </summary>
            <div style="padding:14px; display:grid; gap:10px;">
                <div>
                    <label style="font-size:12px; color:#6b7280; display:block; margin-bottom:4px;">Type</label>
                    <select name="pcs[${i}][type_pc]" style="width:100%; border:1px solid #d1d5db; border-radius:6px; padding:8px 10px; font-size:13px;">
                        <option value="mahasiswa">Mahasiswa</option>
                        <option value="dosen">Dosen</option>
                    </select>
                </div>
                ${slotsHtml}
            </div>
        </details>`;
    }

    // Wiring event setelah HTML masuk DOM.
    c.querySelectorAll('.wiz-asset').forEach(assetSel => {
        assetSel.addEventListener('change', () => {
            const row = assetSel.closest('[data-slot-row]');
            const serialSel = row.querySelector('.wiz-serial');
            fillWizSerials(serialSel, assetSel.dataset.type, assetSel.value, null);
        });
    });
    c.querySelectorAll('.wiz-serial').forEach(serialSel => {
        serialSel.addEventListener('change', refreshWizardSerials);
    });
}

function fillWizSerials(serialSel, type, assetId, selectId) {
    serialSel.innerHTML = '<option value="">— Serial Number —</option>';
    const list = wizardComponents[type] || [];
    const asset = list.find(a => String(a.asset_id) === String(assetId));
    if (asset) {
        asset.serials.forEach(s => {
            const opt = document.createElement('option');
            opt.value = s.id;
            opt.textContent = s.serial_number;
            if (selectId && String(s.id) === String(selectId)) opt.selected = true;
            serialSel.appendChild(opt);
        });
    }
    refreshWizardSerials();
}

// Satu serial number tidak boleh dipakai dua slot/PC dalam satu lab.
function refreshWizardSerials() {
    const selects = [...document.querySelectorAll('#clab-pc-list .wiz-serial')];
    const chosen = selects.map(s => s.value).filter(Boolean);
    selects.forEach(sel => {
        [...sel.options].forEach(opt => {
            if (!opt.value) return;
            opt.disabled = chosen.includes(opt.value) && sel.value !== opt.value;
        });
    });
}

document.addEventListener('click', e => {
    document.querySelectorAll('[id^="clab_"][id$="_dropdown"]').forEach(dropdown => {
        const baseId = dropdown.id.replace('_dropdown', '');
        const search = document.getElementById(`${baseId}_search`);
        if (search && !search.contains(e.target) && !dropdown.contains(e.target)) {
            if (dropdown.style.display !== 'none') {
                document.getElementById(`${baseId}_val`).value = search.value;
                dropdown.style.display = 'none';
            }
        }
    });
});
function addCreateAssetRow(type) {
    const idx       = createAssetCounter++;
    const container = document.getElementById(type === 'electronic' ? 'clab-electronic-rows' : 'clab-non-electronic-rows');
    const options   = type === 'electronic' ? electronicOptions : nonElecOptions;
    const div = document.createElement('div');
    div.className = 'asset-row';
    div.style.cssText = 'border:1px solid #e5e7eb; border-radius:8px; padding:12px; position:relative;';
    div.innerHTML = `
        <button type="button" onclick="this.closest('.asset-row').remove()"
                class="absolute top-1.5 right-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 text-base bg-transparent border-none cursor-pointer">×</button>
        <div class="grid grid-cols-2 gap-2.5">
            <div>
                <label class="block text-xs text-gray-500 dark:text-gray-300 mb-1">Asset Name:</label>
                <select name="lab_assets[${idx}][asset_id]"
                        class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-md px-2.5 py-2 text-sm box-border">
                    <option value="">Choose asset...</option>
                    ${options.map(o => `<option value="${o.id}">${o.name}</option>`).join('')}
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 dark:text-gray-300 mb-1">Quantity:</label>
                <input type="number" name="lab_assets[${idx}][quantity]" value="0" min="0"
                    class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-md px-2.5 py-2 text-sm box-border">
            </div>
        </div>
    `;
    container.appendChild(div);
}

document.getElementById('modal-create').addEventListener('click', e => {
    if (e.target === e.currentTarget) closeCreateModal();
});
@endif
</script>
@endpush
