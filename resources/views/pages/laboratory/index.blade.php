@extends('panel.content')

@section('title', 'Laboratory')

@section('content')

{{-- Ambil data komponen PC langsung dari view (standalone) --}}
@php
$pcComponents = \App\Models\Asset::where('asset_category', 'component-pc')
    ->get(['id', 'asset_name as name', 'total_good as stock']);
@endphp

<div class="panel-page-card">

    {{-- Header --}}
    <div class="mb-5 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <h2 class="panel-page-title">Laboratory List</h2>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <x-button.search.modul-search :action="route('laboratory.index')" name="search" :value="request('search')" placeholder="Search..." />
            <x-button.filter type="button" onclick="document.getElementById('labFilterPanel').classList.toggle('hidden')">Filter</x-button.filter>
            <button type="button" onclick="openCreateLabModal()"
                class="px-4 py-2 bg-[#111B4C] text-white rounded-lg text-sm font-medium hover:bg-[#1a237e] flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 4v16m8-8H4"/></svg>
                Add Lab
            </button>
        </div>
    </div>

    {{-- Filter Panel --}}
    <div id="labFilterPanel" class="hidden mb-4 p-4 bg-gray-50 rounded-lg border border-gray-100">
        <form method="GET" action="{{ route('laboratory.index') }}" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Dari Tanggal</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Sampai Tanggal</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
            </div>
            <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-lg text-sm">Terapkan</button>
            @if(request('date_from') || request('date_to'))
                <a href="{{ route('laboratory.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-600 hover:bg-gray-50">Reset</a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <x-table.index>
        <thead>
            <tr>
                <x-table.th class="w-12"><x-table.checkbox id="checkAll" /></x-table.th>
                <x-table.th>Name</x-table.th>
                <x-table.th>PC Capacity</x-table.th>
                <x-table.th>Active</x-table.th>
                <x-table.th>Inactive</x-table.th>
                <x-table.th align="center">Action</x-table.th>
            </tr>
        </thead>
        <tbody>
            @forelse ($laboratories as $lab)
                <tr class="panel-table-row">
                    <x-table.td><x-table.checkbox name="selected_labs[]" :value="$lab->id" class="row-check" /></x-table.td>
                    <x-table.td>{{ $lab->lab_name }}</x-table.td>
                    <x-table.td>{{ $lab->capacity }} PC</x-table.td>
                    <x-table.td>{{ $lab->pcs->where('status_pc', 'active')->count() }}</x-table.td>
                    <x-table.td>{{ $lab->pcs->where('status_pc', 'inactive')->count() }}</x-table.td>
                    <x-table.td align="center">
                        <div class="flex items-center justify-center gap-1">
                            <x-table.action href="{{ route('laboratory.show', $lab->id) }}" variant="view" title="Detail">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/></svg>
                            </x-table.action>
                            <x-table.action type="button" variant="edit" title="Edit" onclick="alert('Edit belum diimplementasikan')">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            </x-table.action>
                            <form method="POST" action="{{ route('laboratory.destroy', $lab->id) }}" onsubmit="return confirm('Hapus lab ini?')" class="inline">
                                @csrf @method('DELETE')
                                <x-table.action type="submit" variant="delete" title="Delete">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                                </x-table.action>
                            </form>
                        </div>
                    </x-table.td>
                </tr>
            @empty
                <x-table.empty colspan="6" message="Belum ada data laboratory." />
            @endforelse
        </tbody>
    </x-table.index>

    {{-- Pagination --}}
    <div class="mt-5">
        {{ $laboratories->links() }}
    </div>
</div>

{{-- ═══════════════════════════════════════════════
    MODAL CREATE LABORATORY
    ═══════════════════════════════════════════════ --}}
