<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">

    <style>
        body {
            font-family: sans-serif;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid black;
            padding: 8px;
        }

        th {
            background: #eeeeee;
        }
    </style>
</head>
<body>

<h2>Activity Log Report</h2>

<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Date</th>
            <th>User</th>
            <th>Role</th>
            <th>Description</th>
        </tr>
    </thead>

    <tbody>
        @foreach ($logs as $index => $log)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $log->created_at->format('d-m-Y H:i') }}</td>
                <td>{{ $log->user?->name ?? '-' }}</td>
                <td>{{ $log->user?->role ?? '-' }}</td>
                <td>{{ $log->activity }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

</body>
</html>