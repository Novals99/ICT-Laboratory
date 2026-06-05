@extends('panel.content')

@section('title', 'Lab Dashboard')

@section('content')

<div class="db-wrap">

    {{-- ── STAT CARDS ── --}}
    <div class="db-cards-row" style="display:grid; grid-template-columns:repeat(3,1fr); gap:16px;">

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
                            <td>{{ ucfirst(str_replace(['spv inventory','pic','admin','assistant'], ['SPV Inventory','PIC','Admin','Assistant'], $staff->role)) }}</td>
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
                                {{-- view --}}
                                <a href="#" class="action-btn" title="View" style="color:#6b7280;">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                         stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                </a>
                                {{-- edit --}}
                                <a href="#" class="action-btn action-edit" title="Edit">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                         stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                    </svg>
                                </a>
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

</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    const chartData = @json($chartData);
    const labels    = chartData.map(d => d.label);
    const active    = chartData.map(d => d.active);
    const inactive  = chartData.map(d => d.inactive);

    new Chart(document.getElementById('labConditionsChart').getContext('2d'), {
        type: 'bar',
        data: {
            labels,
            datasets: [
                { label: 'Active',   data: active,   backgroundColor: '#111B4C', borderRadius: 4, borderSkipped: false },
                { label: 'Inactive', data: inactive,  backgroundColor: '#98083D', borderRadius: 4, borderSkipped: false },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 11 }, color: '#9ca3af' }, border: { display: false } },
                y: { beginAtZero: true, grid: { color: '#f3f4f6' }, ticks: { font: { size: 11 }, color: '#9ca3af', stepSize: 1 }, border: { display: false } },
            },
        },
    });
</script>
@endpush
