<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        // PERF-01 FIX: Use LEFT JOINs instead of orWhereHas() for department/program search.
        // orWhereHas() generates a correlated EXISTS subquery per matched row, which is O(n) slow.
        // A single JOIN lets the DB engine use indexes and scan once.
        $query = Student::withCount('violations')
            ->with(['violations.violationType', 'academicDepartment', 'academicProgram'])
            ->leftJoin('academic_departments', 'students.department_id', '=', 'academic_departments.id')
            ->leftJoin('academic_programs',    'students.program_id',    '=', 'academic_programs.id')
            ->select('students.*'); // prevent JOIN columns from shadowing student columns

        // Search ID, Name, Department/Program, Patron Category
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('students.id',        'like', "%{$search}%")
                  ->orWhere('students.last_name',  'like', "%{$search}%")
                  ->orWhere('students.first_name',  'like', "%{$search}%")
                  ->orWhere('students.patron_category', 'like', "%{$search}%")
                  // PERF-01 FIX: Direct column references on already-joined tables
                  ->orWhere('academic_departments.name', 'like', "%{$search}%")
                  ->orWhere('academic_programs.name',    'like', "%{$search}%")
                  ->orWhere('academic_programs.code',    'like', "%{$search}%");
            });
        }

        // Filter by Patron Category
        if ($request->filled('category')) {
            $query->where('patron_category', $request->category);
        }

        // Filter by Department
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        // Filter by Program
        if ($request->filled('program_id')) {
            $query->where('program_id', $request->program_id);
        }

        // Filter by Year Level
        if ($request->filled('year_level')) {
            $query->where('year_level', $request->year_level);
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'last_name');
        $sortDir = strtolower($request->input('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        $sortFieldsMap = [
            'name'            => ['last_name', 'first_name'],
            'last_name'       => ['last_name', 'first_name'],
            'id'              => ['id'],
            'department_id'   => ['department_id', 'last_name'],
            'year_level'      => ['year_level', 'last_name'],
            'patron_category' => ['patron_category', 'last_name'],
            'violations_count'=> ['violations_count'],
        ];

        if (isset($sortFieldsMap[$sortBy])) {
            foreach ($sortFieldsMap[$sortBy] as $col) {
                $query->orderBy($col, $sortDir);
            }
        } else {
            $query->orderBy('last_name', 'asc')->orderBy('first_name', 'asc');
        }

        $students = $query->paginate(20)->withQueryString();
        $patronCategories = SystemSetting::get('patron_categories', ['Student', 'Employee', 'Post Graduate', 'Alumni', 'Visitor']);

        // PERF-NEW-03 FIX: Cache departments and programs — they change rarely
        // but are loaded on every students page request (filtering, sorting, pagination).
        $departmentsList = \Illuminate\Support\Facades\Cache::remember('academic_departments_all', 600, function () {
            return \App\Models\AcademicDepartment::query()->orderBy('name', 'asc')->get();
        });
        $programsList = \Illuminate\Support\Facades\Cache::remember('academic_programs_all', 600, function () {
            return \App\Models\AcademicProgram::query()->orderBy('name', 'asc')->get();
        });
        $yearLevelsList = \Illuminate\Support\Facades\Cache::remember('student_year_levels', 300, function () {
            return Student::query()->select('year_level')->whereNotNull('year_level')->where('year_level', '!=', '')->distinct()->pluck('year_level')->sort()->values();
        });

        return view('admin.students.index', compact('students', 'patronCategories', 'departmentsList', 'programsList', 'yearLevelsList'));
    }

    public function store(Request $request)
    {
        // Sanitize barcode scanner input: replace slash with hyphen
        if ($request->has('id')) {
            $request->merge(['id' => str_replace('/', '-', trim($request->input('id')))]);
        }

        $validated = $request->validate([
            'id'              => 'required|string|max:50|unique:students,id',
            'first_name'      => 'required|string|max:255',
            'last_name'       => 'required|string|max:255',
            'middle_name'     => 'nullable|string|max:255',
            'patron_category' => 'required|string|max:100',
            'department_id'   => 'nullable|exists:academic_departments,id',
            'program_id'      => 'nullable|exists:academic_programs,id',
            'year_level'      => 'nullable|string|max:50',
            'email'           => 'nullable|email|max:255',
        ]);

        Student::create($validated);
        return back()->with('success', 'Patron created successfully.');
    }

    public function update(Request $request, string $id)
    {
        $student = Student::findOrFail($id);
        $validated = $request->validate([
            'first_name'      => 'required|string|max:255',
            'last_name'       => 'required|string|max:255',
            'middle_name'     => 'nullable|string|max:255',
            'patron_category' => 'required|string|max:100',
            'department_id'   => 'nullable|exists:academic_departments,id',
            'program_id'      => 'nullable|exists:academic_programs,id',
            'year_level'      => 'nullable|string|max:50',
            'email'           => 'nullable|email|max:255',
        ]);

        $student->update($validated);
        return back()->with('success', 'Patron updated successfully.');
    }

    public function destroy(string $id)
    {
        Student::findOrFail($id)->delete();
        return back()->with('success', 'Patron deleted successfully.');
    }

    public function edit(string $id)
    {
        $student = Student::findOrFail($id);
        $patronCategories = SystemSetting::get('patron_categories', ['Student', 'Employee', 'Post Graduate', 'Alumni', 'Visitor']);
        return view('admin.students.edit', compact('student', 'patronCategories'));
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv,txt',
        ]);

        $file = $request->file('file');
        $spreadsheet = IOFactory::load($file->getPathname());
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();
        
        $totalDataRows = count($rows) - 1; // Exclude header row
        $count = 0;
        
        \Illuminate\Support\Facades\DB::transaction(function () use ($rows, &$count) {
            // Process ALL data rows (skip header row at index 0).
            // Previously array_slice($rows, 1, 5000) silently truncated beyond 5K rows.
            foreach (array_slice($rows, 1) as $row) {
                $id = $row[0] ?? null;
                if (!$id) continue;

                $deptName = trim($row[5] ?? '');
                $progName = trim($row[6] ?? '');

                $dept = null;
                if ($deptName !== '') {
                    $dept = \App\Models\AcademicDepartment::firstOrCreate(
                        ['name' => $deptName],
                        ['level' => 'college']
                    );
                }

                $prog = null;
                if ($progName !== '') {
                    $prog = \App\Models\AcademicProgram::firstOrCreate(
                        ['name' => $progName, 'department_id' => $dept?->id]
                    );
                }

                Student::updateOrCreate(
                    ['id' => $id],
                    [
                        'last_name'       => $row[1] ?? '',
                        'first_name'      => $row[2] ?? '',
                        'middle_name'     => $row[3] ?? null,
                        'patron_category' => $row[4] ?? 'Student',
                        'department_id'   => $dept?->id,
                        'program_id'      => $prog?->id,
                        'year_level'      => $row[7] ?? '',
                        'email'           => $row[8] ?? null,
                    ]
                );
                $count++;
            }
        });

        $skipped = $totalDataRows - $count;
        $message = "Successfully imported {$count} patrons.";
        if ($skipped > 0) {
            $message .= " ({$skipped} rows skipped due to missing ID.)";
        }

        return back()->with('success', $message);
    }

    public function export()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = ['ID', 'Last Name', 'First Name', 'Middle Name', 'Patron Category', 'Department', 'Program', 'Year Level', 'Email', 'Status'];
        $sheet->fromArray($headers, null, 'A1');

        $row = 2;
        Student::with(['academicDepartment', 'academicProgram'])->chunk(500, function ($students) use ($sheet, &$row) {
            foreach ($students as $student) {
                $sheet->fromArray([
                    $student->id,
                    $student->last_name,
                    $student->first_name,
                    $student->middle_name,
                    $student->patron_category,
                    $student->academicDepartment?->name,
                    $student->academicProgram?->name,
                    $student->year_level,
                    $student->email,
                    $student->status,
                ], null, 'A' . $row);
                $row++;
            }
        });

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');

        return response()->streamDownload(function() use ($writer) {
            $writer->save('php://output');
        }, 'patrons.xlsx');
    }
}
