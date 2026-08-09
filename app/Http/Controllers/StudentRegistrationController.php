<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\SystemSetting;
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
        $inputCategory = trim($request->input('patronCategory', ''));
        if (in_array(strtolower($inputCategory), ['staff', 'faculty', 'employee'])) {
            $inputCategory = 'Employee';
            $request->merge(['patronCategory' => 'Employee']);
        }

        $allowedCategories = SystemSetting::get('patron_categories', ['Student', 'Employee', 'Staff', 'Faculty', 'Post Graduate', 'Alumni', 'Visitor']);
        $isStudent = $inputCategory === 'Student';
        $isVisitor = $inputCategory === 'Visitor';

        $validated = $request->validate([
            'studentId'     => $isVisitor
                                ? 'nullable|string|max:50'
                                : 'required|string|max:50|unique:students,id',
            'lastName'      => 'required|string|max:255',
            'firstName'     => 'required|string|max:255',
            'middleName'    => 'nullable|string|max:255',
            'patronCategory'=> ['required', 'string', \Illuminate\Validation\Rule::in(array_merge($allowedCategories, ['Employee', 'Staff', 'Faculty']))],
            'level'         => $isStudent ? 'required|in:college,basic_ed' : 'nullable|in:college,basic_ed',
            'college'       => $isStudent ? 'required|string|max:255' : 'nullable|string|max:255',
            'department'    => $isStudent ? 'required_if:level,college|nullable|string|max:255' : 'nullable|string|max:255',
            'yearLevel'     => $isStudent ? 'required|string|max:50' : 'nullable|string|max:50',
            'email'         => 'nullable|email|max:255',
            'photoDataUrl'  => 'nullable|string',
        ]);

        // Handle photo upload
        $photoPath = null;
        if (!empty($validated['photoDataUrl']) && str_starts_with($validated['photoDataUrl'], 'data:image')) {
            $imageParts = explode(';base64,', $validated['photoDataUrl']);
            if (count($imageParts) == 2) {
                // Preliminary MIME allowlist check (user-controlled, but gives early rejection)
                $imageTypeAux = explode('image/', $imageParts[0]);
                $declaredType = strtolower($imageTypeAux[1] ?? '');
                if (!in_array($declaredType, ['jpeg', 'jpg', 'png', 'webp'])) {
                    return response()->json(['message' => 'Invalid image format. Allowed formats: jpeg, jpg, png, webp'], 422);
                }

                if (strlen($imageParts[1]) > 7 * 1024 * 1024) {
                    return response()->json(['message' => 'Image size must not exceed 7MB'], 422);
                }

                $imageBytes = base64_decode($imageParts[1], strict: true);
                if ($imageBytes === false) {
                    return response()->json(['message' => 'Invalid base64 image data.'], 422);
                }

                // SEC-03 FIX: Verify the decoded bytes are actually a valid image using PHP's
                // image detection — this cannot be spoofed via the data URL MIME string.
                // Use the real detected type for the file extension, not the declared one.
                $imageInfo = @getimagesizefromstring($imageBytes);
                if (!$imageInfo) {
                    return response()->json(['message' => 'Uploaded file is not a valid image.'], 422);
                }

                // Map PHP image type constant to a safe file extension
                $mimeToExt = [
                    IMAGETYPE_JPEG => 'jpg',
                    IMAGETYPE_PNG  => 'png',
                    IMAGETYPE_WEBP => 'webp',
                ];
                $detectedType = $imageInfo[2]; // IMAGETYPE_* constant
                if (!isset($mimeToExt[$detectedType])) {
                    return response()->json(['message' => 'Unsupported image type. Allowed: jpeg, png, webp'], 422);
                }

                // Use the real detected extension — not whatever the attacker declared in the MIME
                $safeExt  = $mimeToExt[$detectedType];
                $safeId   = Str::slug($validated['studentId'] ?? 'visitor_' . time(), '_');
                $fileName = "patron-photos/{$safeId}_" . time() . ".{$safeExt}";
                Storage::disk('public')->put($fileName, $imageBytes);
                $photoPath = $fileName;
            }
        }


        // Generate a unique ID for Visitors if none provided
        $patronId = $validated['studentId'] ?? ('VIS-' . strtoupper(Str::random(6)));

        // Ensure uniqueness for generated Visitor IDs
        while (Student::query()->find($patronId)) {
            $patronId = 'VIS-' . strtoupper(Str::random(6));
        }

        $student = new Student();
        $student->id             = $patronId;
        $student->last_name      = strtoupper(trim($validated['lastName']));
        $student->first_name     = strtoupper(trim($validated['firstName']));
        $student->middle_name    = $validated['middleName'] ? strtoupper(trim($validated['middleName'])) : null;
        $student->patron_category = $validated['patronCategory'];

        if ($isStudent) {
            // Look up the department by name
            $dept = \App\Models\AcademicDepartment::query()->where('name', '=', $validated['college'])->first();
            $student->department_id = $dept?->id;

            // Look up the program by name (for college level)
            if ($validated['level'] === 'college' && !empty($validated['department'])) {
                $prog = \App\Models\AcademicProgram::query()->where('name', '=', $validated['department'])->first();
                $student->program_id = $prog?->id;
            }

            $student->year_level = $validated['yearLevel'];
        } else {
            $student->department_id = null;
            $student->program_id = null;
            $student->year_level = null;
        }

        $student->email      = $validated['email'];
        $student->contact    = null; // contact no longer collected
        $student->photo_path = $photoPath;
        $student->status     = 'active';
        $student->save();

        return response()->json(['success' => true, 'id' => $student->id]);
    }
}
