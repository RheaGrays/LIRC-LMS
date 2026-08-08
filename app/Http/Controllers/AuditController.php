<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        $query = AttendanceLog::with('student')
            ->orderByDesc('logged_at');

        if ($request->filled('date')) {
            $date = $request->date;
            $query->whereDate('logged_at', '=', $date, 'and');
        }

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('student_id', 'LIKE', "%{$searchTerm}%")
                  ->orWhereHas('student', function($sq) use ($searchTerm) {
                      $sq->where('first_name', 'LIKE', "%{$searchTerm}%")
                         ->orWhere('last_name', 'LIKE', "%{$searchTerm}%");
                  });
            });
        }

        if ($request->filled('action')) {
            $query->where('action', '=', $request->action, 'and');
        }

        $logs = $query->paginate(10)->withQueryString();

        $stats = [
            'total_logs' => AttendanceLog::count('*'),
            'today_logs' => AttendanceLog::whereDate('logged_at', '=', \Carbon\Carbon::today(), 'and')->count('*'),
            'first_log' => AttendanceLog::orderBy('logged_at', 'asc')->first(),
        ];

        return view('admin.audit.index', compact('admin', 'logs', 'stats'));
    }
}
