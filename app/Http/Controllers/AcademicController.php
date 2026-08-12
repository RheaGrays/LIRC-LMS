<?php

namespace App\Http\Controllers;

use App\Models\AcademicDepartment;
use App\Models\AcademicProgram;
use App\Models\AcademicTerm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class AcademicController extends Controller
{
    // Web: View the Academic Setup page
    public function index()
    {
        $departments = AcademicDepartment::with('programs')->get();
        return view('admin.academics.index', compact('departments'));
    }

    // Web: Create Department
    public function storeDepartment(Request $request)
    {
        $name = trim($request->name);
        $request->validate([
            'level' => 'required|string',
            'name'  => [
                'required', 'string', 'max:255',
                Rule::unique('academic_departments', 'name'),
            ],
        ], [
            'name.unique' => "Department \"{$name}\" already exists.",
        ]);

        AcademicDepartment::create([
            'level' => $request->level,
            'name'  => $name,
        ]);

        // BUG-A05 FIX: Bust StudentController's cached departments list so the
        // filter dropdown reflects new/updated/deleted departments immediately.
        Cache::forget('academic_departments_all');
        Cache::forget('academic_programs_all');

        return redirect()->back()->with('success', 'Department added successfully.');
    }

    // Web: Update Department
    public function updateDepartment(Request $request, $id)
    {
        $name = trim($request->name);
        $request->validate([
            'level' => 'required|string',
            'name'  => [
                'required', 'string', 'max:255',
                Rule::unique('academic_departments', 'name')->ignore($id),
            ],
        ], [
            'name.unique' => "Department \"{$name}\" already exists.",
        ]);

        AcademicDepartment::findOrFail($id)->update([
            'level' => $request->level,
            'name'  => $name,
        ]);

        Cache::forget('academic_departments_all');
        Cache::forget('academic_programs_all');

        return redirect()->back()->with('success', 'Department updated successfully.');
    }

    // Web: Delete Department
    public function destroyDepartment($id)
    {
        AcademicDepartment::findOrFail($id)->delete();

        Cache::forget('academic_departments_all');
        Cache::forget('academic_programs_all');

        return redirect()->back()->with('success', 'Department deleted successfully.');
    }

    // Web: Create Program
    public function storeProgram(Request $request)
    {
        $name = trim($request->name);
        $request->validate([
            'department_id' => 'required|exists:academic_departments,id',
            'name'          => [
                'required', 'string', 'max:255',
                Rule::unique('academic_programs', 'name'),
            ],
            'code'  => 'nullable|string|max:50',
            'years' => 'required|integer|min:1',
        ], [
            'name.unique' => "Program \"{$name}\" already exists.",
        ]);

        AcademicProgram::create([
            'department_id' => $request->department_id,
            'name'          => $name,
            'code'          => $request->code,
            'years'         => $request->years,
        ]);

        Cache::forget('academic_departments_all');
        Cache::forget('academic_programs_all');

        return redirect()->back()->with('success', 'Program added successfully.');
    }

    // Web: Update Program
    public function updateProgram(Request $request, $id)
    {
        $name = trim($request->name);
        $request->validate([
            'department_id' => 'required|exists:academic_departments,id',
            'name'          => [
                'required', 'string', 'max:255',
                Rule::unique('academic_programs', 'name')->ignore($id),
            ],
            'code'  => 'nullable|string|max:50',
            'years' => 'required|integer|min:1',
        ], [
            'name.unique' => "Program \"{$name}\" already exists.",
        ]);

        AcademicProgram::findOrFail($id)->update([
            'department_id' => $request->department_id,
            'name'          => $name,
            'code'          => $request->code,
            'years'         => $request->years,
        ]);

        Cache::forget('academic_departments_all');
        Cache::forget('academic_programs_all');

        return redirect()->back()->with('success', 'Program updated successfully.');
    }

    // Web: Delete Program
    public function destroyProgram($id)
    {
        AcademicProgram::findOrFail($id)->delete();

        Cache::forget('academic_departments_all');
        Cache::forget('academic_programs_all');

        return redirect()->back()->with('success', 'Program deleted successfully.');
    }

    /**
     * API: Get departments and programs for dropdowns (used by Registration form).
     *
     * SEC-A01 FIX: Scope select() to only the fields the UI actually needs.
     * Previously returned full model data including created_at, updated_at, etc.
     */
    public function apiData()
    {
        return response()->json(
            AcademicDepartment::query()
                ->with(['programs:id,department_id,name,code,years'])
                ->get(['id', 'name', 'level'])
        );
    }
}