<div id="modal-create-lab" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45); z-index:50; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:16px; width:100%; max-width:600px; margin:0 16px; box-shadow:0 20px 60px rgba(0,0,0,0.15); max-height:90vh; display:flex; flex-direction:column; overflow:hidden;">

        {{-- Header --}}
        <div style="display:flex; align-items:center; justify-content:space-between; padding:20px 24px; border-bottom:1px solid #e5e7eb; flex-shrink:0;">
            <h3 style="font-size:16px; font-weight:700; color:#111827; margin:0;">Create Laboratory</h3>
            <button onclick="closeCreateLabModal()" style="background:none; border:none; cursor:pointer; color:#9ca3af; font-size:22px;">&times;</button>
        </div>

        {{-- Progress Bar --}}
        <div style="display:flex; height:4px; flex-shrink:0;">
            <div style="flex:1; background:#be123c;"></div>
            <div style="flex:1; background:#111B4C;"></div>
            <div style="flex:1; background:#e5e7eb;"></div>
        </div>

        <form method="POST" action="{{ route('laboratory.store') }}" style="overflow-y:auto; flex:1; padding:24px; display:flex; flex-direction:column; gap:16px;">
            @csrf

            {{-- Lab Info --}}
            <div style="display:flex; flex-direction:column; gap:14px;">
                <div>
                    <label style="font-size:13px; font-weight:500; color:#374151; display:block; margin-bottom:6px;">Laboratory Name</label>
                    <input type="text" name="lab_name" required placeholder="Enter lab name..."
                        style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px 14px; font-size:13px; outline:none; box-sizing:border-box;">
                </div>
                <div>
                    <label style="font-size:13px; font-weight:500; color:#374151; display:block; margin-bottom:6px;">PC Capacity</label>
                    <input type="number" name="capacity" required min="1" placeholder="e.g. 30"
                        style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px 14px; font-size:13px; outline:none; box-sizing:border-box;">
                </div>
            </div>

            {{-- PC Information Section --}}
            <div>
                <h4 style="font-size:14px; font-weight:600; color:#111827; margin:0 0 12px; text-align:center;">PC Information</h4>

                <div id="pcListContainer" style="display:flex; flex-direction:column; gap:10px;">
                    {{-- PC items akan ditambahkan di sini via JS --}}
                </div>

                <button type="button" onclick="addPcItem()"
                    style="margin-top:10px; width:100%; padding:10px; border:1px dashed #d1d5db; border-radius:8px; background:#f9fafb; color:#374151; font-size:13px; cursor:pointer; font-weight:500; display:flex; align-items:center; justify-content:center; gap:6px;">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 4v16m8-8H4"/></svg>
                    Add PC
                </button>
            </div>

            {{-- Footer --}}
            <div style="display:flex; justify-content:flex-end; gap:8px; padding-top:8px; border-top:1px solid #f3f4f6; flex-shrink:0;">
                <button type="button" onclick="closeCreateLabModal()"
                    style="border:1px solid #d1d5db; background:#fff; border-radius:8px; padding:9px 20px; font-size:13px; cursor:pointer;">Cancel</button>
                <button type="submit"
                    style="background:#111B4C; color:#fff; border:none; border-radius:8px; padding:9px 20px; font-size:13px; cursor:pointer; font-weight:600;">Save</button>
            </div>
        </form>
    </div>
</div>

