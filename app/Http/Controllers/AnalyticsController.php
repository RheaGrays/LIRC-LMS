<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function index()
    {
        return view('admin.analytics.index');
    }

    /** GET /admin/analytics/data?type=hourly&date=YYYY-MM-DD */
    public function data(Request $request): JsonResponse
    {
        $type = $request->input('type', 'daily');

        return match ($type) {
            'hourly'  => response()->json($this->hourly($request->input('date', today()->toDateString()))),
            'weekly'  => response()->json($this->weekly()),
            'monthly' => response()->json($this->monthly()),
            default   => response()->json(['error' => 'Invalid type'], 422),
        };
    }

    private function hourly(string $date): array
    {
        $from = "{$date} 00:00:00";
        $to   = "{$date} 23:59:59";

        $rows = AttendanceLog::selectRaw('HOUR(logged_at) as h, COUNT(*) as cnt')
            ->where('action', 'check_in')
            ->whereBetween('logged_at', [$from, $to])
            ->groupBy('h')
            ->pluck('cnt', 'h')
            ->toArray();

        $labels = [];
        $data   = [];
        for ($h = 6; $h <= 22; $h++) {
            $labels[] = $h < 12 ? "{$h}AM" : ($h === 12 ? "12PM" : ($h - 12) . "PM");
            $data[]   = $rows[$h] ?? 0;
        }

        return compact('labels', 'data');
    }

    private function weekly(): array
    {
        $now    = now();
        $dow    = $now->dayOfWeek; // 0=Sun
        $monday = $now->copy()->subDays($dow === 0 ? 6 : $dow - 1)->startOfDay();

        $rows = AttendanceLog::selectRaw('DAYOFWEEK(logged_at) as dow, COUNT(*) as cnt')
            ->where('action', 'check_in')
            ->where('logged_at', '>=', $monday)
            ->groupBy('dow')
            ->pluck('cnt', 'dow')
            ->toArray();

        // MySQL: 1=Sun,2=Mon,...,7=Sat  →  remap to Mon–Sun
        $dayMap = [2 => 'Mon', 3 => 'Tue', 4 => 'Wed', 5 => 'Thu', 6 => 'Fri', 7 => 'Sat', 1 => 'Sun'];
        $labels = array_values($dayMap);
        $data   = array_map(fn($k) => $rows[$k] ?? 0, array_keys($dayMap));

        return compact('labels', 'data');
    }

    private function monthly(): array
    {
        $start = now()->startOfMonth();
        $end   = now()->endOfMonth();

        $rows = AttendanceLog::selectRaw('DAY(logged_at) as d, COUNT(*) as cnt')
            ->where('action', 'check_in')
            ->whereBetween('logged_at', [$start, $end])
            ->groupBy('d')
            ->pluck('cnt', 'd')
            ->toArray();

        $labels = [];
        $data   = [];
        for ($w = 1; $w <= 5; $w++) {
            $labels[] = "Week {$w}";
            $weekTotal = 0;
            for ($d = ($w - 1) * 7 + 1; $d <= $w * 7; $d++) {
                $weekTotal += $rows[$d] ?? 0;
            }
            $data[] = $weekTotal;
        }

        return compact('labels', 'data');
    }
}
