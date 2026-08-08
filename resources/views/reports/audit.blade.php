<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Audit Log - {{ $date }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #0f2744;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            color: #0f2744;
            font-size: 20px;
        }
        .header p {
            margin: 5px 0 0 0;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f3f4f6;
            color: #111827;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-checkin {
            background-color: #d1fae5;
            color: #065f46;
        }
        .badge-checkout {
            background-color: #fee2e2;
            color: #991b1b;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Library Entrance Monitoring System (LEMS)</h1>
        <p>Daily Attendance & Audit Log Report - {{ \Carbon\Carbon::parse($date)->format('F d, Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Time</th>
                <th>Student ID</th>
                <th>Student Name</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($log->logged_at)->format('h:i:s A') }}</td>
                    <td>{{ $log->student_id }}</td>
                    <td>{{ $log->student?->full_name ?? '—' }}</td>
                    <td>
                        <span class="badge {{ $log->action === 'check_in' ? 'badge-checkin' : 'badge-checkout' }}">
                            {{ str_replace('_', ' ', $log->action) }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align: center; color: #666;">No attendance records found for this date.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
