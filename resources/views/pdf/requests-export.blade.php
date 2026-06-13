<!DOCTYPE html>
<html>
<head>
    <title>Request Lab Export</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        h2 { text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #333; padding: 6px; text-align: left; }
        th { background: #f3f4f6; font-weight: bold; }
        .status { font-weight: bold; text-transform: capitalize; }
        .pending { color: #ca8a04; }
        .approved { color: #16a34a; }
        .rejected { color: #dc2626; }
        .partially { color: #2563eb; }
        .done { color: #374151; }
    </style>
</head>
<body>
    <h2>Request Lab Report</h2>
    <p style="text-align:center; color:#666;">Generated: {{ now()->format('d-m-Y H:i') }}</p>

    <table>
        <thead>
            <tr>
                <th>Request ID</th>
                <th>Requester</th>
                <th>Laboratory</th>
                <th>Date</th>
                <th>Asset Name</th>
                <th>Category</th>
                <th>Qty Req</th>
                <th>Qty Appr</th>
                <th>Qty Rej</th>
                <th>Item Status</th>
                <th>Req Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($requests as $req)
                @foreach($req->items as $item)
                    <tr>
                        <td>{{ $req->request_id }}</td>
                        <td>{{ $req->user?->name ?? '-' }}</td>
                        <td>{{ $req->lab?->lab_name ?? '-' }}</td>
                        <td>{{ $req->request_date?->format('d-m-Y') }}</td>
                        <td>{{ $item->asset_name ?? $item->asset?->asset_name ?? '-' }}</td>
                        <td>{{ $item->category }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ $item->approved_qty }}</td>
                        <td>{{ $item->rejected_qty }}</td>
                        <td>{{ ucfirst($item->status) }}</td>
                        @if($loop->first)
                            <td rowspan="{{ $req->items->count() }}" style="vertical-align:middle; text-align:center;">
                                <span class="status {{ str_replace(' ', '-', strtolower($req->request_status)) }}">
                                    {{ ucfirst($req->request_status) }}
                                </span>
                            </td>
                        @endif
                    </tr>
                @endforeach
            @empty
                <tr>
                    <td colspan="11" style="text-align:center;">No data available</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
