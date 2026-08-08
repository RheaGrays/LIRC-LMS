<?php

namespace App\Http\Controllers;

use App\Models\Violation;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ViolationController extends Controller
{
    public function store(Request $request, $studentId)
    {
        $student = Student::findOrFail($studentId);
        $validated = $request->validate([
            'violation_type_id' => 'required|exists:violation_types,id',
            'notes' => 'nullable|string',
            'severity' => 'required|in:minor,moderate,severe',
            'date' => 'required|date',
        ]);

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
        $student = Student::with('violations.violationType')->findOrFail($studentId);
        $admin = Auth::guard('admin')->user();
        $violationTypes = \App\Models\ViolationType::all();
        return view('admin.violations.index', compact('student', 'admin', 'violationTypes'));
    }

    public function update(Request $request, $vid)
    {
        $violation = Violation::findOrFail($vid);
        $validated = $request->validate([
            'violation_type_id' => 'required|exists:violation_types,id',
            'notes' => 'nullable|string',
            'severity' => 'required|in:minor,moderate,severe',
            'date' => 'required|date',
        ]);

        $violation->update($validated);

        return back()->with('success', 'Violation updated successfully.');
    }
}
