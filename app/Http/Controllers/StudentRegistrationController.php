<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StudentRegistrationController extends Controller
{
    public function index()
    {
        return view('register.index');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'studentId' => 'required|string|max:50|unique:students,id',
            'lastName' => 'required|string|max:255',
            'firstName' => 'required|string|max:255',
            'middleName' => 'nullable|string|max:255',
            'level' => 'required|in:college,basic_ed',
            'college' => 'required|string|max:255',
            'department' => 'nullable|string|max:255', // used for program in college
            'yearLevel' => 'required|string|max:50',
            'email' => 'required|email|max:255',
            'contactNumber' => 'nullable|string|max:50',
            'photoDataUrl' => 'nullable|string',
        ]);

        $photoPath = null;
        if (!empty($validated['photoDataUrl']) && str_starts_with($validated['photoDataUrl'], 'data:image')) {
            // It's a base64 image
            $imageParts = explode(';base64,', $validated['photoDataUrl']);
            if (count($imageParts) == 2) {
                $imageTypeAux = explode('image/', $imageParts[0]);
                $imageType = $imageTypeAux[1] ?? 'jpeg';
                $imageBase64 = base64_decode($imageParts[1]);
                $safeId = Str::slug($validated['studentId'], '_');
                $fileName = "student-photos/{$safeId}_" . time() . ".{$imageType}";
                
                Storage::disk('public')->put($fileName, $imageBase64);
                $photoPath = $fileName;
            }
        }

        $student = new Student();
        $student->id = $validated['studentId'];
        $student->last_name = strtoupper(trim($validated['lastName']));
        $student->first_name = strtoupper(trim($validated['firstName']));
        $student->middle_name = $validated['middleName'] ? strtoupper(trim($validated['middleName'])) : null;
        
        if ($validated['level'] === 'basic_ed') {
            $student->department = $validated['college'];
        } else {
            $student->department = $validated['department']; // Program/Course
        }
        
        $student->year_level = $validated['yearLevel'];
        $student->email = $validated['email'];
        $student->contact = $validated['contactNumber'];
        $student->photo_path = $photoPath;
        $student->status = 'active';
        $student->save();

        return response()->json(['success' => true]);
    }
}
