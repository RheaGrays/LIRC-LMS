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
        $query = Student::withCount('violations')->with('violations');

        // Search ID, Name, Department/Program, Patron Category
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('department', 'like', "%{$search}%")
                  ->orWhere('patron_category', 'like', "%{$search}%");
            });
        }

        // Filter by Patron Category
        if ($request->filled('category')) {
            $query->where('patron_category', $request->category);
        }

        // Filter by Department / Program
        if ($request->filled('department')) {
            $query->where('department', $request->department);
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
            'department'      => ['department', 'last_name'],
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
        
        $departmentsList = Student::query()->whereNotNull('department', 'and')->where('department', '!=', '')->distinct()->pluck('department')->sort()->values();
        $yearLevelsList  = Student::query()->whereNotNull('year_level', 'and')->where('year_level', '!=', '')->distinct()->pluck('year_level')->sort()->values();

        return view('admin.students.index', compact('students', 'patronCategories', 'departmentsList', 'yearLevelsList'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id'              => 'required|string|max:50|unique:students,id',
            'first_name'      => 'required|string|max:255',
            'last_name'       => 'required|string|max:255',
            'middle_name'     => 'nullable|string|max:255',
            'patron_category' => 'required|string|max:100',
            'department'      => 'nullable|string|max:255',
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
            'department'      => 'nullable|string|max:255',
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
        
        $count = 0;
        
        \Illuminate\Support\Facades\DB::transaction(function () use ($rows, &$count) {
            foreach (array_slice($rows, 1, 5000) as $row) {
                $id = $row[0] ?? null;
                if (!$id) continue;

                Student::updateOrCreate(
                    ['id' => $id],
                    [
                        'last_name'       => $row[1] ?? '',
                        'first_name'      => $row[2] ?? '',
                        'middle_name'     => $row[3] ?? null,
                        'patron_category' => $row[4] ?? 'Student',
                        'department'      => $row[5] ?? '',
                        'year_level'      => $row[6] ?? '',
                        'email'           => $row[7] ?? null,
                    ]
                );
                $count++;
            }
        });

        return back()->with('success', "Successfully imported {$count} patrons.");
    }

    public function export()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = ['ID', 'Last Name', 'First Name', 'Middle Name', 'Patron Category', 'Department', 'Year Level', 'Email', 'Status'];
        $sheet->fromArray($headers, null, 'A1');

        $row = 2;
        Student::chunk(500, function ($students) use ($sheet, &$row) {
            foreach ($students as $student) {
                $sheet->fromArray([
                    $student->id,
                    $student->last_name,
                    $student->first_name,
                    $student->middle_name,
                    $student->patron_category,
                    $student->department,
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
