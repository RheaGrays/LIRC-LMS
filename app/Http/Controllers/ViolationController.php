<?php

namespace App\Http\Controllers;

use App\Models\Violation;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
        return back()->with('success', 'Violation removed successfully.');
    }
}
