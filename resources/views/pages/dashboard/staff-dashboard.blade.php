@extends('panel.content')

@section('title', 'Staff Dashboard')

@section('content')
    <div class="db-wrap">

    {{-- ── STAT CARDS ── --}}
    <div class="db-cards-row" style="display:grid; grid-template-columns:repeat(4,1fr); gap:16px;">

        {{-- Users --}}
        <div class="db-stat-card">
            <div class="stat-info">
                <span class="stat-label">Users</span>
                <span class="stat-value">{{ number_format($totalUsers) }}</span>
            </div>
            <div class="stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
            </div>
        </div>

        {{-- Total PC --}}
        <div class="db-stat-card">
            <div class="stat-info">
                <span class="stat-label">Total PC</span>
                <span class="stat-value">{{ number_format($totalPc) }}</span>
            </div>
            <div class="stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="3" width="20" height="14" rx="2"/>
                    <path d="M8 21h8M12 17v4"/>
                </svg>
            </div>
        </div>

        {{-- Lab Request --}}
        <div class="db-stat-card">
            <div class="stat-info">
                <span class="stat-label">Lab Request</span>
                <span class="stat-value">{{ number_format($totalRequestLab) }}</span>
            </div>
            <div class="stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
                    <rect x="8" y="2" width="8" height="4" rx="1"/>
                    <circle cx="12" cy="14" r="2"/>
                </svg>
            </div>
        </div>

        {{-- Return Request --}}
        <div class="db-stat-card">
            <div class="stat-info">
                <span class="stat-label">Return Request</span>
                <span class="stat-value">{{ number_format($totalReturnRequests) }}</span>
            </div>
            <div class="stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 10v-4h4"/>
                    <path d="M12 6a7 7 0 0 1 7 7"/>
                    <path d="M12 6a7 7 0 0 0-7 7"/>
                </svg>
            </div>
        </div>

        {{-- Transfer Request --}}
        <div class="db-stat-card">
            <div class="stat-info">
                <span class="stat-label">Transfer Request</span>
                <span class="stat-value">{{ number_format($totalTransferRequests) }}</span>
            </div>
            <div class="stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M8 7h12l-4-4m4 4l-4 4"/>
                    <path d="M16 17H4l4 4M4 17l4-4"/>
                </svg>
            </div>
        </div>

    </div>

    {{-- ── (#3) LOW STOCK ITEMS (ambil dari Asset Lab, stok < 3) ── --}}
    <div class="db-card" style="padding:18px 20px;">
        <h2 class="db-card-title" style="margin:0 0 14px;">Low Stock Items</h2>
        <div style="display:flex; flex-direction:column; gap:10px;">
            @forelse($labLowStockItems as $item)
                <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; background:#fef2f2; border:1px solid #fee2e2; border-radius:10px; padding:10px 14px;">
                    <div style="display:flex; align-items:center; gap:10px; min-width:0;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2"
                             stroke-linecap="round" stroke-linejoin="round" style="width:18px;height:18px;flex-shrink:0;">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" y1="8" x2="12" y2="12"/>
                            <line x1="12" y1="16" x2="12.01" y2="16"/>
                        </svg>
                        <span style="font-size:13px; font-weight:600; color:#991b1b; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                            {{ $item['asset_name'] }}
                            <span style="font-weight:400; color:#b91c1c;">· {{ $item['lab_name'] }}</span>
                        </span>
                    </div>
                    <span style="font-size:12.5px; font-weight:600; color:#b91c1c; white-space:nowrap;">
                        In stock: {{ $item['in_stock'] }}
                    </span>
                </div>
            @empty
                <p style="color:#9ca3af; font-size:13px; font-style:italic; margin:0;">Semua stok lab aman</p>
            @endforelse
        </div>
    </div>

    {{-- ── MIDDLE: Staff Table + Chart ── --}}
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">

        {{-- Lab Administrator Table --}}
        <div class="db-card" style="padding:0; overflow:hidden;">
            <div style="padding:18px 20px 14px;">
                <h2 class="db-card-title" style="margin:0;">Laboratory Administrator</h2>
            </div>
            <div style="overflow-x:auto;">
                <table class="db-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>NIM</th>
                            <th>Role</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($labStaff as $staff)
                        <tr>
                            <td>{{ $staff->name }}</td>
                            <td style="font-family:monospace; font-size:13px;">{{ $staff->nim }}</td>
                            <td>{{ ucfirst(str_replace(['spv inventory','staff'], ['SPV Inventory','Staff'], $staff->role)) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" style="text-align:center; padding:24px; color:#9ca3af; font-size:13px;">
                                Belum ada staff
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Laboratory Conditions Chart --}}
        <div class="db-card db-chart-card">
            <h2 class="db-card-title">Laboratory Conditions</h2>
                <div class="chart-container" style="position:relative; height:220px;">
                    <canvas id="labConditionsChart"></canvas>
                </div>
                <div class="chart-legend" style="display:flex; align-items:center; gap:6px; font-size:12px; color:#6b7280; margin-top:10px; justify-content:center;">
                    <span style="width:10px; height:10px; border-radius:2px; background:#111B4C; display:inline-block;"></span> Active
                    <span style="width:10px; height:10px; border-radius:2px; background:#98083D; margin-left:16px; display:inline-block;"></span> Inactive
            </div>
            </div>

        </div>

    {{-- ── LAB REQUEST TABLE ── --}}
    <div class="db-card db-table-card" style="padding:0; overflow:hidden;">
        <div style="padding:18px 20px 14px;">
            <h2 class="db-card-title" style="margin:0;">Lab Request</h2>
        </div>
        <div style="overflow-x:auto;">
            <table class="db-table">
                <thead>
                    <tr>
                        <th>ID Request</th>
                        <th>Name</th>
                        <th>Total Request</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentRequests as $req)
                    <tr>
                        <td style="font-family:monospace; font-size:13px;">{{ $req->id }}</td>
                        <td>{{ $req->user->name ?? '-' }}</td>
                        <td>{{ $req->total_requested_items ?? 0 }}</td>
                        <td>{{ \Carbon\Carbon::parse($req->created_at)->format('d-m-y') }}</td>
                        <td>
                            @php
                                $status = strtolower($req->request_status ?? 'pending');
                                $badgeClass = match($status) {
                                    'approved' => 'badge-approved',
                                    'rejected' => 'badge-rejected',
                                    'partial'  => 'badge-partial',
                                    default    => 'badge-pending',
                                };
                            @endphp
                            <span class="status-badge {{ $badgeClass }}">
                                {{ ucfirst($req->request_status ?? 'Pending') }}
                            </span>
                        </td>
                        <td>
                            <div class="action-btns">
                                {{-- view only (staff tidak boleh edit) --}}
                                <button type="button" onclick="openStaffLabRequestModal({{ $req->id }})"
                                        class="action-btn" title="View" style="color:#6b7280; background:none; border:none; cursor:pointer;">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                         stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align:center; padding:32px; color:#9ca3af; font-size:13px;">
                            Belum ada lab request
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── RETURN REQUEST TABLE ── --}}
    <div class="db-card db-table-card" style="padding:0; overflow:hidden;">
        <div style="padding:18px 20px 14px;">
            <h2 class="db-card-title" style="margin:0;">Return Request</h2>
        </div>
        <div style="overflow-x:auto;">
            <table class="db-table">
                <thead>
                    <tr>
                        <th>ID Request</th>
                        <th>Lab</th>
                        <th>Name</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentReturnRequests as $req)
                    <tr>
                        <td style="font-family:monospace; font-size:13px;">{{ $req->request_code }}</td>
                        <td>{{ $req->laboratory?->lab_name ?? '-' }}</td>
                        <td>{{ $req->requestedBy?->name ?? '-' }}</td>
                        <td>{{ \Carbon\Carbon::parse($req->created_at)->format('d-m-y') }}</td>
                        <td>
                            @php
                                $status = strtolower($req->status ?? 'pending');
                                $badgeClass = match($status) {
                                    'completed' => 'badge-approved',
                                    'rejected' => 'badge-rejected',
                                    'partial'  => 'badge-partial',
                                    default    => 'badge-pending',
                                };
                            @endphp
                            <span class="status-badge {{ $badgeClass }}">
                                {{ ucfirst($req->status ?? 'Pending') }}
                            </span>
                        </td>
                        <td>
                            <div class="action-btns">
                                <button type="button" onclick="openStaffReturnModal({{ $req->id }})" class="action-btn" title="View" style="color:#6b7280; background:none; border:none; cursor:pointer;">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                         stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align:center; padding:32px; color:#9ca3af; font-size:13px;">
                            Belum ada return request
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── TRANSFER REQUEST TABLE ── --}}
    <div class="db-card db-table-card" style="padding:0; overflow:hidden;">
        <div style="padding:18px 20px 14px;">
            <h2 class="db-card-title" style="margin:0;">Transfer Request</h2>
        </div>
        <div style="overflow-x:auto;">
            <table class="db-table">
                <thead>
                    <tr>
                        <th>ID Request</th>
                        <th>From Lab</th>
                        <th>To Lab</th>
                        <th>Name</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentTransferRequests as $req)
                    <tr>
                        <td style="font-family:monospace; font-size:13px;">{{ $req->request_code }}</td>
                        <td>{{ $req->fromLab?->lab_name ?? '-' }}</td>
                        <td>{{ $req->toLab?->lab_name ?? '-' }}</td>
                        <td>{{ $req->requestedBy?->name ?? '-' }}</td>
                        <td>{{ \Carbon\Carbon::parse($req->created_at)->format('d-m-y') }}</td>
                        <td>
                            @php
                                $status = strtolower($req->status ?? 'pending');
                                $badgeClass = match($status) {
                                    'completed' => 'badge-approved',
                                    'rejected' => 'badge-rejected',
                                    'partial'  => 'badge-partial',
                                    default    => 'badge-pending',
                                };
                            @endphp
                            <span class="status-badge {{ $badgeClass }}">
                                {{ ucfirst($req->status ?? 'Pending') }}
                            </span>
                        </td>
                        <td>
                            <div class="action-btns">
                                <button type="button" onclick="openStaffTransferModal({{ $req->id }})" class="action-btn" title="View" style="color:#6b7280; background:none; border:none; cursor:pointer;">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                         stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align:center; padding:32px; color:#9ca3af; font-size:13px;">
                            Belum ada transfer request
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    </div>

    {{-- ============ (#3) MODAL VIEW-ONLY UNTUK STAFF ============ --}}

    {{-- Lab Request (read-only) --}}
    <div id="staffLabModal" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.5); align-items:center; justify-content:center;">
        <div style="background:#fff; border-radius:16px; width:100%; max-width:780px; margin:0 16px; max-height:90vh; overflow:auto; box-shadow:0 20px 60px rgba(0,0,0,0.15);">
            <div style="display:flex; align-items:center; justify-content:space-between; padding:22px 28px 14px; border-bottom:1px solid #e5e7eb;">
                <h3 style="font-size:18px; font-weight:700; color:#111827; margin:0;">Request Information</h3>
                <button onclick="closeStaffModal('staffLabModal')" style="background:none; border:none; cursor:pointer; color:#6b7280;">
                    <svg style="width:20px;height:20px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div style="padding:18px 28px;">
                <div style="display:flex; flex-direction:column; gap:10px; margin-bottom:18px;">
                    <div style="display:flex; gap:12px; align-items:center;"><label style="width:100px; text-align:right; font-size:13px; color:#6b7280;">ID Request:</label><input id="staff_lab_id" readonly style="width:220px; padding:7px 12px; border:1px solid #e5e7eb; border-radius:8px; font-size:13px; background:#f9fafb;"></div>
                    <div style="display:flex; gap:12px; align-items:center;"><label style="width:100px; text-align:right; font-size:13px; color:#6b7280;">Name:</label><input id="staff_lab_name" readonly style="width:220px; padding:7px 12px; border:1px solid #e5e7eb; border-radius:8px; font-size:13px; background:#f9fafb;"></div>
                    <div style="display:flex; gap:12px; align-items:center;"><label style="width:100px; text-align:right; font-size:13px; color:#6b7280;">Total:</label><input id="staff_lab_total" readonly style="width:220px; padding:7px 12px; border:1px solid #e5e7eb; border-radius:8px; font-size:13px; background:#f9fafb;"></div>
                </div>
                @foreach (['electronic' => 'Electronic Category', 'non_electronic' => 'Non-Electronic Category', 'component_pc' => 'PC Component Category'] as $k => $label)
                    <p style="font-size:13px; color:#6b7280; margin:12px 0 6px;">{{ $label }}</p>
                    <table style="width:100%; font-size:13px; border:1px solid #e5e7eb; border-radius:8px; border-collapse:separate; border-spacing:0; overflow:hidden; margin-bottom:6px;">
                        <thead><tr style="background:#f3f4f6;"><th style="padding:8px 14px; text-align:left;">Asset Name</th><th style="padding:8px 14px; text-align:center;">Qty</th><th style="padding:8px 14px; text-align:center;">Status</th></tr></thead>
                        <tbody id="staff_lab_{{ $k }}"></tbody>
                    </table>
                @endforeach
            </div>
            <div style="display:flex; justify-content:flex-end; padding:0 28px 22px;">
                <button onclick="closeStaffModal('staffLabModal')" style="border:1px solid #d1d5db; background:#fff; color:#374151; border-radius:8px; padding:8px 18px; font-size:13px; font-weight:600; cursor:pointer;">Close</button>
            </div>
        </div>
    </div>

    {{-- Return Request (read-only) --}}
    <div id="staffReturnModal" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.5); align-items:center; justify-content:center;">
        <div style="background:#fff; border-radius:16px; width:100%; max-width:720px; margin:0 16px; max-height:90vh; overflow:auto; box-shadow:0 20px 60px rgba(0,0,0,0.15);">
            <div style="display:flex; align-items:center; justify-content:space-between; padding:22px 28px 14px; border-bottom:1px solid #e5e7eb;">
                <h3 style="font-size:18px; font-weight:700; color:#111827; margin:0;">Return Request Information</h3>
                <button onclick="closeStaffModal('staffReturnModal')" style="background:none; border:none; cursor:pointer; color:#6b7280;"><svg style="width:20px;height:20px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <div style="padding:18px 28px;">
                <div style="display:flex; flex-direction:column; gap:10px; margin-bottom:18px;">
                    <div style="display:flex; gap:12px; align-items:center;"><label style="width:120px; text-align:right; font-size:13px; color:#6b7280;">Kode Request:</label><input id="staff_ret_code" readonly style="width:230px; padding:7px 12px; border:1px solid #e5e7eb; border-radius:8px; font-size:13px; background:#f9fafb;"></div>
                    <div style="display:flex; gap:12px; align-items:center;"><label style="width:120px; text-align:right; font-size:13px; color:#6b7280;">Lab:</label><input id="staff_ret_lab" readonly style="width:230px; padding:7px 12px; border:1px solid #e5e7eb; border-radius:8px; font-size:13px; background:#f9fafb;"></div>
                    <div style="display:flex; gap:12px; align-items:center;"><label style="width:120px; text-align:right; font-size:13px; color:#6b7280;">Diajukan oleh:</label><input id="staff_ret_by" readonly style="width:230px; padding:7px 12px; border:1px solid #e5e7eb; border-radius:8px; font-size:13px; background:#f9fafb;"></div>
                </div>
                <p style="font-size:13px; color:#6b7280; margin:0 0 6px;">Barang yang Diretur</p>
                <table style="width:100%; font-size:13px; border:1px solid #e5e7eb; border-radius:8px; border-collapse:separate; border-spacing:0; overflow:hidden;">
                    <thead><tr style="background:#f3f4f6;"><th style="padding:8px 14px; text-align:left;">Asset Name</th><th style="padding:8px 14px; text-align:center;">Qty</th><th style="padding:8px 14px; text-align:center;">Qty Disetujui</th></tr></thead>
                    <tbody id="staff_ret_items"></tbody>
                </table>
            </div>
            <div style="display:flex; justify-content:flex-end; padding:0 28px 22px;">
                <button onclick="closeStaffModal('staffReturnModal')" style="border:1px solid #d1d5db; background:#fff; color:#374151; border-radius:8px; padding:8px 18px; font-size:13px; font-weight:600; cursor:pointer;">Close</button>
            </div>
        </div>
    </div>

    {{-- Transfer Request (read-only) --}}
    <div id="staffTransferModal" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.5); align-items:center; justify-content:center;">
        <div style="background:#fff; border-radius:16px; width:100%; max-width:720px; margin:0 16px; max-height:90vh; overflow:auto; box-shadow:0 20px 60px rgba(0,0,0,0.15);">
            <div style="display:flex; align-items:center; justify-content:space-between; padding:22px 28px 14px; border-bottom:1px solid #e5e7eb;">
                <h3 style="font-size:18px; font-weight:700; color:#111827; margin:0;">Transfer Request Information</h3>
                <button onclick="closeStaffModal('staffTransferModal')" style="background:none; border:none; cursor:pointer; color:#6b7280;"><svg style="width:20px;height:20px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <div style="padding:18px 28px;">
                <div style="display:flex; flex-direction:column; gap:10px; margin-bottom:18px;">
                    <div style="display:flex; gap:12px; align-items:center;"><label style="width:120px; text-align:right; font-size:13px; color:#6b7280;">Kode Request:</label><input id="staff_trf_code" readonly style="width:230px; padding:7px 12px; border:1px solid #e5e7eb; border-radius:8px; font-size:13px; background:#f9fafb;"></div>
                    <div style="display:flex; gap:12px; align-items:center;"><label style="width:120px; text-align:right; font-size:13px; color:#6b7280;">From Lab:</label><input id="staff_trf_from" readonly style="width:230px; padding:7px 12px; border:1px solid #e5e7eb; border-radius:8px; font-size:13px; background:#f9fafb;"></div>
                    <div style="display:flex; gap:12px; align-items:center;"><label style="width:120px; text-align:right; font-size:13px; color:#6b7280;">To Lab:</label><input id="staff_trf_to" readonly style="width:230px; padding:7px 12px; border:1px solid #e5e7eb; border-radius:8px; font-size:13px; background:#f9fafb;"></div>
                    <div style="display:flex; gap:12px; align-items:center;"><label style="width:120px; text-align:right; font-size:13px; color:#6b7280;">Diajukan oleh:</label><input id="staff_trf_by" readonly style="width:230px; padding:7px 12px; border:1px solid #e5e7eb; border-radius:8px; font-size:13px; background:#f9fafb;"></div>
                </div>
                <p style="font-size:13px; color:#6b7280; margin:0 0 6px;">Barang yang Ditransfer</p>
                <table style="width:100%; font-size:13px; border:1px solid #e5e7eb; border-radius:8px; border-collapse:separate; border-spacing:0; overflow:hidden;">
                    <thead><tr style="background:#f3f4f6;"><th style="padding:8px 14px; text-align:left;">Asset Name</th><th style="padding:8px 14px; text-align:center;">Qty</th><th style="padding:8px 14px; text-align:center;">Qty Disetujui</th></tr></thead>
                    <tbody id="staff_trf_items"></tbody>
                </table>
            </div>
            <div style="display:flex; justify-content:flex-end; padding:0 28px 22px;">
                <button onclick="closeStaffModal('staffTransferModal')" style="border:1px solid #d1d5db; background:#fff; color:#374151; border-radius:8px; padding:8px 18px; font-size:13px; font-weight:600; cursor:pointer;">Close</button>
            </div>
        </div>
    </div>

