<?php

namespace App\Http\Controllers;

use App\Models\AcademicDepartment;
use App\Models\AcademicProgram;
use App\Models\AcademicTerm;
use Illuminate\Http\Request;

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
        $request->validate([
            'level' => 'required|string',
            'name' => 'required|string|max:255',
        ]);
        AcademicDepartment::create($request->only('level', 'name'));
        return redirect()->back()->with('success', 'Department added successfully.');
    }

    // Web: Delete Department
    public function destroyDepartment($id)
    {
        AcademicDepartment::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Department deleted successfully.');
    }

    // Web: Update Department
    public function updateDepartment(Request $request, $id)
    {
        $request->validate([
            'level' => 'required|string',
            'name' => 'required|string|max:255'
        ]);
        AcademicDepartment::findOrFail($id)->update($request->only('level', 'name'));
        return redirect()->back()->with('success', 'Department updated successfully.');
    }

    // Web: Create Program
    public function storeProgram(Request $request)
    {
        $request->validate([
            'department_id' => 'required|exists:academic_departments,id',
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'years' => 'required|integer|min:1',
        ]);
        AcademicProgram::create($request->only('department_id', 'name', 'code', 'years'));
        return redirect()->back()->with('success', 'Program added successfully.');
    }

    // Web: Delete Program
    public function destroyProgram($id)
    {
        AcademicProgram::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Program deleted successfully.');
    }

    // Web: Update Program
    public function updateProgram(Request $request, $id)
    {
        $request->validate([
            'department_id' => 'required|exists:academic_departments,id',
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'years' => 'required|integer|min:1',
        ]);
        AcademicProgram::findOrFail($id)->update($request->only('department_id', 'name', 'code', 'years'));
        return redirect()->back()->with('success', 'Program updated successfully.');
    }

    // API: Get departments and programs for dropdowns
    public function apiData()
    {
        return response()->json(AcademicDepartment::with('programs')->get());
    }
}
