@extends('panel.content')

@section('title', 'Request Lab')

@php
    $isSpv = auth()->user()->role === 'spv inventory';
    $canCreateRequest = in_array(auth()->user()->role, ['admin', 'pic', 'assistant'], true);
    $assetGroups = $assets
        ->groupBy('asset_category')
        ->map(fn ($items) => $items->map(fn ($asset) => [
            'id' => $asset->id,
            'name' => $asset->asset_name,
        ])->values());
@endphp

@section('content')
    <div style="background:var(--bg-card); border:1px solid var(--border-color);" class="rounded-2xl p-6 shadow-sm">
        <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <h2 class="text-3xl font-semibold" style="color:var(--text-primary);">
                Request List
            </h2>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <form method="GET" action="{{ route('requestlab.index') }}">
                    <div class="relative">
                        <span class="absolute inset-y-0 left-3 flex items-center text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z" />
                            </svg>
                        </span>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..."
                            style="background:var(--bg-input); border:1px solid var(--border-color); color:var(--text-primary);"
                            class="w-56 rounded-lg py-2 pl-9 pr-4 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400">
                    </div>
                </form>

                <x-button.filter :action="route('requestlab.index')">
                    @if (request('search'))
                        <input type="hidden" name="search" value="{{ request('search') }}">
                    @endif

                    <div class="filter-section">
                        <div class="filter-section-title">Request Status</div>
                        @foreach (['pending', 'approved', 'partial', 'rejected'] as $status)
                            <label class="filter-checkbox-row">
                                <input type="checkbox" name="status[]" value="{{ $status }}"
                                    {{ in_array($status, (array) request('status', [])) ? 'checked' : '' }}
                                    style="accent-color: #111B4C;">
                                <span>{{ $status === 'partial' ? 'Partially Approved' : ucwords($status) }}</span>
                            </label>
                        @endforeach
                    </div>

                    <div class="filter-section">
                        <div class="filter-section-title">Request Date</div>
                        <input type="date" name="date_to" value="{{ request('date_to') }}"
                            class="w-full rounded-lg border px-3 py-2 text-sm">
                    </div>
                </x-button.filter>

                @if ($isSpv)
                    <x-button.export href="{{ route('requestlab.export.pdf') }}">
                        Export
                    </x-button.export>
                @endif

                @if ($canCreateRequest)
                    <x-button.add type="button" onclick="openPanelModal('addRequestModal')">
                        Add Request
                    </x-button.add>
                @endif
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr style="background:var(--bg-table-header); color:var(--text-secondary);">
                        <th class="px-4 py-3 text-left font-medium">ID Request</th>
                        <th class="px-4 py-3 text-left font-medium">Name</th>
                        <th class="px-4 py-3 text-left font-medium">Laboratory</th>
                        <th class="px-4 py-3 text-center font-medium">Total Request</th>
                        <th class="px-4 py-3 text-center font-medium">Date</th>
                        <th class="px-4 py-3 text-center font-medium">Status</th>
                        <th class="px-4 py-3 text-center font-medium">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($requests as $request)
                        <tr style="border-bottom:1px solid var(--border-color);" class="transition-colors">
                            <td class="px-4 py-3" style="color:var(--text-secondary);">
                                REQ-{{ str_pad($request->id, 3, '0', STR_PAD_LEFT) }}
                            </td>
                            <td class="px-4 py-3 font-medium" style="color:var(--text-primary);">
                                {{ $request->user->name ?? '-' }}
                            </td>
                            <td class="px-4 py-3" style="color:var(--text-secondary);">
                                {{ $request->lab->lab_name ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-center" style="color:var(--text-primary);">
                                {{ $request->request_items->sum('total_request') }}
                            </td>
                            <td class="px-4 py-3 text-center" style="color:var(--text-secondary);">
                                {{ $request->request_date ? \Carbon\Carbon::parse($request->request_date)->format('d-m-y') : '-' }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-block rounded-md px-4 py-1 text-xs font-semibold"
                                    data-request-status="{{ $request->id }}"
                                    style="{{ match ($request->request_status) {
                                        'approved' => 'background:#16a34a;color:#fff;',
                                        'rejected' => 'background:#dc2626;color:#fff;',
                                        'partial' => 'background:#2563eb;color:#fff;',
                                        default => 'background:#facc15;color:#713f12;',
                                    } }}">
                                    {{ $request->request_status === 'partial' ? 'Partially Approved' : ucwords($request->request_status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-3">
                                    <button type="button" onclick="openRequestModal({{ $request->id }})"
                                        title="Lihat Detail"
                                        style="background:none; border:none; cursor:pointer; padding:4px; color:#9ca3af;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M11 5H6a2 2 0 0 0-2 2v11a2 2 0 0 0 2 2h11a2 2 0 0 0 2-2v-5m-1.414-9.414a2 2 0 1 1 2.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>

                                    <form action="{{ route('requestlab.destroy', $request->id) }}" method="POST"
                                        onsubmit="return confirm('Yakin ingin menghapus data ini?')" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Hapus"
                                            style="background:none; border:none; cursor:pointer; padding:4px; color:#9ca3af;">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M19 7l-.867 12.142A2 2 0 0 1 16.138 21H7.862a2 2 0 0 1-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v3M4 7h16" />
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

        <div class="mt-6">
            {{ $requests->links() }}
        </div>
    </div>

    <div id="requestModal"
        style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.5); align-items:center; justify-content:center;">
        <div
            style="background:var(--bg-modal); border-radius:16px; width:100%; max-width:760px; margin:0 16px; overflow:hidden; box-shadow:0 20px 60px rgba(0,0,0,0.15); border:1px solid var(--border-color);">
            <div
                style="display:flex; align-items:center; padding:24px 32px 12px 32px; gap:16px; border-bottom:1px solid var(--border-color);">
                <h3 style="font-size:18px; font-weight:600; color:var(--text-primary); flex-shrink:0; margin:0;">
                    Request Information
                </h3>
                <div style="flex:1;">
                    <div style="height:6px; background:var(--border-color); border-radius:99px; overflow:hidden;">
                        <div id="modalProgress"
                            style="height:6px; background:#93c5fd; border-radius:99px; width:0%; transition:width 0.5s;">
                        </div>
                    </div>
                </div>
                <button onclick="closeRequestModal()"
                    style="color:var(--text-muted); background:none; border:none; cursor:pointer; padding:4px; line-height:1;">
                    <svg xmlns="http://www.w3.org/2000/svg" style="width:20px;height:20px;" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div style="padding:16px 32px 24px 32px;">
                <div style="display:flex; flex-direction:column; gap:14px; margin-bottom:28px;">
                    <div style="display:flex; align-items:center; gap:16px;">
                        <label style="width:130px; text-align:right; font-size:13px; color:var(--text-secondary);">ID Request:</label>
                        <input id="modal_request_id" type="text" readonly
                            style="width:260px; padding:8px 14px; border:1px solid var(--border-color); border-radius:8px; font-size:13px; color:var(--text-primary); background:var(--bg-input);">
                    </div>
                    <div style="display:flex; align-items:center; gap:16px;">
                        <label style="width:130px; text-align:right; font-size:13px; color:var(--text-secondary);">Name:</label>
                        <input id="modal_user_name" type="text" readonly
                            style="width:260px; padding:8px 14px; border:1px solid var(--border-color); border-radius:8px; font-size:13px; color:var(--text-primary); background:var(--bg-input);">
                    </div>
                    <div style="display:flex; align-items:center; gap:16px;">
                        <label style="width:130px; text-align:right; font-size:13px; color:var(--text-secondary);">Total:</label>
                        <input id="modal_total" type="text" readonly
                            style="width:260px; padding:8px 14px; border:1px solid var(--border-color); border-radius:8px; font-size:13px; color:var(--text-primary); background:var(--bg-input);">
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:20px;">
                    <div>
                        <p style="font-size:13px; color:var(--text-muted); margin-bottom:8px;">Electronic Category</p>
                        <table style="width:100%; font-size:13px; border:1px solid var(--border-color); border-radius:8px; overflow:hidden; border-collapse:separate; border-spacing:0;">
                            <thead>
                                <tr style="background:var(--bg-table-header);">
                                    <th style="padding:8px 14px; text-align:left;">Asset Name</th>
                                    <th style="padding:8px 14px; text-align:left;">Qty</th>
                                    <th style="padding:8px 14px; text-align:center;">Action</th>
                                </tr>
                            </thead>
                            <tbody id="modal_electronic"></tbody>
                        </table>
                    </div>

                    <div>
                        <p style="font-size:13px; color:var(--text-muted); margin-bottom:8px;">Non-Electronic Category</p>
                        <table style="width:100%; font-size:13px; border:1px solid var(--border-color); border-radius:8px; overflow:hidden; border-collapse:separate; border-spacing:0;">
                            <thead>
                                <tr style="background:var(--bg-table-header);">
                                    <th style="padding:8px 14px; text-align:left;">Asset Name</th>
                                    <th style="padding:8px 14px; text-align:left;">Qty</th>
                                    <th style="padding:8px 14px; text-align:center;">Action</th>
                                </tr>
                            </thead>
                            <tbody id="modal_nonelectronic"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px; padding:0 32px 24px 32px;">
                <button onclick="rejectAll()"
                    style="padding:9px 24px; border:1px solid var(--border-color); border-radius:8px; font-size:13px; color:var(--text-secondary); background:var(--bg-modal); cursor:pointer;">
                    Reject All
                </button>
                <button onclick="approveAll()"
                    style="padding:9px 24px; border:none; border-radius:8px; font-size:13px; font-weight:500; color:#fff; background:#111827; cursor:pointer;">
                    Approve All
                </button>
            </div>
        </div>
    </div>

    <x-modal.index id="addRequestModal" title="Create Request" :action="route('requestlab.store')" submitText="Submit"
        cancelText="Cancel">
        <div style="margin-bottom:16px;">
            <label style="font-size:13px; color:var(--text-secondary); display:block; margin-bottom:6px;">Submission Date</label>
            <input type="date" name="request_date" required
                style="width:100%; padding:8px 14px; border:1px solid var(--border-color); border-radius:8px; font-size:13px; color:var(--text-primary); background:var(--bg-input);">
        </div>

        <div style="margin-bottom:16px;">
            <label style="font-size:13px; color:var(--text-secondary); display:block; margin-bottom:6px;">Laboratory</label>
            <select name="lab_id" required
                style="width:100%; padding:8px 14px; border:1px solid var(--border-color); border-radius:8px; font-size:13px; color:var(--text-primary); background:var(--bg-input);">
                <option value="">Pilih Lab</option>
                @foreach ($laboratories as $lab)
                    <option value="{{ $lab->id }}">{{ $lab->lab_name }}</option>
                @endforeach
            </select>
        </div>

        <div style="display:flex; gap:8px; margin-bottom:16px;">
            @foreach (['electronic' => 'Electronic', 'non-electronic' => 'Non-Electronic', 'component-pc' => 'PC Component'] as $category => $label)
                <button type="button" onclick="switchTab('{{ $category }}')" id="tab-{{ $category }}"
                    style="padding:6px 16px; border-radius:8px; font-size:13px; font-weight:500; cursor:pointer; border:{{ $loop->first ? 'none' : '1px solid var(--border-color)' }}; background:{{ $loop->first ? '#111B4C' : 'var(--bg-input)' }}; color:{{ $loop->first ? '#fff' : 'var(--text-secondary)' }};">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        @foreach (['electronic', 'non-electronic', 'component-pc'] as $category)
            <div id="items-{{ $category }}" style="{{ $loop->first ? '' : 'display:none;' }}">
                <div id="itemList-{{ $category }}">
                    <div class="item-row"
                        style="border:1px solid var(--border-color); border-radius:8px; padding:12px; margin-bottom:8px; position:relative;">
                        <button type="button" onclick="removeItemRow(this)"
                            style="position:absolute; top:8px; right:8px; background:none; border:none; cursor:pointer; color:var(--text-muted);">x</button>
                        <div style="margin-bottom:8px;">
                            <label style="font-size:12px; color:var(--text-secondary); display:block; margin-bottom:4px;">Asset Name:</label>
                            <select name="items[{{ $category }}][0][asset_id]"
                                style="width:100%; padding:8px 14px; border:1px solid var(--border-color); border-radius:8px; font-size:13px; color:var(--text-primary); background:var(--bg-input);">
                                <option value="">Choose asset...</option>
                                @foreach ($assets->where('asset_category', $category) as $asset)
                                    <option value="{{ $asset->id }}">{{ $asset->asset_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label style="font-size:12px; color:var(--text-secondary); display:block; margin-bottom:4px;">Quantity:</label>
                            <input type="number" name="items[{{ $category }}][0][total_request]" min="1"
                                placeholder="Enter here..."
                                style="width:100%; padding:8px 14px; border:1px solid var(--border-color); border-radius:8px; font-size:13px; color:var(--text-primary); background:var(--bg-input);">
                        </div>
                    </div>
                </div>
                <button type="button" onclick="addItem('{{ $category }}')"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#111B4C] px-4 py-2 text-sm font-semibold text-white">
                    + Add Item
                </button>
            </div>
        @endforeach
    </x-modal.index>
@endsection

@push('styles')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
@endpush

@push('scripts')
    <script>
        let currentRequestId = null;
        const itemIndexes = {
            'electronic': 1,
            'non-electronic': 1,
            'component-pc': 1
        };
        const assets = @json($assetGroups);

        function openPanelModal(id) {
            const modal = document.getElementById(id);
            if (!modal) return;
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closePanelModal(id) {
            const modal = document.getElementById(id);
            if (!modal) return;
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }

        function closePanelModalOnBackdrop(event, id) {
            if (event.target.id === id) closePanelModal(id);
        }

        function switchTab(category) {
            ['electronic', 'non-electronic', 'component-pc'].forEach(cat => {
                const panel = document.getElementById(`items-${cat}`);
                const tab = document.getElementById(`tab-${cat}`);
                if (!panel || !tab) return;
                panel.style.display = cat === category ? 'block' : 'none';
                tab.style.background = cat === category ? '#111B4C' : 'var(--bg-input)';
                tab.style.color = cat === category ? '#fff' : 'var(--text-secondary)';
                tab.style.border = cat === category ? 'none' : '1px solid var(--border-color)';
            });
        }

        function addItem(category) {
            const idx = itemIndexes[category]++;
            const catAssets = assets[category] ?? [];
            const div = document.createElement('div');
            div.className = 'item-row';
            div.style = 'border:1px solid var(--border-color); border-radius:8px; padding:12px; margin-bottom:8px; position:relative;';
            div.innerHTML = `
                <button type="button" onclick="removeItemRow(this)"
                    style="position:absolute; top:8px; right:8px; background:none; border:none; cursor:pointer; color:var(--text-muted);">x</button>
                <div style="margin-bottom:8px;">
                    <label style="font-size:12px; color:var(--text-secondary); display:block; margin-bottom:4px;">Asset Name:</label>
                    <select name="items[${category}][${idx}][asset_id]"
                        style="width:100%; padding:8px 14px; border:1px solid var(--border-color); border-radius:8px; font-size:13px; color:var(--text-primary); background:var(--bg-input);">
                        <option value="">Choose asset...</option>
                        ${catAssets.map(a => `<option value="${a.id}">${a.name}</option>`).join('')}
                    </select>
                </div>
                <div>
                    <label style="font-size:12px; color:var(--text-secondary); display:block; margin-bottom:4px;">Quantity:</label>
                    <input type="number" name="items[${category}][${idx}][total_request]" placeholder="Enter here..." min="1"
                        style="width:100%; padding:8px 14px; border:1px solid var(--border-color); border-radius:8px; font-size:13px; color:var(--text-primary); background:var(--bg-input);">
                </div>
            `;
            document.getElementById(`itemList-${category}`).appendChild(div);
        }

        function removeItemRow(btn) {
            const list = btn.closest('[id^="itemList-"]');
            if (list && list.querySelectorAll('.item-row').length === 1) return;
            btn.closest('.item-row').remove();
        }

        function openRequestModal(requestId) {
            currentRequestId = requestId;
            const modal = document.getElementById('requestModal');
            modal.style.display = 'flex';
            document.getElementById('modalProgress').style.width = '30%';

            const loading = '<tr><td colspan="3" style="padding:12px;text-align:center;color:var(--text-muted);font-size:12px;">Memuat...</td></tr>';
            document.getElementById('modal_electronic').innerHTML = loading;
            document.getElementById('modal_nonelectronic').innerHTML = loading;

            fetch(`/requestlab/${requestId}/detail`)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('modalProgress').style.width = '100%';
                    document.getElementById('modal_request_id').value = data.request_id;
                    document.getElementById('modal_user_name').value = data.user_name;
                    document.getElementById('modal_total').value = data.total_request;
                    document.getElementById('modal_electronic').innerHTML = rowHtml(data.electronic);
                    document.getElementById('modal_nonelectronic').innerHTML = rowHtml(data.non_electronic);
                })
                .catch(() => {
                    const error = '<tr><td colspan="3" style="padding:12px;text-align:center;color:#f87171;font-size:12px;">Gagal memuat data</td></tr>';
                    document.getElementById('modalProgress').style.width = '100%';
                    document.getElementById('modal_electronic').innerHTML = error;
                    document.getElementById('modal_nonelectronic').innerHTML = error;
                });
        }

        function closeRequestModal() {
            currentRequestId = null;
            document.getElementById('requestModal').style.display = 'none';
            document.getElementById('modalProgress').style.width = '0%';
        }

        function itemStatusBadge(status) {
            if (status === 'approved') {
                return '<span style="background:#16a34a;color:#fff;padding:2px 8px;border-radius:4px;font-size:11px;">Approved</span>';
            }
            if (status === 'rejected') {
                return '<span style="background:#dc2626;color:#fff;padding:2px 8px;border-radius:4px;font-size:11px;">Rejected</span>';
            }
            return '<span style="background:#facc15;color:#713f12;padding:2px 8px;border-radius:4px;font-size:11px;">Pending</span>';
        }

        function rowHtml(items) {
            if (!(items ?? []).length) {
                return '<tr><td colspan="3" style="padding:12px;text-align:center;color:var(--text-muted);font-size:12px;">Tidak ada data</td></tr>';
            }

            return items.map(item => `
                <tr style="border-top:1px solid var(--border-color);">
                    <td style="padding:8px 14px;color:var(--text-primary);">${item.asset_name}</td>
                    <td style="padding:8px 14px;color:var(--text-primary);">${item.quantity}</td>
                    <td style="padding:8px 14px;text-align:center;">
                        <div style="display:flex;align-items:center;justify-content:center;gap:6px;flex-wrap:wrap;">
                            ${itemStatusBadge(item.status)}
                            <select onchange="updateItemStatus(${item.item_id}, this.value)"
                                style="min-width:100px;padding:4px 8px;font-size:11px;border:1px solid var(--border-color);border-radius:4px;background:var(--bg-input);color:var(--text-primary);cursor:pointer;">
                                <option value="" ${item.status === 'pending' ? 'selected' : ''} disabled>Pilih</option>
                                <option value="approved" ${item.status === 'approved' ? 'selected' : ''}>Approve</option>
                                <option value="rejected" ${item.status === 'rejected' ? 'selected' : ''}>Reject</option>
                            </select>
                        </div>
                    </td>
                </tr>
            `).join('');
        }

        function updateItemStatus(itemId, status) {
            if (!status) return;
            fetch(`/requestlab/items/${itemId}/status`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ status })
            })
                .then(res => res.json())
                .then(data => {
                    if (!data.success) return;
                    openRequestModal(currentRequestId);
                    updateRowBadge(currentRequestId, data.request_status);
                });
        }

        function updateRowBadge(requestId, status) {
            const badge = document.querySelector(`[data-request-status="${requestId}"]`);
            if (!badge) return;

            const styles = {
                approved: ['#16a34a', '#fff', 'Approved'],
                rejected: ['#dc2626', '#fff', 'Rejected'],
                partial: ['#2563eb', '#fff', 'Partially Approved'],
                pending: ['#facc15', '#713f12', 'Pending']
            };
            const [background, color, text] = styles[status] ?? styles.pending;
            badge.style.background = background;
            badge.style.color = color;
            badge.textContent = text;
        }

        function approveAll() {
            updateRequestStatus('approved');
        }

        function rejectAll() {
            updateRequestStatus('rejected');
        }

        function updateRequestStatus(status) {
            fetch(`/requestlab/${currentRequestId}/status`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ status })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) updateRowBadge(currentRequestId, data.request_status);
                    closeRequestModal();
                });
        }

        document.getElementById('requestModal').addEventListener('click', function (event) {
            if (event.target === this) closeRequestModal();
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeRequestModal();
                document.querySelectorAll('.panel-modal-overlay:not(.hidden)').forEach(modal => modal.classList.add('hidden'));
                document.body.style.overflow = '';
            }
        });
    </script>
@endpush