@endsection

@push('styles')
    <style>
        .db-wrap {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

    .db-cards-row {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
    }

        .db-stat-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            padding: 20px 22px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .dark .db-stat-card {
            background: #1e2130;
            border-color: #2d3148;
        }

        .stat-info {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .stat-label {
            font-size: 13px;
            color: #6b7280;
            font-weight: 500;
        }

        .dark .stat-label {
            color: #94a3b8;
        }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: #111827;
            line-height: 1;
        }

        .dark .stat-value {
            color: #f1f5f9;
        }

        .db-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            padding: 20px 22px;
        }

        .dark .db-card {
            background: #1e2130;
            border-color: #2d3148;
        }

        .db-card-title {
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            margin: 0 0 16px;
        }

        .dark .db-card-title {
            color: #cbd5e1;
        }

        .chart-container {
            position: relative;
            height: 260px;
        }

        .chart-legend {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            color: #6b7280;
            margin-top: 10px;
            justify-content: center;
        }

        .dark .chart-legend {
            color: #94a3b8;
        }

        .legend-dot {
            width: 10px;
            height: 10px;
            border-radius: 2px;
            display: inline-block;
        }

        .db-table-card {
            padding: 0;
            overflow: hidden;
        }

        .table-wrap {
            overflow-x: auto;
        }

        .db-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13.5px;
        }

    .db-table th {
        padding: 14px 16px;
        text-align: left;
        font-size: 13px;
        font-weight: 600;
        color: #374151;
        white-space: nowrap;
        background: #ffffff;
        border-bottom: 1px solid #e5e7eb;
    }

    .dark .db-table th {
        color: #94a3b8;
        background: #1e2130;
        border-bottom-color: #2d3148;
    }

    .db-table td {
        padding: 13px 16px;
        color: #374151;
        border-bottom: 1px solid #f3f4f6;
    }

    .dark .db-table td {
        color: #cbd5e1;
        border-bottom-color: #2d3148;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 5px 14px;
        border-radius: 6px;
        font-size: 12.5px;
        font-weight: 700;
    }

    .badge-pending {
        background: #f59e0b;
        color: #ffffff;
    }

    .badge-approved {
        background: #16a34a;
        color: #ffffff;
    }

    .badge-rejected {
        background: #dc2626;
        color: #ffffff;
    }

    .empty-state {
        color: #9ca3af;
        font-size: 13px;
        font-style: italic;
    }

    @media (max-width: 1024px) {
        .db-cards-row {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 640px) {
        .db-cards-row {
            grid-template-columns: 1fr;
        }

        .stat-value {
            font-size: 26px;
        }
    }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        const chartData = @json($chartData);
        const labels    = chartData.map(d => d.label);
        const active    = chartData.map(d => d.active);
        const inactive  = chartData.map(d => d.inactive);

        const ctx = document.getElementById('labConditionsChart').getContext('2d');

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                        label: 'Active',
                        data: active,
                        backgroundColor: '#111B4C',
                        borderRadius: 4,
                        borderSkipped: false,
                    },
                    {
                        label: 'Inactive',
                        data: inactive,
                        backgroundColor: '#98083D',
                        borderRadius: 4,
                        borderSkipped: false,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 11
                            },
                            color: '#9ca3af'
                        },
                        border: {
                            display: false
                        },
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#f3f4f6'
                        },
                        ticks: {
                            font: {
                                size: 11
                            },
                            color: '#9ca3af',
                            stepSize: 1,
                        },
                        border: {
                            display: false
                        },
                    },
                },
            },
        });

        /* ============ (#3) MODAL VIEW-ONLY STAFF ============ */
        function closeStaffModal(id) {
            const el = document.getElementById(id);
            if (el) el.style.display = 'none';
        }

        function staffBadge(status) {
            const s = (status || 'pending').toString().toLowerCase();
            if (['approved', 'completed', 'done'].includes(s)) return '<span style="background:#16a34a;color:#fff;padding:2px 8px;border-radius:4px;font-size:11px;">' + (s === 'approved' ? 'Approved' : 'Completed') + '</span>';
            if (s === 'rejected') return '<span style="background:#dc2626;color:#fff;padding:2px 8px;border-radius:4px;font-size:11px;">Rejected</span>';
            if (s === 'partial') return '<span style="background:#2563eb;color:#fff;padding:2px 8px;border-radius:4px;font-size:11px;">Partial</span>';
            return '<span style="background:#facc15;color:#713f12;padding:2px 8px;border-radius:4px;font-size:11px;">Pending</span>';
        }

        const STAFF_LAB_KEYS = ['electronic', 'non_electronic', 'component_pc'];

        function openStaffLabRequestModal(id) {
            document.getElementById('staffLabModal').style.display = 'flex';
            STAFF_LAB_KEYS.forEach(k => document.getElementById('staff_lab_' + k).innerHTML =
                '<tr><td colspan="3" style="padding:10px;text-align:center;color:#9ca3af;font-size:12px;">Memuat...</td></tr>');

            fetch(`/requestlab/${id}/detail`)
                .then(r => r.json())
                .then(d => {
                    document.getElementById('staff_lab_id').value = d.request_id;
                    document.getElementById('staff_lab_name').value = d.user_name;
                    document.getElementById('staff_lab_total').value = d.total_request;
                    STAFF_LAB_KEYS.forEach(k => {
                        const items = d[k] || [];
                        document.getElementById('staff_lab_' + k).innerHTML = items.length
                            ? items.map(it => `<tr style="border-top:1px solid #e5e7eb;"><td style="padding:8px 14px;">${it.asset_name}</td><td style="padding:8px 14px;text-align:center;">${it.quantity}</td><td style="padding:8px 14px;text-align:center;">${staffBadge(it.status)}</td></tr>`).join('')
                            : '<tr><td colspan="3" style="padding:10px;text-align:center;color:#9ca3af;font-size:12px;">Tidak ada data</td></tr>';
                    });
                })
                .catch(() => STAFF_LAB_KEYS.forEach(k => document.getElementById('staff_lab_' + k).innerHTML =
                    '<tr><td colspan="3" style="padding:10px;text-align:center;color:#f87171;font-size:12px;">Gagal memuat data</td></tr>'));
        }

        function openStaffReturnModal(id) {
            document.getElementById('staffReturnModal').style.display = 'flex';
            document.getElementById('staff_ret_items').innerHTML = '<tr><td colspan="3" style="padding:10px;text-align:center;color:#9ca3af;font-size:12px;">Memuat...</td></tr>';
            fetch(`/return-requests/${id}/detail`)
                .then(r => r.json())
                .then(d => {
                    document.getElementById('staff_ret_code').value = d.request_code ?? '-';
                    document.getElementById('staff_ret_lab').value = d.lab ?? '-';
                    document.getElementById('staff_ret_by').value = d.requested_by ?? '-';
                    const items = d.items || [];
                    document.getElementById('staff_ret_items').innerHTML = items.length
                        ? items.map(it => `<tr style="border-top:1px solid #e5e7eb;"><td style="padding:8px 14px;">${it.asset_name}</td><td style="padding:8px 14px;text-align:center;">${it.quantity}</td><td style="padding:8px 14px;text-align:center;">${it.quantity_approved ?? it.quantity}</td></tr>`).join('')
                        : '<tr><td colspan="3" style="padding:10px;text-align:center;color:#9ca3af;font-size:12px;">Tidak ada barang</td></tr>';
                })
                .catch(() => document.getElementById('staff_ret_items').innerHTML = '<tr><td colspan="3" style="padding:10px;text-align:center;color:#f87171;font-size:12px;">Gagal memuat data</td></tr>');
        }

        function openStaffTransferModal(id) {
            document.getElementById('staffTransferModal').style.display = 'flex';
            document.getElementById('staff_trf_items').innerHTML = '<tr><td colspan="3" style="padding:10px;text-align:center;color:#9ca3af;font-size:12px;">Memuat...</td></tr>';
            fetch(`/transfer-requests/${id}/detail`)
                .then(r => r.json())
                .then(d => {
                    document.getElementById('staff_trf_code').value = d.request_code ?? '-';
                    document.getElementById('staff_trf_from').value = d.from_lab ?? '-';
                    document.getElementById('staff_trf_to').value = d.to_lab ?? '-';
                    document.getElementById('staff_trf_by').value = d.requested_by ?? '-';
                    const items = d.items || [];
                    document.getElementById('staff_trf_items').innerHTML = items.length
                        ? items.map(it => `<tr style="border-top:1px solid #e5e7eb;"><td style="padding:8px 14px;">${it.asset_name}</td><td style="padding:8px 14px;text-align:center;">${it.quantity}</td><td style="padding:8px 14px;text-align:center;">${it.quantity_approved ?? it.quantity}</td></tr>`).join('')
                        : '<tr><td colspan="3" style="padding:10px;text-align:center;color:#9ca3af;font-size:12px;">Tidak ada barang</td></tr>';
                })
                .catch(() => document.getElementById('staff_trf_items').innerHTML = '<tr><td colspan="3" style="padding:10px;text-align:center;color:#f87171;font-size:12px;">Gagal memuat data</td></tr>');
        }

        ['staffLabModal', 'staffReturnModal', 'staffTransferModal'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.addEventListener('click', e => { if (e.target === el) el.style.display = 'none'; });
        });
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') ['staffLabModal', 'staffReturnModal', 'staffTransferModal'].forEach(closeStaffModal);
        });
    </script>
@endpush