{{-- Template PC Item (hidden) --}}
<<template id="pcItemTemplate">
    <div class="pc-item-card" data-pc-item style="border:1px solid #e5e7eb; border-radius:10px; overflow:hidden;">
        {{-- Accordion Header --}}
        <div onclick="togglePcAccordion(this)" style="display:flex; align-items:center; justify-content:space-between; padding:12px 16px; background:#f9fafb; cursor:pointer; user-select:none;">
            <div style="display:flex; align-items:center; gap:8px;">
                <span class="pc-arrow" style="font-size:12px; color:#6b7280; transition:transform 0.2s;">▶</span>
                <span class="pc-title" style="font-size:13px; font-weight:600; color:#374151;">PC-00</span>
            </div>
            <button type="button" onclick="event.stopPropagation(); removePcItem(this)" style="background:none; border:none; cursor:pointer; color:#9ca3af; font-size:16px; padding:0 4px;">&times;</button>
        </div>

        {{-- Accordion Body --}}
        <div class="pc-body" style="display:none; padding:16px; display:flex; flex-direction:column; gap:12px;">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div>
                    <label style="font-size:12px; font-weight:500; color:#374151; display:block; margin-bottom:4px;">Type</label>
                    <select data-name="type_pc" style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:8px 12px; font-size:13px; outline:none; box-sizing:border-box;">
                        <option value="mahasiswa">Mahasiswa</option>
                        <option value="dosen">Dosen</option>
                    </select>
                </div>
                <div>
                    <label style="font-size:12px; font-weight:500; color:#374151; display:block; margin-bottom:4px;">Processor</label>
                    <input type="hidden" data-name="processor" data-hidden>
                    <input type="text" data-search="processor" placeholder="Search component..." autocomplete="off"
                        style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:8px 12px; font-size:13px; outline:none; box-sizing:border-box;">
                    <div data-dropdown="processor" style="display:none; position:relative; z-index:100; background:#fff; border:1px solid #d1d5db; border-radius:8px; width:100%; max-height:140px; overflow-y:auto; box-shadow:0 4px 12px rgba(0,0,0,.1); margin-top:2px;"></div>
                </div>
            </div>

            @foreach(['ram'=>'RAM','ssd'=>'SSD','motherboard'=>'Motherboard','vga'=>'VGA','cpu_fan'=>'CPU Fan','powersupply'=>'Power Supply'] as $field => $label)
            <div style="position:relative;">
                <label style="font-size:12px; font-weight:500; color:#374151; display:block; margin-bottom:4px;">{{ $label }}</label>
                <input type="hidden" data-name="{{ $field }}" data-hidden>
                <input type="text" data-search="{{ $field }}" placeholder="Search component or type manually..." autocomplete="off"
                    style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:8px 12px; font-size:13px; outline:none; box-sizing:border-box;">
                <div data-dropdown="{{ $field }}" style="display:none; position:relative; z-index:100; background:#fff; border:1px solid #d1d5db; border-radius:8px; width:100%; max-height:140px; overflow-y:auto; box-shadow:0 4px 12px rgba(0,0,0,.1); margin-top:2px;"></div>
            </div>
            @endforeach
        </div>
    </div>
</template>

@endsection

@push('scripts')
<script>
// ── Data Komponen PC dari Asset Master (component-pc) ──
const pcComponents = @json($pcComponents);
const pcFields = ['processor','ram','ssd','motherboard','vga','cpu_fan','powersupply'];
let pcIndex = 0;

// ── Modal Control ──
function openCreateLabModal() {
    document.getElementById('modal-create-lab').style.display = 'flex';
    if (document.getElementById('pcListContainer').children.length === 0) {
        addPcItem();
    }
}
function closeCreateLabModal() {
    document.getElementById('modal-create-lab').style.display = 'none';
}
document.getElementById('modal-create-lab').addEventListener('click', function(e) {
    if (e.target === this) closeCreateLabModal();
});

// ── Accordion ──
function togglePcAccordion(header) {
    const card = header.closest('[data-pc-item]');
    const body = card.querySelector('.pc-body');
    const arrow = card.querySelector('.pc-arrow');
    const isHidden = body.style.display === 'none' || getComputedStyle(body).display === 'none';

    if (isHidden) {
        body.style.display = 'flex';
        arrow.style.transform = 'rotate(90deg)';
    } else {
        body.style.display = 'none';
        arrow.style.transform = 'rotate(0deg)';
    }
}

// ── Add / Remove PC ──
function addPcItem() {
    const container = document.getElementById('pcListContainer');
    const template = document.getElementById('pcItemTemplate');
    const clone = template.content.cloneNode(true);
    const card = clone.querySelector('[data-pc-item]');

    // Set title
    card.querySelector('.pc-title').textContent = 'PC-' + String(pcIndex).padStart(2, '0');

    // Set name indexes
    card.querySelectorAll('[data-name]').forEach(input => {
        const name = input.dataset.name;
        if (input.tagName === 'SELECT') {
            input.name = `pcs[${pcIndex}][${name}]`;
        } else {
            input.name = `pcs[${pcIndex}][${name}]`;
        }
    });

    // Bind dropdowns
    pcFields.forEach(field => bindDropdown(card, field));

    container.appendChild(card);
    pcIndex++;

    // Auto expand yang baru
    const header = card.querySelector('[onclick="togglePcAccordion(this)"]');
    if (header) {
        const body = card.querySelector('.pc-body');
        const arrow = card.querySelector('.pc-arrow');
        body.style.display = 'flex';
        arrow.style.transform = 'rotate(90deg)';
    }
}

