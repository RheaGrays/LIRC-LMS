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
            $query->whereDate('logged_at', $date);
        }

        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        $logs = $query->paginate(50)->withQueryString();

        return view('admin.audit.index', compact('admin', 'logs'));
    }
}
