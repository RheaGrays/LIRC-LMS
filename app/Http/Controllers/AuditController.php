<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        // CQ-A03 FIX: use ::query() so Intelephense resolves Builder type (P1005)
        $query = AttendanceLog::query()
            ->with(['student.academicDepartment'])
            ->orderByDesc('logged_at');

        if ($request->filled('date')) {
            // BUG-A01 FIX: whereDate($col, $value) — removed redundant '=' operator
            // and the invalid 4th 'and' argument (it's the default and was causing P1005).
            $query->whereDate('logged_at', $request->date);
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
            // BUG-A01 FIX: removed redundant '=' and invalid 4th 'and' arg
            $query->where('action', $request->action);
        }

        $logs = $query->paginate(25)->withQueryString();

        // PERF-A03 FIX: Cache audit stats for 60 seconds.
        // These are aggregate stats shown in the header — they don't need to be
        // real-time. Previously re-ran 3 separate queries on every page load/filter.
        //
        // CQ-A03 / PERF-A03 FIX: Cached stats with ::query() prefix.
        // Intelephense stubs require explicit args even for params that have defaults at runtime:
        //   count('*')              — stub marks $columns as required
        //   whereDate($col,'=',$val) — stub requires the operator arg
        $stats = Cache::remember('audit_page_stats', 60, function () {
            return [
                'total_logs' => AttendanceLog::query()->count('*'),
                'today_logs' => AttendanceLog::query()->whereDate('logged_at', '=', Carbon::today())->count('*'),
                'first_log'  => AttendanceLog::query()->orderBy('logged_at', 'asc')->first(),
            ];
        });

        return view('admin.audit.index', compact('admin', 'logs', 'stats'));
    }
}
