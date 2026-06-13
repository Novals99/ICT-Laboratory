<!DOCTYPE html>
<html>
<head>
    <title>Lab Requests</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        h2 {
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table, th, td {
            border: 1px solid black;
        }

        th, td {
            padding: 8px;
            text-align: center;
        }
    </style>
</head>
<body>

    <h2>Lab Request List</h2>

    <table>
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
            @foreach($requests as $request)
                <tr>
                    <td>REQ-{{ str_pad($request->id, 3, '0', STR_PAD_LEFT) }}</td>
                    <td>{{ $request->user->name ?? '-' }}</td>
                    <td>{{ $request->request_items->sum('total_request') }}</td>
                    <td>{{ $request->request_date }}</td>
                    <td>{{ $request->request_status === 'partial' ? 'Partially Approved' : ucwords($request->request_status) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>
