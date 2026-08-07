<?php

namespace App\Http\Controllers;

use App\Models\AcademicDepartment;
use App\Models\AcademicProgram;
use App\Models\AcademicTerm;
use Illuminate\Http\Request;
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

        return redirect()->back()->with('success', 'Department updated successfully.');
    }

    // Web: Delete Department
    public function destroyDepartment($id)
    {
        AcademicDepartment::findOrFail($id)->delete();
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

        return redirect()->back()->with('success', 'Program updated successfully.');
    }

    // Web: Delete Program
    public function destroyProgram($id)
    {
        AcademicProgram::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Program deleted successfully.');
    }

    // API: Get departments and programs for dropdowns
    public function apiData()
    {
        return response()->json(AcademicDepartment::with('programs')->get());
    }
}
