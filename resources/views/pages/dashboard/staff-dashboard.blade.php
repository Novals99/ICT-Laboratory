@extends('panel.content')

@section('title', 'Dashboard')

@section('content')
    <div class="db-wrap">

        <div class="db-cards-row">
            <div class="db-stat-card">
                <div class="stat-info">
                    <span class="stat-label">Laboratory</span>
                    <span class="stat-value">{{ number_format($totalLaboratory) }}</span>
                </div>
            </div>

            <div class="db-stat-card">
                <div class="stat-info">
                    <span class="stat-label">PC Active</span>
                    <span class="stat-value">{{ number_format($totalPcActive) }}</span>
                </div>
            </div>

            <div class="db-stat-card">
                <div class="stat-info">
                    <span class="stat-label">PC Inactive</span>
                    <span class="stat-value">{{ number_format($totalPcInactive) }}</span>
                </div>
            </div>

            <div class="db-stat-card">
                <div class="stat-info">
                    <span class="stat-label">Lab Request</span>
                    <span class="stat-value">{{ number_format($totalRequestLab) }}</span>
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

        <div class="db-card db-table-card">
            <div class="table-wrap">
                <table class="db-table">
                    <thead>
                        <tr>
                            <th>ID Request</th>
                            <th>Name</th>
                            <th>Total Request</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($recentRequests as $req)
                            <tr>
                                <td class="td-mono">{{ $req->id }}</td>
                                <td>{{ $req->user->name ?? '-' }}</td>
                                <td>{{ $req->total_requested_items ?? 0 }}</td>
                                <td>{{ optional($req->created_at)->format('d-m-y') ?? '-' }}</td>
                                <td>
                                    @php
                                        $status = strtolower($req->request_status ?? 'pending');

                                        $badgeClass = match ($status) {
                                            'approved' => 'badge-approved',
                                            'rejected' => 'badge-rejected',
                                            default => 'badge-pending',
                                        };
                                    @endphp

                                    <span class="status-badge {{ $badgeClass }}">
                                        {{ ucfirst($req->request_status ?? 'Pending') }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="empty-state" style="text-align:center; padding:32px">
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

        const labels = chartData.map(d => d.label);
        const active = chartData.map(d => d.active);
        const inactive = chartData.map(d => d.inactive);

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
    </script>
@endpush
