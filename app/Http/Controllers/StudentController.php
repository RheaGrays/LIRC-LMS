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
        $query = Student::query()
            ->select('students.*') // prevent JOIN columns from shadowing student columns
            ->withCount('violations')
            ->with(['violations.violationType', 'academicDepartment', 'academicProgram'])
            ->leftJoin('academic_departments', 'students.department_id', '=', 'academic_departments.id', 'left', false)
            ->leftJoin('academic_programs',    'students.program_id',    '=', 'academic_programs.id', 'left', false);

        // Search ID, Name, Department/Program, Patron Category, Year Level
        if ($request->filled('search')) {
            $search = trim($request->search);
            $driver = \Illuminate\Support\Facades\DB::connection()->getDriverName();
            
            $fullNameExpr = $driver === 'sqlite' 
                ? "students.first_name || ' ' || COALESCE(students.middle_name, '') || ' ' || students.last_name"
                : "CONCAT(students.first_name, ' ', COALESCE(students.middle_name, ''), ' ', students.last_name)";

            $reversedNameExpr = $driver === 'sqlite'
                ? "students.last_name || ' ' || students.first_name"
                : "CONCAT(students.last_name, ' ', students.first_name)";

            $query->where(function($q) use ($search, $fullNameExpr, $reversedNameExpr) {
                $q->where('students.id',               'like', "%{$search}%")
                  ->orWhere('students.last_name',       'like', "%{$search}%")
                  ->orWhere('students.first_name',      'like', "%{$search}%")
                  ->orWhere('students.middle_name',     'like', "%{$search}%")
                  ->orWhere('students.patron_category', 'like', "%{$search}%")
                  ->orWhere('students.year_level',      'like', "%{$search}%")
                  ->orWhere('academic_departments.name','like', "%{$search}%")
                  ->orWhere('academic_programs.name',   'like', "%{$search}%")
                  ->orWhere('academic_programs.code',   'like', "%{$search}%")
                  ->orWhereRaw("{$fullNameExpr} LIKE ?", ["%{$search}%"])
                  ->orWhereRaw("{$reversedNameExpr} LIKE ?", ["%{$search}%"]);
            });
        }

        // Filter by Patron Category
        if ($request->filled('category')) {
            $query->where('students.patron_category', $request->category);
        }

        // Filter by Department
        if ($request->filled('department_id')) {
            $query->where('students.department_id', $request->department_id);
        }

        // Filter by Program
        if ($request->filled('program_id')) {
            $query->where('students.program_id', $request->program_id);
        }

        // Filter by Year Level
        if ($request->filled('year_level')) {
            $query->where('students.year_level', $request->year_level);
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'last_name');
        $sortDir = strtolower($request->input('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        $sortFieldsMap = [
            'name'            => ['students.last_name', 'students.first_name'],
            'last_name'       => ['students.last_name', 'students.first_name'],
            'id'              => ['students.id'],
            'department_id'   => ['students.department_id', 'students.last_name'],
            'year_level'      => ['students.year_level', 'students.last_name'],
            'patron_category' => ['students.patron_category', 'students.last_name'],
            'violations_count'=> ['violations_count'],
        ];

        if (isset($sortFieldsMap[$sortBy])) {
            foreach ($sortFieldsMap[$sortBy] as $col) {
                $query->orderBy($col, $sortDir);
            }
        } else {
            $query->orderBy('students.last_name', 'asc')->orderBy('students.first_name', 'asc');
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
        
        $violationTypes = \App\Models\ViolationType::query()->orderBy('name', 'asc')->get();

        return view('admin.students.index', compact('students', 'patronCategories', 'departmentsList', 'programsList', 'yearLevelsList', 'violationTypes'));
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

        Student::query()->create($validated);
        return back()->with('success', 'Patron created successfully.');
    }

    public function update(Request $request, string $id)
    {
        $student = Student::query()->findOrFail($id);
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
        Student::query()->findOrFail($id)->delete(null);
        return back()->with('success', 'Patron deleted successfully.');
    }

    public function edit(string $id)
    {
        $student = Student::query()->findOrFail($id);
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
        $unmatchedDepts = [];
        $unmatchedProgs = [];
        
        // Pre-load all departments and programs for fast matching
        $allDepts = \App\Models\AcademicDepartment::query()->get();
        $allProgs = \App\Models\AcademicProgram::query()->get();

        \Illuminate\Support\Facades\DB::transaction(function () use ($rows, &$count, &$unmatchedDepts, &$unmatchedProgs, $allDepts, $allProgs) {
            // Process ALL data rows (skip header row at index 0).
            foreach (array_slice($rows, 1) as $row) {
                $id = $row[0] ?? null;
                if (!$id) continue;

                $deptName = trim($row[5] ?? '');
                $progName = trim($row[6] ?? '');

                // Department Matching (SSOT Reference - No Auto-Creation)
                $dept = null;
                if ($deptName !== '') {
                    $deptLower = mb_strtolower($deptName);
                    
                    // 1. Exact match
                    $dept = $allDepts->firstWhere('name', $deptName);
                    
                    // 2. Case-insensitive match
                    if (!$dept) {
                        $dept = $allDepts->first(fn($d) => mb_strtolower(trim($d->name)) === $deptLower);
                    }
                    
                    // 3. Match code/abbreviation (e.g. "CCIS", "CABE", "CEAS", "CHS", "COE")
                    if (!$dept) {
                        $dept = $allDepts->first(fn($d) => !empty($d->code) && mb_strtolower(trim($d->code)) === $deptLower);
                    }

                    // 4. Normalized substring / contains match
                    if (!$dept) {
                        $dept = $allDepts->first(function($d) use ($deptLower) {
                            $dbLower = mb_strtolower(trim($d->name));
                            return str_contains($deptLower, $dbLower) || str_contains($dbLower, $deptLower);
                        });
                    }
                    
                    // If still not matched, record as unmatched (DO NOT CREATE)
                    if (!$dept) {
                        $unmatchedDepts[$deptName] = ($unmatchedDepts[$deptName] ?? 0) + 1;
                    }
                }

                // Program Matching (SSOT Reference - No Auto-Creation)
                $prog = null;
                if ($progName !== '') {
                    $progLower = mb_strtolower($progName);
                    
                    // Scope search to department if resolved, otherwise search all programs
                    $searchProgs = $dept 
                        ? $allProgs->where('department_id', $dept->id) 
                        : $allProgs;
                    
                    // 1. Exact match
                    $prog = $searchProgs->firstWhere('name', $progName);
                    
                    // 2. Case-insensitive trimmed match
                    if (!$prog) {
                        $prog = $searchProgs->first(fn($p) => mb_strtolower(trim($p->name)) === $progLower);
                    }
                    
                    // 3. Match by program code/abbreviation (e.g. "BSIT", "BSCS", "BSCE", "BSA")
                    if (!$prog) {
                        $prog = $searchProgs->first(fn($p) => 
                            !empty($p->code) && mb_strtolower(trim($p->code)) === $progLower
                        );
                    }

                    // 4. Normalized contains match within department
                    if (!$prog) {
                        $cleanProg = preg_replace('/\s+/', ' ', $progLower);
                        $prog = $searchProgs->first(function($p) use ($cleanProg) {
                            $dbLower = mb_strtolower(trim($p->name));
                            $cleanDb = preg_replace('/\s+/', ' ', $dbLower);
                            return str_contains($cleanProg, $cleanDb) || str_contains($cleanDb, $cleanProg);
                        });
                    }
                    
                    // 5. Broaden search to ALL master programs if dept-scoped search failed
                    if (!$prog && $dept) {
                        $prog = $allProgs->first(fn($p) => mb_strtolower(trim($p->name)) === $progLower);
                        if (!$prog) {
                            $prog = $allProgs->first(fn($p) => !empty($p->code) && mb_strtolower(trim($p->code)) === $progLower);
                        }
                        if (!$prog) {
                            $cleanProg = preg_replace('/\s+/', ' ', $progLower);
                            $prog = $allProgs->first(function($p) use ($cleanProg) {
                                $dbLower = mb_strtolower(trim($p->name));
                                $cleanDb = preg_replace('/\s+/', ' ', $dbLower);
                                return str_contains($cleanProg, $cleanDb) || str_contains($cleanDb, $cleanProg);
                            });
                        }
                        // If found in another department, sync $dept
                        if ($prog && $prog->department_id) {
                            $dept = $allDepts->firstWhere('id', $prog->department_id) ?: $dept;
                        }
                    }
                    
                    // If still not matched, record as unmatched (DO NOT CREATE)
                    if (!$prog) {
                        $unmatchedProgs[$progName] = ($unmatchedProgs[$progName] ?? 0) + 1;
                    }
                }

                Student::query()->updateOrCreate(
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

        // Clear academic caches so UI reflects updated records immediately
        \Illuminate\Support\Facades\Cache::forget('academic_departments_all');
        \Illuminate\Support\Facades\Cache::forget('academic_programs_all');
        \Illuminate\Support\Facades\Cache::forget('student_year_levels');

        $skipped = $totalDataRows - $count;
        $message = "Successfully imported {$count} patrons.";
        if ($skipped > 0) {
            $message .= " ({$skipped} rows skipped due to missing ID.)";
        }
        if (!empty($unmatchedDepts)) {
            $deptList = array_map(fn($k, $v) => "'{$k}' ({$v}x)", array_keys($unmatchedDepts), array_values($unmatchedDepts));
            $message .= " Note: " . count($unmatchedDepts) . " department name(s) were not found in master records: " . implode(', ', array_slice($deptList, 0, 3)) . ".";
        }
        if (!empty($unmatchedProgs)) {
            $progList = array_map(fn($k, $v) => "'{$k}' ({$v}x)", array_keys($unmatchedProgs), array_values($unmatchedProgs));
            $message .= " Note: " . count($unmatchedProgs) . " program name(s) were not found in master records: " . implode(', ', array_slice($progList, 0, 3)) . ".";
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
        Student::query()->with(['academicDepartment', 'academicProgram'])->chunk(500, function ($students) use ($sheet, &$row) {
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
