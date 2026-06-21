@extends('panel.content')

@section('title', auth()->user()->role === 'spv inventory' ? 'SPV Dashboard' : 'Staff Dashboard')

@section('content')

    <div class="db-wrap">

        <div class="db-top-row">

            <div class="db-stats-col">
                <div class="db-cards-row">

                    <div class="db-stat-card">
                        <div class="stat-info">
                            <span class="stat-label">Users</span>
                            <span class="stat-value">{{ number_format($totalUsers) }}</span>
                        </div>
                        <div class="stat-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                                <circle cx="9" cy="7" r="4" />
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                                <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                            </svg>
                        </div>
                    </div>

                    <div class="db-stat-card">
                        <div class="stat-info">
                            <span class="stat-label">Laboratory</span>
                            <span class="stat-value">{{ number_format($totalLaboratory) }}</span>
                        </div>
                        <div class="stat-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                                <polyline points="9 22 9 12 15 12 15 22" />
                            </svg>
                        </div>
                    </div>

                    <div class="db-stat-card">
                        <div class="stat-info">
                            <span class="stat-label">Lab Request</span>
                            <span class="stat-value">{{ number_format($totalRequestLab) }}</span>
                        </div>
                        <div class="stat-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2" />
                                <rect x="8" y="2" width="8" height="4" rx="1" ry="1" />
                                <circle cx="12" cy="14" r="2" />
                                <path d="M12 11v1M12 16v1" />
                            </svg>
                        </div>
                    </div>

                    <div class="db-stat-card">
                        <div class="stat-info">
                            <span class="stat-label">Return Request</span>
                            <span class="stat-value">{{ number_format($totalReturnRequests) }}</span>
                        </div>
                        <div class="stat-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 10v-4h4" />
                                <path d="M12 6a7 7 0 0 1 7 7" />
                                <path d="M12 6a7 7 0 0 0-7 7" />
                            </svg>
                        </div>
                    </div>

                    <div class="db-stat-card">
                        <div class="stat-info">
                            <span class="stat-label">Transfer Request</span>
                            <span class="stat-value">{{ number_format($totalTransferRequests) }}</span>
                        </div>
                        <div class="stat-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path d="M8 7h12l-4-4m4 4l-4 4" />
                                <path d="M16 17H4l4 4M4 17l4-4" />
                            </svg>
                        </div>
                    </div>

                </div>

                <div class="db-card db-chart-card">
                    <h2 class="db-card-title">Laboratory Conditions</h2>
                    <div class="chart-container">
                        <canvas id="labConditionsChart"></canvas>
                    </div>
                    <div class="chart-legend">
                        <span class="legend-dot" style="background:#111B4C"></span> Active
                        <span class="legend-dot" style="background:#98083D; margin-left:16px"></span> Inactive
                    </div>
                </div>
            </div>

            <div class="db-card db-lowstock-card">
                <h2 class="db-card-title">Low Stock Items</h2>

                <div class="lowstock-list">
                    @forelse ($lowStockItems as $item)
                        <div class="lowstock-item">
                            <div class="lowstock-left">
                                <svg class="lowstock-alert" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10" />
                                    <line x1="12" y1="8" x2="12" y2="12" />
                                    <line x1="12" y1="16" x2="12.01" y2="16" />
                                </svg>

                                <span class="lowstock-name">
                                    {{ $item->asset_name }}
                                </span>
                            </div>

                            <span class="lowstock-count">
                                In stock: {{ $item->total_good }}
                            </span>
                        </div>
                    @empty
                        <p class="empty-state">Semua stok aman</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="db-card db-table-card" style="margin-bottom: 24px;">
            <h2 class="db-card-title" style="margin: 16px 20px 16px 20px;">Recent Lab Requests</h2>
            <div class="table-wrap">
                <table class="db-table">
                    <thead>
                        <tr>
                            <th class="th-check">
                                <input type="checkbox" class="db-checkbox" id="checkAll" onclick="toggleAll(this)">
                            </th>
                            <th>ID Request</th>
                            <th>Name</th>
                            <th>Total Request</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentRequests as $req)
                            <tr>
                                <td class="th-check">
                                    <input type="checkbox" class="db-checkbox row-check">
                                </td>
                                <td class="td-mono">REQ-{{ str_pad($req->id, 3, '0', STR_PAD_LEFT) }}</td>
                                <td>{{ $req->user->name ?? '-' }}</td>
                                <td>{{ $req->total_requested_items ?? 0 }}</td>
                                <td>{{ \Carbon\Carbon::parse($req->created_at)->format('d-m-y') }}</td>
                                <td>
                                    @php
                                        $status = strtolower($req->request_status ?? 'pending');
                                        $badgeClass = match ($status) {
                                            'approved', 'done' => 'badge-approved',
                                            'rejected' => 'badge-rejected',
                                            'partial' => 'badge-partial',
                                            default => 'badge-pending',
                                        };
                                    @endphp
                                    <span class="status-badge {{ $badgeClass }}">
                                        {{ ucfirst($req->request_status ?? 'Pending') }}
                                    </span>
                                </td>
                                <td>
                                    <div class="action-btns">
                                        <button type="button" onclick="openLabRequestModal({{ $req->id }})"
                                            class="action-btn action-edit" title="Edit / Review">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                                stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                                            </svg>
                                        </button>
                                        <form method="POST" action="{{ route('requestlab.destroy', $req->id) }}"
                                            onsubmit="return confirm('Hapus request ini?')" style="display:inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="action-btn action-delete" title="Hapus">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                                    stroke-linecap="round" stroke-linejoin="round">
                                                    <polyline points="3 6 5 6 21 6" />
                                                    <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" />
                                                    <path d="M10 11v6M14 11v6" />
                                                    <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="empty-state" style="text-align:center; padding:32px">
                                    Belum ada lab request
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="db-card db-table-card" style="margin-bottom: 24px;">
            <h2 class="db-card-title" style="margin: 16px 20px 16px 20px;">Recent Return Requests</h2>
            <div class="table-wrap">
                <table class="db-table">
                    <thead>
                        <tr>
                            <th>ID Request</th>
                            <th>Laboratory</th>
                            <th>Requested By</th>
                            <th>Item Count</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentReturnRequests as $req)
                            <tr>
                                <td class="td-mono">{{ $req->request_code }}</td>
                                <td>{{ $req->laboratory->lab_name ?? '-' }}</td>
                                <td>{{ $req->requestedBy->name ?? '-' }}</td>
                                <td>
                                    @if($req->pc_id)
                                        PC
                                    @else
                                        {{ $req->items->count() }}
                                    @endif
                                </td>
                                <td>{{ $req->created_at->format('d-m-y') }}</td>
                                <td>
                                    @php
                                        [$label, $color] = $req->getStatusBadge();
                                    @endphp
                                    <span class="status-badge {{ $color }}">
                                        {{ $label }}
                                    </span>
                                </td>
                                <td>
                                    <button type="button" onclick="openReturnDetailModal({{ $req->id }})" class="action-btn action-edit" title="Edit / Review">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="empty-state" style="text-align:center; padding:32px">
                                    Belum ada return request
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="db-card db-table-card">
            <h2 class="db-card-title" style="margin: 16px 20px 16px 20px;">Recent Transfer Requests</h2>
            <div class="table-wrap">
                <table class="db-table">
                    <thead>
                        <tr>
                            <th>ID Request</th>
                            <th>From Lab</th>
                            <th>To Lab</th>
                            <th>Requested By</th>
                            <th>Item Count</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentTransferRequests as $req)
                            <tr>
                                <td class="td-mono">{{ $req->request_code }}</td>
                                <td>{{ $req->fromLab->lab_name ?? '-' }}</td>
                                <td>{{ $req->toLab->lab_name ?? '-' }}</td>
                                <td>{{ $req->requestedBy->name ?? '-' }}</td>
                                <td>{{ $req->items->count() }}</td>
                                <td>{{ $req->created_at->format('d-m-y') }}</td>
                                <td>
                                    @php
                                        [$label, $color] = $req->getStatusBadge();
                                    @endphp
                                    <span class="status-badge {{ $color }}">
                                        {{ $label }}
                                    </span>
                                </td>
                                <td>
                                    <button type="button" onclick="openTransferDetailModal({{ $req->id }})" class="action-btn action-edit" title="Edit / Review">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="empty-state" style="text-align:center; padding:32px">
                                    Belum ada transfer request
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <div id="returnDetailModal"
        style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.5); align-items:center; justify-content:center;">
        <div
            style="background:var(--bg-modal); border-radius:16px; width:100%; max-width:760px; margin:0 16px; overflow:hidden; box-shadow:0 20px 60px rgba(0,0,0,0.15); border:1px solid var(--border-color);">
            <div
                style="display:flex; align-items:center; padding:24px 32px 12px 32px; gap:16px; border-bottom:1px solid var(--border-color);">
                <h3 style="font-size:18px; font-weight:700; color:var(--text-primary); flex-shrink:0; margin:0;">
                    Return Request Information
                </h3>
                <div style="flex:1;">
                    <div style="height:6px; background:var(--border-color); border-radius:99px; overflow:hidden;">
                        <div id="returnModalProgress"
                            style="height:6px; background:#93c5fd; border-radius:99px; width:0%; transition:width 0.5s;">
                        </div>
                    </div>
                </div>
                <button onclick="closeReturnDetailModal()"
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
                        <label style="width:130px; text-align:right; font-size:13px; color:var(--text-secondary);">Kode Request:</label>
                        <input id="return_modal_request_code" type="text" readonly
                            style="width:260px; padding:8px 14px; border:1px solid var(--border-color); border-radius:8px; font-size:13px; color:var(--text-primary); background:var(--bg-input);">
                    </div>
                    <div style="display:flex; align-items:center; gap:16px;">
                        <label style="width:130px; text-align:right; font-size:13px; color:var(--text-secondary);">Lab:</label>
                        <input id="return_modal_lab" type="text" readonly
                            style="width:260px; padding:8px 14px; border:1px solid var(--border-color); border-radius:8px; font-size:13px; color:var(--text-primary); background:var(--bg-input);">
                    </div>
                    <div style="display:flex; align-items:center; gap:16px;">
                        <label style="width:130px; text-align:right; font-size:13px; color:var(--text-secondary);">Diajukan oleh:</label>
                        <input id="return_modal_requested_by" type="text" readonly
                            style="width:260px; padding:8px 14px; border:1px solid var(--border-color); border-radius:8px; font-size:13px; color:var(--text-primary); background:var(--bg-input);">
                    </div>
                </div>

                <div style="margin-bottom:20px;">
                    <p style="font-size:13px; color:var(--text-muted); margin-bottom:8px;">Barang yang Diretur</p>
                    <table id="return_modal_items" style="width:100%; font-size:13px; border:1px solid var(--border-color); border-radius:8px; overflow:hidden; border-collapse:separate; border-spacing:0;">
                        <thead>
                            <tr style="background:var(--bg-table-header);">
                                <th style="padding:8px 14px; text-align:left;">Asset Name</th>
                                <th style="padding:8px 14px; text-align:center;">Qty</th>
                                <th style="padding:8px 14px; text-align:center;">Kondisi</th>
                                <th style="padding:8px 14px; text-align:center;">Qty Disetujui</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px; padding:0 32px 24px 32px;">
                <button type="button" onclick="rejectAllReturn()"
                    style="border:1px solid #dc2626; background:#dc2626; color:#fff; border-radius:8px; padding:8px 16px; font-size:13px; font-weight:600; cursor:pointer;">
                    Tolak Semua
                </button>
                <button type="button" onclick="approveAllReturn()"
                    style="border:1px solid #16a34a; background:#16a34a; color:#fff; border-radius:8px; padding:8px 16px; font-size:13px; font-weight:600; cursor:pointer;">
                    Setujui Semua
                </button>
                <button type="button" onclick="saveReturnStatuses()"
                    style="border:1px solid #111B4C; background:#111B4C; color:#fff; border-radius:8px; padding:8px 16px; font-size:13px; font-weight:600; cursor:pointer;">
                    Simpan
                </button>
            </div>
        </div>
    </div>

    <div id="transferDetailModal"
        style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.5); align-items:center; justify-content:center;">
        <div
            style="background:var(--bg-modal); border-radius:16px; width:100%; max-width:760px; margin:0 16px; overflow:hidden; box-shadow:0 20px 60px rgba(0,0,0,0.15); border:1px solid var(--border-color);">
            <div
                style="display:flex; align-items:center; padding:24px 32px 12px 32px; gap:16px; border-bottom:1px solid var(--border-color);">
                <h3 style="font-size:18px; font-weight:700; color:var(--text-primary); flex-shrink:0; margin:0;">
                    Transfer Request Information
                </h3>
                <div style="flex:1;">
                    <div style="height:6px; background:var(--border-color); border-radius:99px; overflow:hidden;">
                        <div id="transferModalProgress"
                            style="height:6px; background:#93c5fd; border-radius:99px; width:0%; transition:width 0.5s;">
                        </div>
                    </div>
                </div>
                <button onclick="closeTransferDetailModal()"
                    style="color:var(--text-muted); background:none; border:none; cursor:pointer; padding:4px; line-height:1;">
                    <svg xmlns="http://www.w3.org/2000/svg" style="width:20px;height:20px;" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div style="padding:16px 32px 24px 32px;">
                <div style="display:flex; flex-direction:column; gap:14px; margin-bottom:28px;">
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label style="font-size:13px; color:var(--text-secondary); display:block; margin-bottom:6px;">Kode Request</label>
                            <input id="transfer_modal_request_code" type="text" readonly
                                style="width:100%; padding:8px 14px; border:1px solid var(--border-color); border-radius:8px; font-size:13px; color:var(--text-primary); background:var(--bg-input);">
                        </div>
                        <div>
                            <label style="font-size:13px; color:var(--text-secondary); display:block; margin-bottom:6px;">From Lab</label>
                            <input id="transfer_modal_from_lab" type="text" readonly
                                style="width:100%; padding:8px 14px; border:1px solid var(--border-color); border-radius:8px; font-size:13px; color:var(--text-primary); background:var(--bg-input);">
                        </div>
                        <div>
                            <label style="font-size:13px; color:var(--text-secondary); display:block; margin-bottom:6px;">To Lab</label>
                            <input id="transfer_modal_to_lab" type="text" readonly
                                style="width:100%; padding:8px 14px; border:1px solid var(--border-color); border-radius:8px; font-size:13px; color:var(--text-primary); background:var(--bg-input);">
                        </div>
                    </div>
                    <div style="display:flex; align-items:center; gap:16px;">
                        <label style="width:130px; text-align:right; font-size:13px; color:var(--text-secondary);">Diajukan oleh:</label>
                        <input id="transfer_modal_requested_by" type="text" readonly
                            style="width:260px; padding:8px 14px; border:1px solid var(--border-color); border-radius:8px; font-size:13px; color:var(--text-primary); background:var(--bg-input);">
                    </div>
                </div>

                <div style="margin-bottom:20px;">
                    <p style="font-size:13px; color:var(--text-muted); margin-bottom:8px;">Barang yang Ditransfer</p>
                    <table id="transfer_modal_items" style="width:100%; font-size:13px; border:1px solid var(--border-color); border-radius:8px; overflow:hidden; border-collapse:separate; border-spacing:0;">
                        <thead>
                            <tr style="background:var(--bg-table-header);">
                                <th style="padding:8px 14px; text-align:left;">Asset Name</th>
                                <th style="padding:8px 14px; text-align:center;">Qty</th>
                                <th style="padding:8px 14px; text-align:center;">Qty Disetujui</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px; padding:0 32px 24px 32px;">
                <button type="button" onclick="rejectAllTransfer()"
                    style="border:1px solid #dc2626; background:#dc2626; color:#fff; border-radius:8px; padding:8px 16px; font-size:13px; font-weight:600; cursor:pointer;">
                    Tolak Semua
                </button>
                <button type="button" onclick="approveAllTransfer()"
                    style="border:1px solid #16a34a; background:#16a34a; color:#fff; border-radius:8px; padding:8px 16px; font-size:13px; font-weight:600; cursor:pointer;">
                    Setujui Semua
                </button>
                <button type="button" onclick="saveTransferStatuses()"
                    style="border:1px solid #111B4C; background:#111B4C; color:#fff; border-radius:8px; padding:8px 16px; font-size:13px; font-weight:600; cursor:pointer;">
                    Simpan
                </button>
            </div>
        </div>
    </div>

    {{-- ============ (#2) MODAL REVIEW LAB REQUEST (sama seperti di Request Lab) ============ --}}
    <div id="labRequestModal"
        style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.5); align-items:center; justify-content:center;">
        <div
            style="background:var(--bg-modal); border-radius:16px; width:100%; max-width:820px; margin:0 16px; max-height:90vh; overflow:auto; box-shadow:0 20px 60px rgba(0,0,0,0.15); border:1px solid var(--border-color);">
            <div
                style="display:flex; align-items:center; padding:24px 32px 12px 32px; gap:16px; border-bottom:1px solid var(--border-color);">
                <h3 style="font-size:18px; font-weight:700; color:var(--text-primary); flex-shrink:0; margin:0;">
                    Request Information
                </h3>
                <div style="flex:1;">
                    <div style="height:6px; background:var(--border-color); border-radius:99px; overflow:hidden;">
                        <div id="labModalProgress"
                            style="height:6px; background:#93c5fd; border-radius:99px; width:0%; transition:width 0.5s;"></div>
                    </div>
                </div>
                <button onclick="closeLabRequestModal()"
                    style="color:var(--text-muted); background:none; border:none; cursor:pointer; padding:4px; line-height:1;">
                    <svg xmlns="http://www.w3.org/2000/svg" style="width:20px;height:20px;" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div style="padding:16px 32px 8px 32px;">
                <div style="display:flex; flex-direction:column; gap:12px; margin-bottom:20px;">
                    <div style="display:flex; align-items:center; gap:16px;">
                        <label style="width:110px; text-align:right; font-size:13px; color:var(--text-secondary);">ID Request:</label>
                        <input id="lab_modal_id" type="text" readonly
                            style="width:240px; padding:8px 14px; border:1px solid var(--border-color); border-radius:8px; font-size:13px; color:var(--text-primary); background:var(--bg-input);">
                    </div>
                    <div style="display:flex; align-items:center; gap:16px;">
                        <label style="width:110px; text-align:right; font-size:13px; color:var(--text-secondary);">Name:</label>
                        <input id="lab_modal_name" type="text" readonly
                            style="width:240px; padding:8px 14px; border:1px solid var(--border-color); border-radius:8px; font-size:13px; color:var(--text-primary); background:var(--bg-input);">
                    </div>
                    <div style="display:flex; align-items:center; gap:16px;">
                        <label style="width:110px; text-align:right; font-size:13px; color:var(--text-secondary);">Total:</label>
                        <input id="lab_modal_total" type="text" readonly
                            style="width:240px; padding:8px 14px; border:1px solid var(--border-color); border-radius:8px; font-size:13px; color:var(--text-primary); background:var(--bg-input);">
                    </div>
                </div>

                @php
                    $labModalSections = [
                        'electronic'     => 'Electronic Category',
                        'non_electronic' => 'Non-Electronic Category',
                        'component_pc'   => 'PC Component Category',
                    ];
                @endphp

                @foreach ($labModalSections as $key => $label)
                    <p style="font-size:13px; color:var(--text-muted); margin:14px 0 8px;">{{ $label }}</p>
                    <table style="width:100%; font-size:13px; border:1px solid var(--border-color); border-radius:8px; overflow:hidden; border-collapse:separate; border-spacing:0; margin-bottom:6px;">
                        <thead>
                            <tr style="background:var(--bg-table-header);">
                                <th style="padding:8px 14px; text-align:left;">Asset Name</th>
                                <th style="padding:8px 14px; text-align:center;">Qty</th>
                                <th style="padding:8px 14px; text-align:center;">Action</th>
                            </tr>
                        </thead>
                        <tbody id="lab_modal_{{ $key }}"></tbody>
                    </table>
                @endforeach
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px; padding:12px 32px 24px 32px;">
                <button type="button" onclick="rejectAllLabRequest()"
                    style="border:1px solid #dc2626; background:#dc2626; color:#fff; border-radius:8px; padding:8px 16px; font-size:13px; font-weight:600; cursor:pointer;">
                    Reject All
                </button>
                <button type="button" onclick="approveAllLabRequest()"
                    style="border:1px solid #16a34a; background:#16a34a; color:#fff; border-radius:8px; padding:8px 16px; font-size:13px; font-weight:600; cursor:pointer;">
                    Approve All
                </button>
                <button type="button" onclick="saveLabRequestStatuses()"
                    style="border:1px solid #111B4C; background:#111B4C; color:#fff; border-radius:8px; padding:8px 16px; font-size:13px; font-weight:600; cursor:pointer;">
                    Save
                </button>
            </div>
        </div>
    </div>

@endsection

@push('styles')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        const chartData = @json($chartData);
        const labels = chartData.map(d => d.label);
        const active = chartData.map(d => d.active);
        const inactive = chartData.map(d => d.inactive);

        const ctx = document.getElementById('labConditionsChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels,
                datasets: [
                    { label: 'Active', data: active, backgroundColor: '#111B4C', borderRadius: 4, borderSkipped: false },
                    { label: 'Inactive', data: inactive, backgroundColor: '#98083D', borderRadius: 4, borderSkipped: false },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => ` ${ctx.dataset.label}: ${ctx.parsed.y}` } } },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 11 }, color: '#9ca3af' }, border: { display: false } },
                    y: { beginAtZero: true, grid: { color: '#f3f4f6' }, ticks: { font: { size: 11 }, color: '#9ca3af', stepSize: 8 }, border: { display: false } },
                },
            },
        });

        function toggleAll(master) {
            document.querySelectorAll('.row-check').forEach(cb => cb.checked = master.checked);
        }

        let currentReturnRequestId = null;
        let currentTransferRequestId = null;

        function openReturnDetailModal(requestId) {
            currentReturnRequestId = requestId;
            const modal = document.getElementById('returnDetailModal');
            modal.style.display = 'flex';
            document.getElementById('returnModalProgress').style.width = '30%';
            const loadingRow = '<tr><td colspan="4" style="padding:12px;text-align:center;color:var(--text-muted);font-size:12px;">Memuat...</td></tr>';
            document.querySelector('#return_modal_items tbody').innerHTML = loadingRow;

            fetch(`/return-requests/${requestId}/detail`)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('returnModalProgress').style.width = '100%';
                    document.getElementById('return_modal_request_code').value = data.request_code;
                    document.getElementById('return_modal_lab').value = data.lab_name;
                    document.getElementById('return_modal_requested_by').value = data.requested_by;
                    document.querySelector('#return_modal_items tbody').innerHTML = data.items.map(item => `
                        <tr style="border-top:1px solid var(--border-color);">
                            <td style="padding:8px 14px;color:var(--text-primary);">${item.asset_name}</td>
                            <td style="padding:8px 14px;text-align:center;color:var(--text-primary);">${item.quantity}</td>
                            <td style="padding:8px 14px;text-align:center;color:var(--text-primary);">${item.condition}</td>
                            <td style="padding:8px 14px;text-align:center;">
                                <div style="display:flex;align-items:center;justify-content:center;gap:6px;flex-wrap:wrap;">
                                    ${getReturnStatusBadge(item)}
                                    <input type="number" data-return-item-id="${item.id}"
                                        value="${item.quantity_approved ?? item.quantity}" min="0" max="${item.quantity}"
                                        style="min-width:80px;padding:4px 8px;font-size:12px;border:1px solid var(--border-color);border-radius:4px;background:var(--bg-input);color:var(--text-primary);text-align:center;">
                                </div>
                            </td>
                        </tr>
                    `).join('') || '<tr><td colspan="4" style="padding:12px;text-align:center;color:var(--text-muted);font-size:12px;">Tidak ada barang</td></tr>';
                })
                .catch(() => {
                    document.getElementById('returnModalProgress').style.width = '100%';
                    const error = '<tr><td colspan="4" style="padding:12px;text-align:center;color:#f87171;font-size:12px;">Gagal memuat data</td></tr>';
                    document.querySelector('#return_modal_items tbody').innerHTML = error;
                });
        }

        function getReturnStatusBadge(item) {
            if (!item.status) {
                if (item.quantity_approved == 0) {
                    return '<span style="background:#dc2626;color:#fff;padding:2px 8px;border-radius:4px;font-size:11px;">Rejected</span>';
                } else if (item.quantity_approved < item.quantity) {
                    return '<span style="background:#2563eb;color:#fff;padding:2px 8px;border-radius:4px;font-size:11px;">Partial</span>';
                } else if (item.quantity_approved == item.quantity) {
                    return '<span style="background:#16a34a;color:#fff;padding:2px 8px;border-radius:4px;font-size:11px;">Approved</span>';
                }
                return '<span style="background:#facc15;color:#713f12;padding:2px 8px;border-radius:4px;font-size:11px;">Pending</span>';
            }
            return '<span style="background:#facc15;color:#713f12;padding:2px 8px;border-radius:4px;font-size:11px;">Pending</span>';
        }

        function closeReturnDetailModal() {
            currentReturnRequestId = null;
            document.getElementById('returnDetailModal').style.display = 'none';
            document.getElementById('returnModalProgress').style.width = '0%';
        }

        window.saveReturnStatuses = async function() {
            if (!currentReturnRequestId) {
                alert('Buka detail request terlebih dahulu.');
                return;
            }

            const items = [];
            document.querySelectorAll('[data-return-item-id]').forEach(input => {
                items.push({
                    id: input.dataset.returnItemId,
                    quantity_approved: parseInt(input.value) || 0
                });
            });

            try {
                const response = await fetch(`/return-requests/${currentReturnRequestId}/approve`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ items })
                });
                const data = await response.json();
                if (!data.success) {
                    alert(data.message || 'Gagal menyimpan status.');
                    return;
                }
                alert('Status berhasil disimpan!');
                closeReturnDetailModal();
                window.location.reload();
            } catch (e) {
                alert('Gagal menyimpan status.');
            }
        }

        window.approveAllReturn = function() {
            if (!currentReturnRequestId) {
                alert('Buka detail request terlebih dahulu.');
                return;
            }
            document.querySelectorAll('[data-return-item-id]').forEach(input => {
                input.value = input.getAttribute('max');
            });
        }

        window.rejectAllReturn = function() {
            if (!currentReturnRequestId) {
                alert('Buka detail request terlebih dahulu.');
                return;
            }
            if (confirm('Apakah Anda yakin ingin menolak seluruh request ini?')) {
                fetch(`/return-requests/${currentReturnRequestId}/reject`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ rejection_reason: 'Ditolak seluruhnya oleh SPV' })
                })
                .then(res => res.json())
                .then(data => {
                    if (!data.success) {
                        alert(data.message || 'Gagal menolak request.');
                        return;
                    }
                    alert('Request berhasil ditolak!');
                    closeReturnDetailModal();
                    window.location.reload();
                })
                .catch(() => alert('Gagal menolak request.'));
            }
        }

        function openTransferDetailModal(requestId) {
            currentTransferRequestId = requestId;
            const modal = document.getElementById('transferDetailModal');
            modal.style.display = 'flex';
            document.getElementById('transferModalProgress').style.width = '30%';
            const loadingRow = '<tr><td colspan="3" style="padding:12px;text-align:center;color:var(--text-muted);font-size:12px;">Memuat...</td></tr>';
            document.querySelector('#transfer_modal_items tbody').innerHTML = loadingRow;

            fetch(`/transfer-requests/${requestId}/detail`)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('transferModalProgress').style.width = '100%';
                    document.getElementById('transfer_modal_request_code').value = data.request_code;
                    document.getElementById('transfer_modal_from_lab').value = data.from_lab;
                    document.getElementById('transfer_modal_to_lab').value = data.to_lab;
                    document.getElementById('transfer_modal_requested_by').value = data.requested_by;
                    document.querySelector('#transfer_modal_items tbody').innerHTML = data.items.map(item => `
                        <tr style="border-top:1px solid var(--border-color);">
                            <td style="padding:8px 14px;color:var(--text-primary);">${item.asset_name}</td>
                            <td style="padding:8px 14px;text-align:center;color:var(--text-primary);">${item.quantity}</td>
                            <td style="padding:8px 14px;text-align:center;">
                                <div style="display:flex;align-items:center;justify-content:center;gap:6px;flex-wrap:wrap;">
                                    ${getTransferStatusBadge(item)}
                                    <input type="number" data-tr-item-id="${item.id}"
                                        value="${item.quantity_approved ?? item.quantity}" min="0" max="${item.quantity}"
                                        style="min-width:80px;padding:4px 8px;font-size:12px;border:1px solid var(--border-color);border-radius:4px;background:var(--bg-input);color:var(--text-primary);text-align:center;">
                                </div>
                            </td>
                        </tr>
                    `).join('') || '<tr><td colspan="3" style="padding:12px;text-align:center;color:var(--text-muted);font-size:12px;">Tidak ada barang</td></tr>';
                })
                .catch(() => {
                    document.getElementById('transferModalProgress').style.width = '100%';
                    const error = '<tr><td colspan="3" style="padding:12px;text-align:center;color:#f87171;font-size:12px;">Gagal memuat data</td></tr>';
                    document.querySelector('#transfer_modal_items tbody').innerHTML = error;
                });
        }

        function getTransferStatusBadge(item) {
            if (!item.status) {
                if (item.quantity_approved == 0) {
                    return '<span style="background:#dc2626;color:#fff;padding:2px 8px;border-radius:4px;font-size:11px;">Rejected</span>';
                } else if (item.quantity_approved < item.quantity) {
                    return '<span style="background:#2563eb;color:#fff;padding:2px 8px;border-radius:4px;font-size:11px;">Partial</span>';
                } else if (item.quantity_approved == item.quantity) {
                    return '<span style="background:#16a34a;color:#fff;padding:2px 8px;border-radius:4px;font-size:11px;">Approved</span>';
                }
                return '<span style="background:#facc15;color:#713f12;padding:2px 8px;border-radius:4px;font-size:11px;">Pending</span>';
            }
            return '<span style="background:#facc15;color:#713f12;padding:2px 8px;border-radius:4px;font-size:11px;">Pending</span>';
        }

        function closeTransferDetailModal() {
            currentTransferRequestId = null;
            document.getElementById('transferDetailModal').style.display = 'none';
            document.getElementById('transferModalProgress').style.width = '0%';
        }

        window.saveTransferStatuses = async function() {
            if (!currentTransferRequestId) {
                alert('Buka detail request terlebih dahulu.');
                return;
            }

            const items = [];
            document.querySelectorAll('[data-tr-item-id]').forEach(input => {
                items.push({
                    id: input.dataset.trItemId,
                    quantity_approved: parseInt(input.value) || 0
                });
            });

            try {
                const response = await fetch(`/transfer-requests/${currentTransferRequestId}/approve`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ items })
                });
                const data = await response.json();
                if (!data.success) {
                    alert(data.message || 'Gagal menyimpan status.');
                    return;
                }
                alert('Status berhasil disimpan!');
                closeTransferDetailModal();
                window.location.reload();
            } catch (e) {
                alert('Gagal menyimpan status.');
            }
        }

        window.approveAllTransfer = function() {
            if (!currentTransferRequestId) {
                alert('Buka detail request terlebih dahulu.');
                return;
            }
            document.querySelectorAll('[data-tr-item-id]').forEach(input => {
                input.value = input.getAttribute('max');
            });
        }

        window.rejectAllTransfer = function() {
            if (!currentTransferRequestId) {
                alert('Buka detail request terlebih dahulu.');
                return;
            }
            if (confirm('Apakah Anda yakin ingin menolak seluruh request ini?')) {
                fetch(`/transfer-requests/${currentTransferRequestId}/reject`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ rejection_reason: 'Ditolak seluruhnya oleh SPV' })
                })
                .then(res => res.json())
                .then(data => {
                    if (!data.success) {
                        alert(data.message || 'Gagal menolak request.');
                        return;
                    }
                    alert('Request berhasil ditolak!');
                    closeTransferDetailModal();
                    window.location.reload();
                })
                .catch(() => alert('Gagal menolak request.'));
            }
        }

        document.getElementById('returnDetailModal').addEventListener('click', function(event) {
            if (event.target === this) closeReturnDetailModal();
        });
        document.getElementById('transferDetailModal').addEventListener('click', function(event) {
            if (event.target === this) closeTransferDetailModal();
        });

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeReturnDetailModal();
                closeTransferDetailModal();
                closeLabRequestModal();
            }
        });

        /* ============ (#2) MODAL REVIEW LAB REQUEST ============ */
        let currentLabRequestId = null;

        const LAB_SECTION_KEYS = ['electronic', 'non_electronic', 'component_pc'];

        function labStatusBadge(status) {
            const s = (status || 'pending').toLowerCase();
            if (s === 'approved') return '<span style="background:#16a34a;color:#fff;padding:2px 8px;border-radius:4px;font-size:11px;">Approved</span>';
            if (s === 'rejected') return '<span style="background:#dc2626;color:#fff;padding:2px 8px;border-radius:4px;font-size:11px;">Rejected</span>';
            return '<span style="background:#facc15;color:#713f12;padding:2px 8px;border-radius:4px;font-size:11px;">Pending</span>';
        }

        function labRow(item) {
            const s = (item.status || 'pending').toLowerCase();
            return `
                <tr style="border-top:1px solid var(--border-color);">
                    <td style="padding:8px 14px;color:var(--text-primary);">${item.asset_name}</td>
                    <td style="padding:8px 14px;text-align:center;color:var(--text-primary);">${item.quantity}</td>
                    <td style="padding:8px 14px;">
                        <div style="display:flex;align-items:center;justify-content:flex-end;gap:8px;">
                            <span data-lab-badge>${labStatusBadge(s)}</span>
                            <select data-lab-item-id="${item.item_id}"
                                style="min-width:110px;padding:4px 8px;font-size:12px;border:1px solid var(--border-color);border-radius:4px;background:var(--bg-input);color:var(--text-primary);">
                                <option value="pending"  ${s === 'pending'  ? 'selected' : ''}>Pending</option>
                                <option value="approved" ${s === 'approved' ? 'selected' : ''}>Approve</option>
                                <option value="rejected" ${s === 'rejected' ? 'selected' : ''}>Reject</option>
                            </select>
                        </div>
                    </td>
                </tr>`;
        }

        function openLabRequestModal(requestId) {
            currentLabRequestId = requestId;
            document.getElementById('labRequestModal').style.display = 'flex';
            document.getElementById('labModalProgress').style.width = '30%';

            LAB_SECTION_KEYS.forEach(k => {
                document.getElementById('lab_modal_' + k).innerHTML =
                    '<tr><td colspan="3" style="padding:12px;text-align:center;color:var(--text-muted);font-size:12px;">Memuat...</td></tr>';
            });

            fetch(`/requestlab/${requestId}/detail`)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('labModalProgress').style.width = '100%';
                    document.getElementById('lab_modal_id').value = data.request_id;
                    document.getElementById('lab_modal_name').value = data.user_name;
                    document.getElementById('lab_modal_total').value = data.total_request;

                    LAB_SECTION_KEYS.forEach(k => {
                        const items = data[k] || [];
                        document.getElementById('lab_modal_' + k).innerHTML = items.length
                            ? items.map(labRow).join('')
                            : '<tr><td colspan="3" style="padding:10px;text-align:center;color:var(--text-muted);font-size:12px;">Tidak ada data</td></tr>';
                    });

                    // Badge ikut berubah saat dropdown diganti.
                    document.querySelectorAll('#labRequestModal [data-lab-item-id]').forEach(sel => {
                        sel.addEventListener('change', e => {
                            const badge = e.target.closest('div').querySelector('[data-lab-badge]');
                            if (badge) badge.innerHTML = labStatusBadge(e.target.value);
                        });
                    });
                })
                .catch(() => {
                    LAB_SECTION_KEYS.forEach(k => {
                        document.getElementById('lab_modal_' + k).innerHTML =
                            '<tr><td colspan="3" style="padding:12px;text-align:center;color:#f87171;font-size:12px;">Gagal memuat data</td></tr>';
                    });
                });
        }

        function closeLabRequestModal() {
            currentLabRequestId = null;
            document.getElementById('labRequestModal').style.display = 'none';
            document.getElementById('labModalProgress').style.width = '0%';
        }

        async function saveLabRequestStatuses() {
            if (!currentLabRequestId) return;
            const selects = document.querySelectorAll('#labRequestModal [data-lab-item-id]');
            const csrf = document.querySelector('meta[name="csrf-token"]').content;
            try {
                for (const sel of selects) {
                    await fetch(`/requestlab/item/${sel.dataset.labItemId}/status`, {
                        method: 'PATCH',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                        body: JSON.stringify({ status: sel.value }),
                    });
                }
                alert('Status request berhasil disimpan!');
                closeLabRequestModal();
                window.location.reload();
            } catch (e) {
                alert('Gagal menyimpan status.');
            }
        }

        function applyAllLabRequest(status, confirmMsg) {
            if (!currentLabRequestId) return;
            if (confirmMsg && !confirm(confirmMsg)) return;
            fetch(`/requestlab/${currentLabRequestId}/status`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ status }),
            })
            .then(res => res.json())
            .then(data => {
                if (data.success === false) { alert(data.message || 'Gagal.'); return; }
                alert('Request berhasil diperbarui!');
                closeLabRequestModal();
                window.location.reload();
            })
            .catch(() => alert('Gagal memproses request.'));
        }

        function approveAllLabRequest() {
            applyAllLabRequest('approved', null);
        }

        function rejectAllLabRequest() {
            applyAllLabRequest('rejected', 'Tolak seluruh item pada request ini?');
        }

        document.getElementById('labRequestModal').addEventListener('click', function(event) {
            if (event.target === this) closeLabRequestModal();
        });
    </script>
@endpush