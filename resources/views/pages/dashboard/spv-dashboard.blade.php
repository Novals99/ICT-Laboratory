@extends('panel.content')

@section('title', auth()->user()->role === 'spv inventory' ? 'SPV Dashboard' : 'Staff Dashboard')

@section('content')

    <div class="db-wrap">

        {{-- 3 card + card stok --}}
        <div class="db-top-row">

            {{-- stat card --}}
            <div class="db-stats-col">
                <div class="db-cards-row">

                    {{-- user --}}
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

                    {{-- lab --}}
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

                    {{-- lab request --}}
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

                </div>

                {{-- bar chart: active & inactve pc per lab --}}
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

            {{-- low stok card --}}
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

        {{-- tabel recent lab request --}}
        <div class="db-card db-table-card">
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
                                <td class="td-mono">{{ $req->id }}</td>
                                <td>{{ $req->user->name ?? '-' }}</td>
                                <td>{{ $req->total_request ?? $req->items_count ?? '-' }}</td>
                                <td>{{ \Carbon\Carbon::parse($req->created_at)->format('d-m-y') }}</td>
                                <td>
                                    @php
                                        $status = strtolower($req->status ?? 'pending');
                                        $badgeClass = match ($status) {
                                            'approved' => 'badge-approved',
                                            'rejected' => 'badge-rejected',
                                            default => 'badge-pending',
                                        };
                                    @endphp
                                    <span class="status-badge {{ $badgeClass }}">
                                        {{ ucfirst($req->status ?? 'Pending') }}
                                    </span>
                                </td>
                                <td>
                                    <div class="action-btns">
                                        <a href="{{ route('requestlab.edit', $req->id) }}" class="action-btn action-edit"
                                            title="Edit">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                                stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                                            </svg>
                                        </a>
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

    </div>

@endsection

@push('styles')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
@endpush

@push('scripts')
    {{-- Chart.js CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        // ── Data dari PHP ──────────────────────────────────────────
        const chartData = @json($chartData);

        const labels = chartData.map(d => d.label);
        const active = chartData.map(d => d.active);
        const inactive = chartData.map(d => d.inactive);

        // ── Render chart ───────────────────────────────────────────
        const ctx = document.getElementById('labConditionsChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels,
                datasets: [
                    {
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
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => ` ${ctx.dataset.label}: ${ctx.parsed.y}`
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 11 }, color: '#9ca3af' },
                        border: { display: false },
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f3f4f6' },
                        ticks: {
                            font: { size: 11 }, color: '#9ca3af',
                            stepSize: 8,
                        },
                        border: { display: false },
                    },
                },
            },
        });

        // ── Select all checkbox ────────────────────────────────────
        function toggleAll(master) {
            document.querySelectorAll('.row-check')
                .forEach(cb => cb.checked = master.checked);
        }
    </script>
@endpush
