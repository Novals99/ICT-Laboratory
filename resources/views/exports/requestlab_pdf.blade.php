<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #111827;
        }

        h2 {
            font-size: 16px;
            margin-bottom: 4px;
        }

        .generated {
            font-size: 10px;
            color: #6b7280;
            margin-bottom: 16px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        thead tr th {
            background-color: #1e1b4b;
            color: #ffffff;
            text-align: left;
            padding: 7px 8px;
            font-size: 11px;
            border: 1px solid #312e81;
        }

        td {
            padding: 6px 8px;
            border: 1px solid #e5e7eb;
            vertical-align: top;
        }

        /* Baris grup header (info request) */
        .row-request td {
            background-color: #eff6ff;
            font-weight: bold;
        }

        /* Baris sub-item (detail barang) */
        .row-item td {
            background-color: #ffffff;
        }

        .row-item td:first-child {
            padding-left: 20px;
            color: #374151;
        }

        /* Badge status request */
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
        }

        .badge-done       { background: #d1fae5; color: #065f46; }
        .badge-pending    { background: #fef3c7; color: #92400e; }
        .badge-partial    { background: #dbeafe; color: #1e40af; }
        .badge-rejected   { background: #fee2e2; color: #991b1b; }
        .badge-approved   { background: #d1fae5; color: #065f46; }

        /* kolom item-status */
        .item-approved { color: #059669; font-weight: bold; }
        .item-rejected { color: #dc2626; font-weight: bold; }
        .item-pending  { color: #d97706; font-weight: bold; }

        .no-items { color: #9ca3af; font-style: italic; }
    </style>
</head>

<body>
    <h2>{{ $title }}</h2>
    <p class="generated">Generated: {{ now()->format('d M Y, H:i') }}</p>

    <table>
        <thead>
            <tr>
                <th style="width:9%">ID Request</th>
                <th style="width:12%">Name</th>
                <th style="width:10%">Laboratory</th>
                <th style="width:10%">Date</th>
                <th style="width:12%">Status</th>
                <th style="width:25%">Asset Name</th>
                <th style="width:10%">Qty Requested</th>
                <th style="width:12%">Item Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($requests as $req)
                @if(count($req['items']) > 0)
                    @foreach($req['items'] as $i => $item)
                        @if($i === 0)
                        {{-- Baris pertama: tampilkan info request + item pertama --}}
                        <tr class="row-request">
                            <td>{{ $req['request_id'] }}</td>
                            <td>{{ $req['name'] }}</td>
                            <td>{{ $req['laboratory'] }}</td>
                            <td>{{ $req['date'] }}</td>
                            <td>
                                @php
                                    $statusLower = strtolower($req['status']);
                                    $badgeClass = match(true) {
                                        str_contains($statusLower, 'done')     => 'badge-done',
                                        str_contains($statusLower, 'partial')  => 'badge-partial',
                                        str_contains($statusLower, 'pending')  => 'badge-pending',
                                        str_contains($statusLower, 'rejected') => 'badge-rejected',
                                        default                                => 'badge-pending',
                                    };
                                @endphp
                                <span class="badge {{ $badgeClass }}">{{ $req['status'] }}</span>
                            </td>
                            <td>{{ $item['asset_name'] }}</td>
                            <td style="text-align:center">{{ $item['qty'] }}</td>
                            <td>
                                @php $cl = strtolower($item['item_status']); @endphp
                                <span class="item-{{ $cl }}">{{ $item['item_status'] }}</span>
                            </td>
                        </tr>
                        @else
                        {{-- Baris item selanjutnya: kolom request dikosongkan --}}
                        <tr class="row-item">
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>{{ $item['asset_name'] }}</td>
                            <td style="text-align:center">{{ $item['qty'] }}</td>
                            <td>
                                @php $cl = strtolower($item['item_status']); @endphp
                                <span class="item-{{ $cl }}">{{ $item['item_status'] }}</span>
                            </td>
                        </tr>
                        @endif
                    @endforeach
                @else
                    {{-- Request tanpa item --}}
                    <tr class="row-request">
                        <td>{{ $req['request_id'] }}</td>
                        <td>{{ $req['name'] }}</td>
                        <td>{{ $req['laboratory'] }}</td>
                        <td>{{ $req['date'] }}</td>
                        <td>
                            @php
                                $statusLower = strtolower($req['status']);
                                $badgeClass = match(true) {
                                    str_contains($statusLower, 'done')     => 'badge-done',
                                    str_contains($statusLower, 'partial')  => 'badge-partial',
                                    str_contains($statusLower, 'pending')  => 'badge-pending',
                                    str_contains($statusLower, 'rejected') => 'badge-rejected',
                                    default                                => 'badge-pending',
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{ $req['status'] }}</span>
                        </td>
                        <td colspan="3" class="no-items">No items requested</td>
                    </tr>
                @endif
            @empty
                <tr>
                    <td colspan="8" style="text-align:center; color:#9ca3af; padding: 16px;">No request data found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>

</html>
