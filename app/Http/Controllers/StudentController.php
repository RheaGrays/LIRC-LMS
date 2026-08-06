<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $query = Student::withCount('violations')->with('violations')->orderBy('last_name')->orderBy('first_name');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('department', 'like', "%{$search}%");
            });
        }

        $students = $query->paginate(20)->withQueryString();

        return view('admin.students.index', compact('students'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|string|max:50|unique:students,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'department' => 'required|string|max:255',
            'year_level' => 'required|string|max:50',
            'email' => 'nullable|email|max:255',
            'contact' => 'nullable|string|max:50',
        ]);

        Student::create($validated);
        return back()->with('success', 'Student created successfully.');
    }

    public function update(Request $request, $id)
    {
        $student = Student::findOrFail($id);
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'department' => 'required|string|max:255',
            'year_level' => 'required|string|max:50',
            'email' => 'nullable|email|max:255',
            'contact' => 'nullable|string|max:50',
        ]);

        $student->update($validated);
        return back()->with('success', 'Student updated successfully.');
    }

    public function destroy($id)
    {
        Student::findOrFail($id)->delete();
        return back()->with('success', 'Student deleted successfully.');
    }

    public function edit($id)
    {
        $student = Student::findOrFail($id);
        return view('admin.students.edit', compact('student'));
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
        
        // Wrap in transaction and limit to 5000 rows max to prevent partial data and timeouts
        \Illuminate\Support\Facades\DB::transaction(function () use ($rows, &$count) {
            foreach (array_slice($rows, 1, 5000) as $row) {
                $id = $row[0] ?? null;
                if (!$id) continue;

                Student::updateOrCreate(
                    ['id' => $id],
                    [
                        'last_name' => $row[1] ?? '',
                        'first_name' => $row[2] ?? '',
                        'middle_name' => $row[3] ?? null,
                        'department' => $row[4] ?? '',
                        'year_level' => $row[5] ?? '',
                        'email' => $row[6] ?? null,
                        'contact' => $row[7] ?? null,
                    ]
                );
                $count++;
            }
        });

        return back()->with('success', "Successfully imported {$count} students.");
    }

    public function export()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = ['ID', 'Last Name', 'First Name', 'Middle Name', 'Department', 'Year Level', 'Email', 'Contact', 'Status'];
        $sheet->fromArray($headers, null, 'A1');

        $row = 2;
        Student::chunk(500, function ($students) use ($sheet, &$row) {
            foreach ($students as $student) {
                $sheet->fromArray([
                    $student->id,
                    $student->last_name,
                    $student->first_name,
                    $student->middle_name,
                    $student->department,
                    $student->year_level,
                    $student->email,
                    $student->contact,
                    $student->status,
                ], null, 'A' . $row);
                $row++;
            }
        });

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');

        return response()->streamDownload(function() use ($writer) {
            $writer->save('php://output');
        }, 'students.xlsx');
    }
}
