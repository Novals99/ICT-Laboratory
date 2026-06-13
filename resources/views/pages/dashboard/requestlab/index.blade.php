@extends('panel.content')

@section('title', 'Request Lab')

@section('content')
@php
    $role = strtolower(auth()->user()->role ?? 'assistant');
    $isSPV = str_contains($role, 'spv inventory') || str_contains($role, 'spv');
@endphp

<div class="bg-white rounded-2xl p-6 shadow-sm">
    {{-- Header --}}
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-6 gap-4">
        <h2 class="text-2xl font-bold text-gray-800">Request List</h2>

        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
            {{-- Search --}}
            <form action="{{ route('requestlab.index') }}" method="GET" class="relative">
                <span class="absolute inset-y-0 left-3 flex items-center text-gray-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
                    </svg>
                </span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..."
                    class="w-56 pl-9 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-gray-400">
            </form>

            {{-- Filter Toggle --}}
            <button type="button" onclick="document.getElementById('filterPanel').classList.toggle('hidden')"
                class="flex items-center gap-2 px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-600 hover:bg-gray-50">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 4h18M7 8h10M11 12h2M9 16h6"/>
                </svg>
                Filter
            </button>

            @if($isSPV)
                {{-- Export --}}
                <div class="relative">
                    <button type="button" onclick="document.getElementById('exportMenu').classList.toggle('hidden')"
                        class="flex items-center gap-2 px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-600 hover:bg-gray-50">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2M12 12V4m0 8l-3-3m3 3 3-3"/>
                        </svg>
                        Export
                    </button>
                    <div id="exportMenu" class="hidden absolute right-0 mt-1 w-40 bg-white border rounded-lg shadow-lg z-20">
                        <a href="{{ route('requestlab.export', array_merge(request()->all(), ['format' => 'csv'])) }}"
                           class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Export CSV</a>
                        <a href="{{ route('requestlab.export', array_merge(request()->all(), ['format' => 'pdf'])) }}"
                           class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Export PDF</a>
                    </div>
                </div>
            @else
                {{-- Add Request --}}
                <button type="button" onclick="openCreateModal()"
                    class="flex items-center gap-2 px-4 py-2 bg-[#111B4C] text-white rounded-lg text-sm font-medium hover:bg-[#1a237e]">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add Request
                </button>
            @endif
        </div>
    </div>

    {{-- Filter Panel --}}
    <div id="filterPanel" class="hidden mb-4 p-4 bg-gray-50 rounded-lg border border-gray-100">
        <form method="GET" action="{{ route('requestlab.index') }}" class="flex flex-wrap items-end gap-3">
            <input type="hidden" name="search" value="{{ request('search') }}">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Dari Tanggal</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Sampai Tanggal</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
            </div>
            <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-lg text-sm font-medium hover:bg-gray-700">Terapkan</button>
            @if(request('date_from') || request('date_to'))
                <a href="{{ route('requestlab.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-600 hover:bg-gray-50">Reset</a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-100 text-gray-600">
                    <th class="px-4 py-3 w-10">
                        <input type="checkbox" class="w-4 h-4 rounded border-gray-300 cursor-pointer" id="checkAll">
                    </th>
                    <th class="px-4 py-3 text-left font-medium">ID Request</th>
                    <th class="px-4 py-3 text-left font-medium">Name</th>
                    <th class="px-4 py-3 text-center font-medium">Total Request</th>
                    <th class="px-4 py-3 text-center font-medium">Date</th>
                    <th class="px-4 py-3 text-center font-medium">Status</th>
                    <th class="px-4 py-3 text-center font-medium">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($requests as $request)
                    <tr class="border-b border-gray-100 hover:bg-slate-50 transition-colors">
                        <td class="px-4 py-3">
                            <input type="checkbox" class="w-4 h-4 rounded border-gray-300 cursor-pointer row-check">
                        </td>
                        <td class="px-4 py-3 text-gray-500">{{ $request->request_id }}</td>
                        <td class="px-4 py-3 text-gray-800 font-medium">{{ $request->user?->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-center text-gray-700">{{ $request->items->sum('quantity') }}</td>
                        <td class="px-4 py-3 text-center text-gray-600">
                            {{ $request->request_date?->format('d-m-Y') }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @php
                                $status = strtolower($request->request_status);
                                $badgeClass = match($status) {
                                    'pending' => 'bg-yellow-400 text-yellow-900',
                                    'approved' => 'bg-green-600 text-white',
                                    'partially approved' => 'bg-blue-600 text-white',
                                    'rejected' => 'bg-red-600 text-white',
                                    'done' => 'bg-gray-800 text-white',
                                    default => 'bg-gray-200 text-gray-600',
                                };
                            @endphp
                            <span class="inline-block px-4 py-1 text-xs font-semibold rounded-md {{ $badgeClass }}">
                                {{ ucfirst($request->request_status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-3">
                                @if($isSPV)
                                    {{-- SPV: Edit button opens modal with approve/reject --}}
                                    <button type="button" onclick="openModal({{ $request->id }})" title="Edit"
                                        class="text-gray-400 hover:text-blue-500 transition">
                                        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 0 0-2 2v11a2 2 0 0 0 2 2h11a2 2 0 0 0 2-2v-5m-1.414-9.414a2 2 0 1 1 2.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                @else
                                    {{-- Admin/PIC/Assistant: View only --}}
                                    <button type="button" onclick="openModal({{ $request->id }})" title="View"
                                        class="text-gray-400 hover:text-blue-500 transition">
                                        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/>
                                            <circle cx="12" cy="12" r="3"/>
                                        </svg>
                                    </button>
                                @endif

                                <form action="{{ route('requestlab.destroy', $request->id) }}" method="POST"
                                    onsubmit="return confirm('Yakin ingin menghapus data ini?')" class="inline m-0">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Delete" class="text-gray-400 hover:text-red-500 transition">
                                        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0 1 16.138 21H7.862a2 2 0 0 1-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-10 text-center text-gray-400">
                            Tidak ada data request.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="flex justify-end items-center gap-1 mt-6 text-sm">
        @if ($requests->onFirstPage())
            <span class="flex items-center gap-1 px-2 py-1 text-gray-300 cursor-not-allowed">← Previous</span>
        @else
            <a href="{{ $requests->previousPageUrl() }}" class="flex items-center gap-1 px-2 py-1 text-gray-400 hover:text-gray-700">← Previous</a>
        @endif

        @foreach ($requests->getUrlRange(1, $requests->lastPage()) as $page => $url)
            @if ($page == $requests->currentPage())
                <span class="w-8 h-8 flex items-center justify-center rounded bg-gray-800 text-white font-medium">{{ $page }}</span>
            @else
                <a href="{{ $url }}" class="w-8 h-8 flex items-center justify-center rounded text-gray-600 hover:bg-gray-100">{{ $page }}</a>
            @endif
        @endforeach

        @if ($requests->hasMorePages())
            <a href="{{ $requests->nextPageUrl() }}" class="flex items-center gap-1 px-2 py-1 text-gray-400 hover:text-gray-700">Next →</a>
        @else
            <span class="flex items-center gap-1 px-2 py-1 text-gray-300 cursor-not-allowed">Next →</span>
        @endif
    </div>
</div>

{{-- ===================== CREATE MODAL ===================== --}}
<div id="createModal" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.4); align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:16px; width:100%; max-width:600px; margin:0 16px; overflow:hidden; box-shadow:0 20px 60px rgba(0,0,0,0.15); max-height:90vh; display:flex; flex-direction:column;">
        <div style="display:flex; align-items:center; justify-content:space-between; padding:20px 24px; border-bottom:1px solid #e5e7eb; flex-shrink:0;">
            <h3 style="font-size:16px; font-weight:700; color:#111827; margin:0;">Create Request</h3>
            <button onclick="closeCreateModal()" style="background:none; border:none; cursor:pointer; color:#9ca3af; font-size:22px;">&times;</button>
        </div>

        <form method="POST" action="{{ route('requestlab.store') }}" style="overflow-y:auto; flex:1; padding:24px; display:flex; flex-direction:column; gap:16px;">
            @csrf
            <div>
                <label style="font-size:13px; font-weight:500; color:#374151; display:block; margin-bottom:6px;">Laboratory:</label>
                <select name="lab_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                    <option value="">Choose lab...</option>
                    @foreach($laboratories as $lab)
                        <option value="{{ $lab->id }}">{{ $lab->lab_name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label style="font-size:13px; font-weight:500; color:#374151; display:block; margin-bottom:6px;">Category:</label>
                <div class="flex gap-2">
                    @foreach(['electronic' => 'Electronic', 'non-electronic' => 'Non-Electronic', 'component-pc' => 'PC Component'] as $val => $label)
                        <button type="button" onclick="setCategory('{{ $val }}')" id="cat-btn-{{ $val }}"
                            class="px-3 py-1.5 rounded-lg text-xs font-medium border border-gray-200 bg-gray-50 text-gray-600 hover:bg-gray-100 category-btn">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
                <input type="hidden" name="category" id="createCategory" required>
            </div>

            <div id="itemsContainer" class="flex flex-col gap-3">
                {{-- Dynamic items --}}
            </div>

            <button type="button" onclick="addItem()" class="flex items-center gap-1 px-3 py-1.5 bg-gray-100 text-gray-700 rounded-lg text-xs font-medium hover:bg-gray-200 self-start">
                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 4v16m8-8H4"/></svg>
                Add Item
            </button>

            <div>
                <label style="font-size:13px; font-weight:500; color:#374151; display:block; margin-bottom:6px;">Notes (optional):</label>
                <textarea name="notes" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500" placeholder="Optional notes..."></textarea>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:8px; padding-top:8px; border-top:1px solid #f3f4f6;">
                <button type="button" onclick="closeCreateModal()" class="px-5 py-2 border border-gray-300 rounded-lg text-sm text-gray-600 hover:bg-gray-50">Cancel</button>
                <button type="submit" class="px-5 py-2 bg-[#111B4C] text-white rounded-lg text-sm font-medium hover:bg-[#1a237e]">Request</button>
            </div>
        </form>
    </div>
</div>

{{-- ===================== DETAIL/EDIT MODAL ===================== --}}
<div id="requestModal" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.4); align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:16px; width:100%; max-width:800px; margin:0 16px; overflow:hidden; box-shadow:0 20px 60px rgba(0,0,0,0.15); max-height:90vh; display:flex; flex-direction:column;">
        <div style="display:flex; align-items:center; padding:24px 32px 12px; gap:16px;">
            <h3 style="font-size:18px; font-weight:600; color:#1f2937; flex-shrink:0; margin:0;">Request Information</h3>
            <div style="flex:1;">
                <div style="height:6px; background:#e5e7eb; border-radius:99px; overflow:hidden;">
                    <div id="modalProgress" style="height:6px; background:#93c5fd; border-radius:99px; width:0%; transition:width 0.5s;"></div>
                </div>
            </div>
            <button onclick="closeModal()" style="color:#9ca3af; background:none; border:none; cursor:pointer; padding:4px; line-height:1;">
                <svg style="width:20px;height:20px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div style="overflow-y:auto; flex:1; padding:16px 32px 24px;">
            <div style="display:flex; flex-direction:column; gap:14px; margin-bottom:28px;">
                <div style="display:flex; align-items:center; gap:16px;">
                    <label style="width:130px; text-align:right; font-size:13px; color:#6b7280; flex-shrink:0;">ID Request:</label>
                    <input id="modal_request_id" type="text" readonly style="width:260px; padding:8px 14px; border:1px solid #d1d5db; border-radius:8px; font-size:13px; color:#1f2937; outline:none; background:#f9fafb;">
                </div>
                <div style="display:flex; align-items:center; gap:16px;">
                    <label style="width:130px; text-align:right; font-size:13px; color:#6b7280; flex-shrink:0;">Admin's Name:</label>
                    <input id="modal_user_name" type="text" readonly style="width:260px; padding:8px 14px; border:1px solid #d1d5db; border-radius:8px; font-size:13px; color:#1f2937; outline:none; background:#f9fafb;">
                </div>
                <div style="display:flex; align-items:center; gap:16px;">
                    <label style="width:130px; text-align:right; font-size:13px; color:#6b7280; flex-shrink:0;">Lab:</label>
                    <input id="modal_lab_name" type="text" readonly style="width:260px; padding:8px 14px; border:1px solid #d1d5db; border-radius:8px; font-size:13px; color:#1f2937; outline:none; background:#f9fafb;">
                </div>
                <div style="display:flex; align-items:center; gap:16px;">
                    <label style="width:130px; text-align:right; font-size:13px; color:#6b7280; flex-shrink:0;">Total:</label>
                    <input id="modal_total" type="text" readonly style="width:260px; padding:8px 14px; border:1px solid #d1d5db; border-radius:8px; font-size:13px; color:#1f2937; outline:none; background:#f9fafb;">
                </div>
            </div>

            {{-- Three Column Tables --}}
            <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:16px; margin-bottom:20px;">
                @foreach(['electronic' => 'Electronic', 'non_electronic' => 'Non-Electronic', 'pc_component' => 'PC Component'] as $key => $label)
                    <div>
                        <p style="font-size:13px; color:#9ca3af; margin-bottom:8px;">{{ $label }} Category</p>
                        <table style="width:100%; font-size:12px; border:1px solid #e5e7eb; border-radius:8px; overflow:hidden; border-collapse:separate; border-spacing:0;">
                            <thead>
                                <tr style="background:#f3f4f6;">
                                    <th style="padding:6px 10px; text-align:left; font-weight:500; color:#374151; border-bottom:1px solid #e5e7eb;">Asset</th>
                                    <th style="padding:6px 10px; text-align:center; font-weight:500; color:#374151; border-bottom:1px solid #e5e7eb; width:40px;">Qty</th>
                                    @if($isSPV)
                                        <th style="padding:6px 10px; text-align:center; font-weight:500; color:#374151; border-bottom:1px solid #e5e7eb; width:80px;">Action</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody id="modal_{{ $key }}">
                                <tr><td colspan="{{ $isSPV ? 3 : 2 }}" style="padding:12px; text-align:center; color:#9ca3af;">Memuat...</td></tr>
                            </tbody>
                        </table>
                    </div>
                @endforeach
            </div>

            <div id="modal_notes_wrap" style="display:none; margin-bottom:12px;">
                <p style="font-size:13px; color:#9ca3af; margin-bottom:4px;">Notes:</p>
                <p id="modal_notes" style="font-size:13px; color:#4b5563; background:#f9fafb; border-radius:8px; padding:10px 14px;"></p>
            </div>
        </div>

        @if($isSPV)
        <div style="display:flex; justify-content:flex-end; gap:10px; padding:0 32px 24px;">
            <form id="form_reject_all" method="POST">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="Rejected">
                <button type="submit" class="px-6 py-2 border border-red-300 text-red-600 rounded-lg text-sm font-medium hover:bg-red-50">Reject All</button>
            </form>
            <form id="form_approve_all" method="POST">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="Approved">
                <button type="submit" class="px-6 py-2 bg-[#111B4C] text-white rounded-lg text-sm font-medium hover:bg-[#1a237e]">Approve All</button>
            </form>
        </div>
        @endif
    </div>
</div>

@endsection

@push('scripts')
<script>
    const assetsByCategory = @json($assetsByCategory);
    const isSPV = @json($isSPV);
    let currentCategory = '';
    let itemIndex = 0;

    // ===== CREATE MODAL =====
    function openCreateModal() {
        document.getElementById('createModal').style.display = 'flex';
        if (document.getElementById('itemsContainer').children.length === 0) addItem();
    }
    function closeCreateModal() {
        document.getElementById('createModal').style.display = 'none';
    }
    document.getElementById('createModal').addEventListener('click', function(e) {
        if (e.target === this) closeCreateModal();
    });

    function setCategory(cat) {
        currentCategory = cat;
        document.getElementById('createCategory').value = cat;
        document.querySelectorAll('.category-btn').forEach(btn => {
            btn.classList.remove('bg-blue-600', 'text-white');
            btn.classList.add('bg-gray-50', 'text-gray-600');
        });
        const active = document.getElementById('cat-btn-' + cat);
        active.classList.remove('bg-gray-50', 'text-gray-600');
        active.classList.add('bg-blue-600', 'text-white');

        // Refresh all item dropdowns
        document.querySelectorAll('.asset-select').forEach(select => populateAssetSelect(select, cat));
    }

    function populateAssetSelect(select, cat) {
        const list = assetsByCategory[cat] || [];
        select.innerHTML = '<option value="">Choose asset...</option>';
        list.forEach(a => {
            const opt = document.createElement('option');
            opt.value = a.id;
            opt.textContent = a.name;
            select.appendChild(opt);
        });
    }

    function addItem() {
        const container = document.getElementById('itemsContainer');
        const div = document.createElement('div');
        div.className = 'flex gap-3 items-start';
        div.innerHTML = `
            <div class="flex-1">
                <label class="block text-xs text-gray-500 mb-1">Asset Name:</label>
                <select name="items[${itemIndex}][asset_id]" required class="asset-select w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                    <option value="">Choose asset...</option>
                </select>
            </div>
            <div class="w-28">
                <label class="block text-xs text-gray-500 mb-1">Quantity:</label>
                <input type="number" name="items[${itemIndex}][quantity]" min="1" value="1" required
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
            </div>
            <input type="hidden" name="items[${itemIndex}][category]" value="${currentCategory}">
            <button type="button" onclick="this.parentElement.remove()" class="mt-5 text-gray-400 hover:text-red-500">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        `;
        container.appendChild(div);
        const select = div.querySelector('.asset-select');
        if (currentCategory) populateAssetSelect(select, currentCategory);
        itemIndex++;
    }

    // ===== DETAIL MODAL =====
    function openModal(requestId) {
        const modal = document.getElementById('requestModal');
        document.getElementById('modalProgress').style.width = '30%';

        document.getElementById('modal_request_id').value = '';
        document.getElementById('modal_user_name').value = '';
        document.getElementById('modal_lab_name').value = '';
        document.getElementById('modal_total').value = '';
        ['electronic','non_electronic','pc_component'].forEach(k => {
            document.getElementById('modal_' + k).innerHTML = '<tr><td colspan="' + (isSPV ? 3 : 2) + '" style="padding:12px;text-align:center;color:#9ca3af;">Memuat...</td></tr>';
        });
        document.getElementById('modal_notes_wrap').style.display = 'none';

        if (isSPV) {
            document.getElementById('form_approve_all').action = `/requestlab/${requestId}/status`;
            document.getElementById('form_reject_all').action = `/requestlab/${requestId}/status`;
        }

        modal.style.display = 'flex';
        document.body.appendChild(modal);

        fetch(`/requestlab/${requestId}/detail`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(res => res.json())
            .then(data => {
                document.getElementById('modalProgress').style.width = '100%';
                document.getElementById('modal_request_id').value = data.request_id;
                document.getElementById('modal_user_name').value = data.user_name;
                document.getElementById('modal_lab_name').value = data.lab_name;
                document.getElementById('modal_total').value = data.total_request;

                if (data.notes) {
                    document.getElementById('modal_notes').textContent = data.notes;
                    document.getElementById('modal_notes_wrap').style.display = 'block';
                }

                ['electronic','non_electronic','pc_component'].forEach(key => {
                    const items = data[key] || [];
                    const tbody = document.getElementById('modal_' + key);
                    if (items.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="' + (isSPV ? 3 : 2) + '" style="padding:12px;text-align:center;color:#9ca3af;font-size:12px;">Tidak ada data</td></tr>';
                        return;
                    }
                    tbody.innerHTML = items.map(item => {
                        let actions = '';
                        if (isSPV && item.status === 'pending') {
                            actions = `
                                <td style="padding:6px 10px; text-align:center;">
                                    <div style="display:flex; gap:4px; justify-content:center;">
                                        <form method="POST" action="/requestlab/${data.id}/item/${item.id}/status" style="display:inline;">
                                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                            <input type="hidden" name="status" value="approved">
                                            <button type="submit" style="width:24px;height:24px;border-radius:50%;border:none;background:#16a34a;color:#fff;cursor:pointer;font-size:12px;display:flex;align-items:center;justify-content:center;" title="Approve">✓</button>
                                        </form>
                                        <form method="POST" action="/requestlab/${data.id}/item/${item.id}/status" style="display:inline;">
                                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                            <input type="hidden" name="status" value="rejected">
                                            <button type="submit" style="width:24px;height:24px;border-radius:50%;border:none;background:#dc2626;color:#fff;cursor:pointer;font-size:12px;display:flex;align-items:center;justify-content:center;" title="Reject">✕</button>
                                        </form>
                                    </div>
                                </td>`;
                        } else if (isSPV) {
                            const statusColor = item.status === 'approved' ? '#16a34a' : (item.status === 'rejected' ? '#dc2626' : '#9ca3af');
                            actions = `<td style="padding:6px 10px; text-align:center; font-size:11px; color:${statusColor}; font-weight:600;">${item.status}</td>`;
                        }
                        return `
                            <tr style="border-top:1px solid #f3f4f6;">
                                <td style="padding:6px 10px; color:#374151;">${item.asset_name}</td>
                                <td style="padding:6px 10px; text-align:center; color:#374151;">${item.quantity}</td>
                                ${actions}
                            </tr>`;
                    }).join('');
                });
            })
            .catch(() => {
                document.getElementById('modalProgress').style.width = '100%';
                ['electronic','non_electronic','pc_component'].forEach(k => {
                    document.getElementById('modal_' + k).innerHTML = '<tr><td colspan="' + (isSPV ? 3 : 2) + '" style="padding:12px;text-align:center;color:#f87171;font-size:12px;">Gagal memuat</td></tr>';
                });
            });
    }

    function closeModal() {
        document.getElementById('requestModal').style.display = 'none';
        document.getElementById('modalProgress').style.width = '0%';
    }

    document.getElementById('requestModal').addEventListener('click', function(e) {
        if (e.target === this) closeModal();
    });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeModal();
    });

    // Check all
    document.getElementById('checkAll')?.addEventListener('change', function() {
        document.querySelectorAll('.row-check').forEach(cb => cb.checked = this.checked);
    });
</script>
@endpush
