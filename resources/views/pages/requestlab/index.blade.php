@extends('panel.content')

@section('title', 'Request Lab')

@php
    $role = auth()->user()->role;
    $isSpv = $role === 'spv inventory';
    $canCreateRequest = $role === 'staff';
    $canReviewRequest = $isSpv;
    $canDeleteRequest = $isSpv;
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

            <x-button.filter menuId="requestLabFilterMenu" formId="requestLabFilterForm" activeCount="{{
                (request()->filled('status') ? 1 : 0) +
                (request()->filled('date_to') ? 1 : 0) +
                (request()->filled('lab_id') ? 1 : 0) +
                (request('sort') === 'asc' ? 1 : 0)
            }}">
                @if (request('search'))
                    <input type="hidden" name="search" value="{{ request('search') }}">
                @endif

                @if (in_array($role, ['admin', 'spv inventory']))
                    <div class="filter-section">
                        <div class="filter-section-title">User Role</div>
                        @foreach (['' => 'All', 'admin' => 'Admin', 'assistant' => 'Assistant'] as $filterRole => $label)
                            <label class="filter-checkbox-row">
                                <input type="radio" name="role" value="{{ $filterRole }}"
                                    {{ request('role', '') === $filterRole ? 'checked' : '' }}
                                    style="accent-color: #111B4C;">
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>

                    <div class="filter-section">
                        <div class="filter-section-title">Sort</div>
                        <label class="filter-checkbox-row" style="cursor: pointer; display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                            <input type="radio" name="sort" value="desc" {{ request('sort', 'desc') === 'desc' ? 'checked' : '' }} style="accent-color: #111B4C; cursor: pointer;">
                            <span style="font-size: 13px; color: var(--text-secondary);">Newest to Oldest</span>
                        </label>
                        <label class="filter-checkbox-row" style="cursor: pointer; display: flex; align-items: center; gap: 8px;">
                            <input type="radio" name="sort" value="asc" {{ request('sort') === 'asc' ? 'checked' : '' }} style="accent-color: #111B4C; cursor: pointer;">
                            <span style="font-size: 13px; color: var(--text-secondary);">Oldest to Newest</span>
                        </label>
                    </div>
                @endif

                @if ($isSpv)
                    <div class="filter-section">
                        <div class="filter-section-title">Request Status</div>
                        @foreach (['pending', 'partial', 'done', 'approved', 'rejected'] as $status)
                            <label class="filter-checkbox-row">
                                <input type="checkbox" name="status[]" value="{{ $status }}"
                                    {{ in_array($status, (array) request('status', [])) ? 'checked' : '' }}
                                    style="accent-color: #111B4C;">
                                <span>{{ match ($status) {
                                    'partial' => 'Partially Approved',
                                    'done' => 'Done',
                                    default => ucwords($status),
                                } }}</span>
                            </label>
                        @endforeach
                    </div>

                    <div class="filter-section">
                        <div class="filter-section-title">Request Date</div>
                        <input type="date" name="date_to" value="{{ request('date_to') }}"
                            class="w-full rounded-lg border px-3 py-2 text-sm">
                    </div>
                @endif

                <div class="filter-section">
                    <div class="filter-section-title">Laboratory</div>
                    <select name="lab_id" class="w-full rounded-lg border px-3 py-2 text-sm" style="background:var(--bg-input); border-color:var(--border-color); color:var(--text-primary);">
                        <option value="">All Labs</option>
                        @foreach ($laboratories as $lab)
                            <option value="{{ $lab->id }}" {{ request('lab_id') == $lab->id ? 'selected' : '' }}>{{ $lab->lab_name }}</option>
                        @endforeach
                    </select>
                </div>
            </x-button.filter>

            @if ($isSpv)
                <x-button.export.export
                    menuId="requestLabExportMenu"
                    pdfUrl="{{ route('requestlab.export', 'pdf') }}"
                    excelUrl="{{ route('requestlab.export', 'excel') }}"
                    csvUrl="{{ route('requestlab.export', 'csv') }}"
                />
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
                    @if ($isSpv)
                        <th class="w-12 px-4 py-3 text-left">
                            <x-table.checkbox id="checkAll" />
                        </th>
                    @endif
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
                        @if ($isSpv)
                            <td class="px-4 py-3">
                                <x-table.checkbox class="row-check" value="{{ $request->id }}" />
                            </td>
                        @endif
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
                                    'done' => 'background:#7c3aed;color:#fff;',
                                    default => 'background:#facc15;color:#713f12;',
                                } }}">
                                {{ match ($request->request_status) {
                                    'partial' => 'Partially Approved',
                                    'done' => 'Done',
                                    default => ucwords($request->request_status),
                                } }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-3">
                                <button type="button" onclick="openRequestModal({{ $request->id }})"
                                    title="Lihat Detail"
                                    style="background:none; border:none; cursor:pointer; padding:4px; color:#9ca3af;">
                                    @if ($isSpv)
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                                        </svg>
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z" />
                                            <circle cx="12" cy="12" r="3" />
                                        </svg>
                                    @endif
                                </button>

                                @if ($canDeleteRequest)
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
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-10 text-center text-gray-400">
                            Request data not found.
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

            <div style="display:flex; flex-direction:column; gap:20px; margin-bottom:20px;">
                <div>
                    <p style="font-size:13px; color:var(--text-muted); margin-bottom:8px;">Electronic Category</p>
                    <table style="width:100%; font-size:13px; border:1px solid var(--border-color); border-radius:8px; overflow:hidden; border-collapse:separate; border-spacing:0;">
                        <thead>
                            <tr style="background:var(--bg-table-header);">
                                <th style="padding:8px 14px; text-align:left;">Asset Name</th>
                                <th style="padding:8px 14px; text-align:center;">Qty</th>
                                <th style="padding:8px 14px; text-align:left;">Serial Numbers</th>
                                <th style="padding:8px 14px; text-align:center;">{{ $canReviewRequest ? 'Action' : 'Status' }}</th>
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
                                <th style="padding:8px 14px; text-align:center;">Qty</th>
                                <th style="padding:8px 14px; text-align:center;">{{ $canReviewRequest ? 'Action' : 'Status' }}</th>
                            </tr>
                        </thead>
                        <tbody id="modal_nonelectronic"></tbody>
                    </table>
                </div>

                <div>
                    <p style="font-size:13px; color:var(--text-muted); margin-bottom:8px;">PC Component Category</p>
                    <table style="width:100%; font-size:13px; border:1px solid var(--border-color); border-radius:8px; overflow:hidden; border-collapse:separate; border-spacing:0;">
                        <thead>
                            <tr style="background:var(--bg-table-header);">
                                <th style="padding:8px 14px; text-align:left;">Asset Name</th>
                                <th style="padding:8px 14px; text-align:center;">Qty</th>
                                <th style="padding:8px 14px; text-align:left;">Serial Numbers</th>
                                <th style="padding:8px 14px; text-align:center;">{{ $canReviewRequest ? 'Action' : 'Status' }}</th>
                            </tr>
                        </thead>
                        <tbody id="modal_componentpc"></tbody>
                    </table>
                </div>
            </div>
        </div>

        @if ($isSpv)
            <div style="display:flex; justify-content:flex-end; gap:10px; padding:0 32px 24px 32px;">
                <button type="button" onclick="rejectAll()"
                    style="border:1px solid #dc2626; background:#dc2626; color:#fff; border-radius:8px; padding:8px 16px; font-size:13px; font-weight:600; cursor:pointer;">
                    Reject All
                </button>
                <button type="button" onclick="approveAll()"
                    style="border:1px solid #16a34a; background:#16a34a; color:#fff; border-radius:8px; padding:8px 16px; font-size:13px; font-weight:600; cursor:pointer;">
                    Approve All
                </button>
                <button type="button" onclick="saveItemStatuses()"
                    style="border:1px solid #111B4C; background:#111B4C; color:#fff; border-radius:8px; padding:8px 16px; font-size:13px; font-weight:600; cursor:pointer;">
                    Save
                </button>
            </div>
        @endif
    </div>
</div>

<div id="serialPickerModal"
    style="display:none; position:fixed; inset:0; z-index:10000; background:rgba(0,0,0,0.5); align-items:center; justify-content:center;">
    <div style="background:var(--bg-modal); border-radius:16px; width:100%; max-width:400px; padding:24px; box-shadow:0 20px 60px rgba(0,0,0,0.2); border:1px solid var(--border-color);">
        <h4 style="font-size:16px; font-weight:700; color:var(--text-primary); margin-bottom:12px;">Select Serial Numbers</h4>
        <p style="font-size:12px; color:var(--text-secondary); margin-bottom:16px;">
            Choose up to <span id="serial_picker_max_qty" style="font-weight:700; color:#4ade80;">1</span> serial numbers.
        </p>
        <div id="serial_picker_list" style="max-height:200px; overflow-y:auto; margin-bottom:20px; display:flex; flex-direction:column; gap:8px;">
            <!-- Serial checkbox options will go here -->
        </div>
        <div style="display:flex; justify-content:flex-end; gap:10px;">
            <button type="button" onclick="closeSerialPicker()"
                style="background:none; border:1px solid var(--border-color); color:var(--text-secondary); border-radius:8px; padding:8px 16px; font-size:13px; font-weight:600; cursor:pointer;">
                Cancel
            </button>
            <button type="button" onclick="confirmSerialSelection()"
                style="background:#111B4C; border:none; color:#fff; border-radius:8px; padding:8px 16px; font-size:13px; font-weight:600; cursor:pointer;">
                Confirm
            </button>
        </div>
    </div>
</div>

<x-modal.index id="addRequestModal" title="Create Request" :action="route('requestlab.store')" submitText="Submit"
    cancelText="Cancel" boxClass="request-lab-create-modal" innerClass="request-lab-create-inner">
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
            @foreach (auth()->user()->labs as $lab)
                <option value="{{ $lab->id }}">{{ $lab->lab_name }}</option>
            @endforeach
        </select>
    </div>

    <div id="requestItemList">
        <div class="item-row"
            style="border:1px solid var(--border-color); border-radius:8px; padding:12px; margin-bottom:8px; position:relative;">
            <button type="button" onclick="removeItemRow(this)"
                style="position:absolute; top:8px; right:8px; background:none; border:none; cursor:pointer; color:var(--text-muted);">x</button>
            <div style="margin-bottom:8px;">
                <label style="font-size:12px; color:var(--text-secondary); display:block; margin-bottom:4px;">Category:</label>
                <select name="items[0][category]" onchange="updateItemAssetOptions(this)"
                    style="width:100%; padding:8px 14px; border:1px solid var(--border-color); border-radius:8px; font-size:13px; color:var(--text-primary); background:var(--bg-input);">
                    <option value="electronic">Electronic</option>
                    <option value="non-electronic">Non-Electronic</option>
                    <option value="component-pc">PC Component</option>
                    <option value="pc">PC</option>
                </select>
            </div>
            <div style="margin-bottom:8px;">
                <label style="font-size:12px; color:var(--text-secondary); display:block; margin-bottom:4px;">Asset Name:</label>
                <select name="items[0][asset_id]" data-asset-select
                    style="width:100%; padding:8px 14px; border:1px solid var(--border-color); border-radius:8px; font-size:13px; color:var(--text-primary); background:var(--bg-input);">
                    <option value="">Choose asset...</option>
                    @foreach ($assets->where('asset_category', 'electronic') as $asset)
                        <option value="{{ $asset->id }}">{{ $asset->asset_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="font-size:12px; color:var(--text-secondary); display:block; margin-bottom:4px;">Quantity:</label>
                <input type="number" name="items[0][total_request]" min="1" placeholder="Enter here..."
                    style="width:100%; padding:8px 14px; border:1px solid var(--border-color); border-radius:8px; font-size:13px; color:var(--text-primary); background:var(--bg-input);">
            </div>
        </div>
    </div>

    <button type="button" onclick="addItem()"
        class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#111B4C] px-4 py-2 text-sm font-semibold text-white">
        + Add Item
    </button>
</x-modal.index>
@endsection

@push('styles')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .request-lab-create-modal {
            max-height: calc(100vh - 48px);
            min-height: auto;
        }

        .request-lab-create-modal .panel-modal-form {
            min-height: 0;
        }

        .request-lab-create-inner {
            max-height: calc(100vh - 210px);
            overflow-y: auto;
            padding-right: 20px;
        }

        .request-lab-create-modal .panel-modal-footer {
            flex-shrink: 0;
        }
    </style>
@endpush

@push('scripts')
<script>
    let currentRequestId = null;
    let requestItemIndex = 1;
    const assets = @json($assetGroups);
    const canReviewRequest = @json($canReviewRequest);

    document.addEventListener('DOMContentLoaded', () => {
        const checkAll = document.getElementById('checkAll');
        if (checkAll) {
            checkAll.addEventListener('change', function () {
                document.querySelectorAll('.row-check').forEach((checkbox) => {
                    checkbox.checked = this.checked;
                });
            });
        }
    });

    window.openPanelModal = function(id) {
        const modal = document.getElementById(id);
        if (!modal) return;
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    };

    window.closePanelModal = function(id) {
        const modal = document.getElementById(id);
        if (!modal) return;
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    };

    window.closePanelModalOnBackdrop = function(event, id) {
        if (event.target.id === id) closePanelModal(id);
    };

    function assetOptions(category) {
        const categoryAssets = assets[category] ?? [];
        return '<option value="">Choose asset...</option>' +
            categoryAssets.map(asset => `<option value="${asset.id}">${asset.name}</option>`).join('');
    }

    function updateItemAssetOptions(categorySelect) {
        const row = categorySelect.closest('.item-row');
        const assetSelect = row.querySelector('[data-asset-select]');
        assetSelect.innerHTML = assetOptions(categorySelect.value);
    }

    function addItem() {
        const idx = requestItemIndex++;
        const div = document.createElement('div');
        div.className = 'item-row';
        div.style = 'border:1px solid var(--border-color); border-radius:8px; padding:12px; margin-bottom:8px; position:relative;';
        div.innerHTML = `
            <button type="button" onclick="removeItemRow(this)"
                style="position:absolute; top:8px; right:8px; background:none; border:none; cursor:pointer; color:var(--text-muted);">x</button>
            <div style="margin-bottom:8px;">
                <label style="font-size:12px; color:var(--text-secondary); display:block; margin-bottom:4px;">Category:</label>
                <select name="items[${idx}][category]" onchange="updateItemAssetOptions(this)"
                    style="width:100%; padding:8px 14px; border:1px solid var(--border-color); border-radius:8px; font-size:13px; color:var(--text-primary); background:var(--bg-input);">
                    <option value="electronic">Electronic</option>
                    <option value="non-electronic">Non-Electronic</option>
                    <option value="component-pc">PC Component</option>
                    <option value="pc">PC</option>
                </select>
            </div>
            <div style="margin-bottom:8px;">
                <label style="font-size:12px; color:var(--text-secondary); display:block; margin-bottom:4px;">Asset Name:</label>
                <select name="items[${idx}][asset_id]" data-asset-select
                    style="width:100%; padding:8px 14px; border:1px solid var(--border-color); border-radius:8px; font-size:13px; color:var(--text-primary); background:var(--bg-input);">
                    ${assetOptions('electronic')}
                </select>
            </div>
            <div>
                <label style="font-size:12px; color:var(--text-secondary); display:block; margin-bottom:4px;">Quantity:</label>
                <input type="number" name="items[${idx}][total_request]" placeholder="Enter here..." min="1"
                    style="width:100%; padding:8px 14px; border:1px solid var(--border-color); border-radius:8px; font-size:13px; color:var(--text-primary); background:var(--bg-input);">
            </div>
        `;
        document.getElementById('requestItemList').appendChild(div);
        if (typeof validateAddRequestForm === 'function') validateAddRequestForm();
    }

    function removeItemRow(btn) {
        const list = btn.closest('#requestItemList');
        if (list && list.querySelectorAll('.item-row').length === 1) return;
        btn.closest('.item-row').remove();
        if (typeof validateAddRequestForm === 'function') validateAddRequestForm();
    }

    let requestItemSerials = {};
    window.currentRequestItemsList = [];

    function openRequestModal(requestId) {
        currentRequestId = requestId;
        const modal = document.getElementById('requestModal');
        modal.style.display = 'flex';
        document.getElementById('modalProgress').style.width = '30%';

        const loading = '<tr><td colspan="4" style="padding:12px;text-align:center;color:var(--text-muted);font-size:12px;">Loading...</td></tr>';
        document.getElementById('modal_electronic').innerHTML = loading;
        document.getElementById('modal_nonelectronic').innerHTML = '<tr><td colspan="3" style="padding:12px;text-align:center;color:var(--text-muted);font-size:12px;">Loading...</td></tr>';
        document.getElementById('modal_componentpc').innerHTML = loading;

        requestItemSerials = {};

        fetch(`/requestlab/${requestId}/detail`)
            .then(res => res.json())
            .then(data => {
                document.getElementById('modalProgress').style.width = '100%';
                document.getElementById('modal_request_id').value = data.request_id;
                document.getElementById('modal_user_name').value = data.user_name;
                document.getElementById('modal_total').value = data.total_request;
                
                window.currentRequestItemsList = [
                    ...(data.electronic || []),
                    ...(data.non_electronic || []),
                    ...(data.component_pc || [])
                ];

                document.getElementById('modal_electronic').innerHTML = rowHtml(data.electronic, true);
                document.getElementById('modal_nonelectronic').innerHTML = rowHtml(data.non_electronic, false);
                document.getElementById('modal_componentpc').innerHTML = rowHtml(data.component_pc, true);
            })
            .catch(() => {
                const error = '<tr><td colspan="4" style="padding:12px;text-align:center;color:#f87171;font-size:12px;">Failed to load data</td></tr>';
                document.getElementById('modalProgress').style.width = '100%';
                document.getElementById('modal_electronic').innerHTML = error;
                document.getElementById('modal_nonelectronic').innerHTML = '<tr><td colspan="3" style="padding:12px;text-align:center;color:#f87171;font-size:12px;">Failed to load data</td></tr>';
                document.getElementById('modal_componentpc').innerHTML = error;
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

    function rowHtml(items, hasSerial = false) {
        if (!(items ?? []).length) {
            return `<tr><td colspan="${hasSerial ? 4 : 3}" style="padding:12px;text-align:center;color:var(--text-muted);font-size:12px;">No data</td></tr>`;
        }

        return items.map(item => {
            let serialCol = '';
            if (hasSerial) {
                if (!requestItemSerials[item.item_id]) {
                    requestItemSerials[item.item_id] = (item.serials ?? []).map(s => s.id);
                }
                const currentLabels = (item.serials ?? []).map(s => s.serial_number).join(', ') || 'None';
                const isSpvPending = canReviewRequest && item.status === 'pending';
                
                if (isSpvPending) {
                    serialCol = `
                        <td style="padding:8px 14px;">
                            <div style="display:flex; align-items:center; gap:8px;">
                                <span id="serial_labels_${item.item_id}" style="color:var(--text-secondary); font-family:monospace; font-size:12px;">${currentLabels}</span>
                                <button type="button" onclick="selectItemSerials(${item.item_id}, ${item.asset_id}, ${item.quantity})"
                                    style="background:#111B4C; border:none; cursor:pointer; color:#fff; padding:4px 8px; border-radius:4px; font-size:11px; font-weight:600;">
                                    Select S/N
                                </button>
                            </div>
                        </td>
                    `;
                } else {
                    serialCol = `
                        <td style="padding:8px 14px; color:var(--text-secondary); font-family:monospace; font-size:12px;">
                            ${currentLabels}
                        </td>
                    `;
                }
            }

            return `
                <tr style="border-top:1px solid var(--border-color);">
                    <td style="padding:8px 14px;color:var(--text-primary);">${item.asset_name}</td>
                    <td style="padding:8px 14px;text-align:center;color:var(--text-primary); font-weight:600;">${item.quantity}</td>
                    ${serialCol}
                    <td style="padding:8px 14px;text-align:center;">
                        <div style="display:flex;align-items:center;justify-content:center;gap:6px;flex-wrap:wrap;">
                            ${itemStatusBadge(item.status)}
                            ${canReviewRequest && item.status === 'pending' ? `
                                <select data-item-status-select data-item-id="${item.item_id}"
                                    style="min-width:100px;padding:4px 8px;font-size:11px;border:1px solid var(--border-color);border-radius:4px;background:var(--bg-input);color:var(--text-primary);cursor:pointer;">
                                    <option value="pending" ${item.status === 'pending' ? 'selected' : ''}>Pending</option>
                                    <option value="approved" ${item.status === 'approved' ? 'selected' : ''}>Approve</option>
                                    <option value="rejected" ${item.status === 'rejected' ? 'selected' : ''}>Reject</option>
                                </select>
                            ` : ''}
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
    }

    let currentPickerItemId = null;
    let currentPickerMaxQty = 0;
    let tempSelectedSerials = [];

    window.selectItemSerials = function(itemId, assetId, maxQty) {
        currentPickerItemId = itemId;
        currentPickerMaxQty = maxQty;
        tempSelectedSerials = [...(requestItemSerials[itemId] || [])];

        document.getElementById('serial_picker_max_qty').innerText = maxQty;
        const pickerList = document.getElementById('serial_picker_list');
        pickerList.innerHTML = '<div style="color:var(--text-muted); font-size:12px;">Loading serial numbers...</div>';
        
        document.getElementById('serialPickerModal').style.display = 'flex';

        fetch(`/api/assets/${assetId}/available-spv-serials`)
            .then(res => res.json())
            .then(data => {
                const serials = data.serials || [];
                
                const originalSerials = (window.currentRequestItemsList.find(i => i.item_id === itemId)?.serials || []).map(s => ({
                    id: s.id,
                    serial_number: s.serial_number,
                    condition: 'good'
                }));

                const allOptions = [...serials];
                originalSerials.forEach(orig => {
                    if (!allOptions.some(opt => opt.id === orig.id)) {
                        allOptions.push(orig);
                    }
                });

                if (allOptions.length === 0) {
                    pickerList.innerHTML = '<div style="color:var(--text-muted); font-size:12px;">No available serial numbers in SPV warehouse.</div>';
                    return;
                }

                pickerList.innerHTML = allOptions.map(s => {
                    const checked = tempSelectedSerials.includes(s.id) ? 'checked' : '';
                    return `
                        <label style="display:flex; align-items:center; gap:8px; font-size:13px; color:var(--text-primary); cursor:pointer;">
                            <input type="checkbox" value="${s.id}" data-serial-no="${s.serial_number}" ${checked} onchange="handleSerialCheckboxChange(this)"
                                style="accent-color:#111B4C;">
                            <span>${s.serial_number} (${s.condition})</span>
                        </label>
                    `;
                }).join('');
            })
            .catch(() => {
                pickerList.innerHTML = '<div style="color:#f87171; font-size:12px;">Failed to load serial numbers.</div>';
            });
    }

    window.handleSerialCheckboxChange = function(cb) {
        const id = parseInt(cb.value);
        if (cb.checked) {
            if (tempSelectedSerials.length >= currentPickerMaxQty) {
                cb.checked = false;
                alert(`You can select at most ${currentPickerMaxQty} serial numbers.`);
                return;
            }
            if (!tempSelectedSerials.includes(id)) {
                tempSelectedSerials.push(id);
            }
        } else {
            tempSelectedSerials = tempSelectedSerials.filter(x => x !== id);
        }
    }

    window.closeSerialPicker = function() {
        document.getElementById('serialPickerModal').style.display = 'none';
        currentPickerItemId = null;
    }

    window.confirmSerialSelection = function() {
        if (currentPickerItemId) {
            requestItemSerials[currentPickerItemId] = [...tempSelectedSerials];
            const labelsEl = document.getElementById(`serial_labels_${currentPickerItemId}`);
            if (labelsEl) {
                const checkedBoxes = document.querySelectorAll(`#serial_picker_list input[type="checkbox"]:checked`);
                const serialNos = Array.from(checkedBoxes).map(cb => cb.dataset.serialNo);
                labelsEl.innerText = serialNos.join(', ') || 'None';
            }
        }
        closeSerialPicker();
    }

    function updateItemStatus(itemId, status) {
        if (!status) return;

        const serialIds = requestItemSerials[itemId] || [];

        fetch(`/requestlab/item/${itemId}/status`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ status, serial_ids: serialIds })
        })
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    alert(data.message || 'Status item gagal diubah.');
                    return;
                }
                openRequestModal(currentRequestId);
                updateRowBadge(currentRequestId, data.request_status);
            })
            .catch(() => alert('Status item gagal diubah.'));
    }

    window.saveItemStatuses = async function() {
        if (!currentRequestId) {
            alert('Buka detail request terlebih dahulu.');
            return;
        }

        const selects = document.querySelectorAll('[data-item-status-select]');
        let latestStatus = null;

        for (const select of selects) {
            const itemId = select.dataset.itemId;
            const status = select.value;
            const serialIds = requestItemSerials[itemId] || [];

            try {
                const response = await fetch(`/requestlab/item/${itemId}/status`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ status, serial_ids: serialIds })
                });
                const data = await response.json();

                if (!data.success) {
                    alert(data.message || 'Status item gagal disimpan.');
                    return;
                }

                latestStatus = data.request_status;
            } catch (error) {
                alert('Status item gagal disimpan.');
                return;
            }
        }

        if (latestStatus) {
            updateRowBadge(currentRequestId, latestStatus);
        }

        openRequestModal(currentRequestId);
    };

    function updateRowBadge(requestId, status) {
        const badge = document.querySelector(`[data-request-status="${requestId}"]`);
        if (!badge) return;

        const styles = {
            approved: ['#16a34a', '#fff', 'Approved'],
            rejected: ['#dc2626', '#fff', 'Rejected'],
            partial: ['#2563eb', '#fff', 'Partially Approved'],
            done: ['#7c3aed', '#fff', 'Done'],
            pending: ['#facc15', '#713f12', 'Pending']
        };
        const [background, color, text] = styles[status] ?? styles.pending;
        badge.style.background = background;
        badge.style.color = color;
        badge.textContent = text;
    }

    window.approveAll = function() {
        updateRequestStatus('approved');
    };

    window.rejectAll = function() {
        updateRequestStatus('rejected');
    };

    function updateRequestStatus(status) {
        if (!currentRequestId) {
            alert('Buka detail request terlebih dahulu.');
            return;
        }

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
                if (!data.success) {
                    alert(data.message || 'Status request gagal diubah.');
                    return;
                }

                updateRowBadge(currentRequestId, data.request_status);
                closeRequestModal();
            })
            .catch(() => alert('Status request gagal diubah.'));
    }

    document.getElementById('requestModal').addEventListener('click', function(event) {
        if (event.target === this) closeRequestModal();
    });

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeRequestModal();
            document.querySelectorAll('.panel-modal-overlay:not(.hidden)').forEach(modal => {
                modal.classList.add('hidden');
            });
            document.body.style.overflow = '';
        }
    });

    function validateAddRequestForm() {
        const form = document.querySelector('#addRequestModal form');
        if (!form) return;

        const date = form.querySelector('[name="request_date"]');
        const lab = form.querySelector('[name="lab_id"]');
        
        let hasValidItem = false;
        const itemRows = form.querySelectorAll('.item-row');
        itemRows.forEach(row => {
            const assetSelect = row.querySelector('select[name*="[asset_id]"]');
            const qtyInput = row.querySelector('input[name*="[total_request]"]');
            if (assetSelect && assetSelect.value && qtyInput && qtyInput.value > 0) {
                hasValidItem = true;
            }
        });

        const submitBtn = form.querySelector('.panel-btn-submit');
        if (submitBtn) {
            if (date && date.value && lab && lab.value && hasValidItem) {
                submitBtn.disabled = false;
                submitBtn.style.opacity = '1';
                submitBtn.style.cursor = 'pointer';
            } else {
                submitBtn.disabled = true;
                submitBtn.style.opacity = '0.5';
                submitBtn.style.cursor = 'not-allowed';
            }
        }
    }

    document.addEventListener('input', function(e) {
        if (e.target.closest('#addRequestModal')) validateAddRequestForm();
    });
    document.addEventListener('change', function(e) {
        if (e.target.closest('#addRequestModal')) validateAddRequestForm();
    });
    
    // Initial validation check
    document.addEventListener('DOMContentLoaded', () => {
        validateAddRequestForm();
    });
</script>
@endpush
