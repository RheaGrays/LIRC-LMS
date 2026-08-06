<?php

namespace App\Http\Controllers;

use App\Models\Violation;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class ViolationController extends Controller
{
    public function store(Request $request, $studentId)
    {
        $student = Student::findOrFail($studentId);
        $validated = $request->validate([
            'type' => 'required|string',
            'notes' => 'nullable|string',
            'severity' => 'required|in:minor,moderate,severe',
            'date' => 'required|date',
        ]);

        $validated['id'] = Str::uuid();
        $validated['student_id'] = $student->id;

        Violation::create($validated);

        return back()->with('success', 'Violation recorded successfully.');
    }

    public function destroy($vid)
    {
        Violation::findOrFail($vid)->delete();

        \Illuminate\Support\Facades\Log::info('Violation Deleted', [
            'admin_id' => \Illuminate\Support\Facades\Auth::guard('admin')->id(),
            'violation_id' => $vid,
        ]);

        return back()->with('success', 'Violation removed successfully.');
    }

    public function index($studentId)
    {
        $student = Student::with('violations')->findOrFail($studentId);
        $admin = Auth::guard('admin')->user();
        return view('admin.violations.index', compact('student', 'admin'));
    }

    public function update(Request $request, $vid)
    {
        $violation = Violation::findOrFail($vid);
        $validated = $request->validate([
            'type' => 'required|string',
            'notes' => 'nullable|string',
            'severity' => 'required|in:minor,moderate,severe',
            'date' => 'required|date',
        ]);

        $violation->update($validated);

        return back()->with('success', 'Violation updated successfully.');
    }
}
