<?php

namespace App\Http\Controllers;

use App\Models\Violation;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ViolationController extends Controller
{
    public function store(Request $request, int|string $studentId)
    {
        $student = Student::query()->findOrFail($studentId);
        $validated = $request->validate([
            'violation_type_id' => 'required|exists:violation_types,id',
            'notes' => 'nullable|string',
            'severity' => 'required|in:minor,moderate,severe',
            'date' => 'required|date',
        ]);

        $validated['student_id'] = $student->id;

        Violation::query()->create($validated);

        return back()->with('success', 'Violation recorded successfully.');
    }

    public function destroy(int|string $vid)
    {
        Violation::query()->findOrFail($vid)->delete(null);

        \Illuminate\Support\Facades\Log::info('Violation Deleted', [
            'admin_id' => \Illuminate\Support\Facades\Auth::guard('admin')->id(),
            'violation_id' => $vid,
        ]);

        return back()->with('success', 'Violation removed successfully.');
    }

    public function index(int|string $studentId)
    {
        $student = Student::query()->with('violations.violationType')->findOrFail($studentId);
        $admin = Auth::guard('admin')->user();
        $violationTypes = \App\Models\ViolationType::query()->get();
        return view('admin.violations.index', compact('student', 'admin', 'violationTypes'));
    }

    public function update(Request $request, int|string $vid)
    {
        $violation = Violation::query()->findOrFail($vid);
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