function removePcItem(btn) {
    const container = document.getElementById('pcListContainer');
    const item = btn.closest('[data-pc-item]');
    if (!item) return;

    const total = container.querySelectorAll('[data-pc-item]').length;
    if (total <= 1) {
        alert('Minimal harus ada 1 PC.');
        return;
    }

    item.remove();
    updatePcIndexes();
}

function updatePcIndexes() {
    const items = document.querySelectorAll('#pcListContainer [data-pc-item]');
    items.forEach((item, idx) => {
        item.querySelector('.pc-title').textContent = 'PC-' + String(idx).padStart(2, '0');
        item.querySelectorAll('[data-name]').forEach(input => {
            const name = input.dataset.name;
            input.name = `pcs[${idx}][${name}]`;
        });
    });
    pcIndex = items.length;
}

// ── Dropdown Komponen PC ──
function bindDropdown(card, field) {
    const searchInput = card.querySelector(`[data-search="${field}"]`);
    const hiddenInput = card.querySelector(`[data-name="${field}"][data-hidden]`);
    const dropdown = card.querySelector(`[data-dropdown="${field}"]`);

    if (!searchInput || !dropdown) return;

    searchInput.addEventListener('input', () => filterPcDropdown(searchInput, dropdown, hiddenInput));
    searchInput.addEventListener('focus', () => filterPcDropdown(searchInput, dropdown, hiddenInput));

    // Close when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.style.display = 'none';
            if (hiddenInput && searchInput.value) {
                hiddenInput.value = searchInput.value;
            }
        }
    });
}

function filterPcDropdown(searchInput, dropdown, hiddenInput) {
    const query = searchInput.value.toLowerCase();
    const filtered = pcComponents.filter(c => c.name.toLowerCase().includes(query));

    dropdown.innerHTML = '';

    // Manual option
    const manualOpt = document.createElement('div');
    manualOpt.textContent = '— Ketik Manual —';
    manualOpt.style.cssText = 'padding:8px 12px; font-size:12px; cursor:pointer; color:#9ca3af; border-bottom:1px solid #f3f4f6;';
    manualOpt.onmousedown = () => {
        hiddenInput.value = searchInput.value;
        dropdown.style.display = 'none';
    };
    dropdown.appendChild(manualOpt);

    if (filtered.length > 0) {
        filtered.forEach(comp => {
            const disabled = comp.stock < 1;
            const item = document.createElement('div');
            item.style.cssText = `padding:8px 12px; font-size:12px; cursor:${disabled?'not-allowed':'pointer'}; display:flex; justify-content:space-between; align-items:center;`;
            item.innerHTML = `
                <span style="color:${disabled?'#9ca3af':'#374151'};">${comp.name}</span>
                <span style="font-size:11px; background:${disabled?'#fee2e2':'#dcfce7'}; color:${disabled?'#dc2626':'#16a34a'}; padding:2px 6px; border-radius:4px; font-weight:600;">
                    Stok: ${comp.stock}
                </span>`;
            if (!disabled) {
                item.onmousedown = () => {
                    hiddenInput.value = comp.name;
                    searchInput.value = comp.name;
                    dropdown.style.display = 'none';
                };
                item.onmouseover = () => item.style.background = '#f9fafb';
                item.onmouseout  = () => item.style.background = '';
            }
            dropdown.appendChild(item);
        });
    }

    dropdown.style.display = 'block';
}

// Check all
document.getElementById('checkAll')?.addEventListener('change', function() {
    document.querySelectorAll('.row-check').forEach(cb => cb.checked = this.checked);
});
</script>
@endpush
