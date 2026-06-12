<!DOCTYPE html>
<html>

<head>
       <meta charset="utf-8">
       <style>
              body {
                     font-family: Arial, sans-serif;
                     font-size: 12px;
              }

              h2 {
                     margin-bottom: 12px;
              }

              table {
                     width: 100%;
                     border-collapse: collapse;
              }

              th {
                     background-color: #f3f4f6;
                     text-align: left;
                     padding: 8px;
                     border: 1px solid #d1d5db;
              }

              td {
                     padding: 7px 8px;
                     border: 1px solid #e5e7eb;
              }

              tr:nth-child(even) td {
                     background-color: #f9fafb;
              }
       </style>
</head>

<body>
       <h2>{{ $title }}</h2>
       <p>Generated: {{ now()->format('d M Y, H:i') }}</p>

       <table>
              <thead>
                     <tr>
                            @foreach ($headings as $heading)
                                   <th>{{ $heading }}</th>
                            @endforeach
                     </tr>
              </thead>
              <tbody>
                     @foreach ($data as $row)
                            <tr>
                                   @foreach ($row as $value)
                                          <td>{{ $value }}</td>
                                   @endforeach
                            </tr>
                     @endforeach
              </tbody>
       </table>
</body>

</html>