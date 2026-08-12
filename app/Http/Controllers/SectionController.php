<?php

namespace App\Http\Controllers;

use App\Models\SectionLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SectionController extends Controller
{
    /** Admin view */
    public function index()
    {
        $admin    = Auth::guard('admin')->user();
        $logs     = $this->latestSections();
        $settings = \App\Models\SystemSetting::allSettings();
        return view('admin.sections.index', compact('admin', 'logs', 'settings'));
    }

    /** Statistics view */
    public function statistics()
    {
        $admin    = Auth::guard('admin')->user();
        $logs     = $this->latestSections();
        $settings = \App\Models\SystemSetting::allSettings();
        return view('admin.statistics.index', compact('admin', 'logs', 'settings'));
    }

    /** GET /admin/sections/latest — JSON for Alpine.js */
    public function latest(): JsonResponse
    {
        return response()->json($this->latestSections());
    }

    /** POST /admin/sections/upsert */
    public function upsert(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id'       => ['required', 'string'],
            'name'     => ['required', 'string'],
            'occupied' => ['required', 'integer', 'min:0'],
            'reserved' => ['required', 'integer', 'min:0'],
            'total'    => ['required', 'integer', 'min:1'],
        ]);

        SectionLog::upsertSnapshot($data);
        return response()->json(['success' => true]);
    }

    /** POST /admin/sections/upload-image */
    public function uploadImage(Request $request): JsonResponse
    {
        $request->validate([
            'section_id' => ['required', 'string'],
            'image'      => ['required', 'image', 'max:5120'], // 5MB max
        ]);

        $sectionId = $request->section_id;
        $file = $request->file('image');
        
        $filename = "{$sectionId}_" . time() . ".{$file->extension()}";
        $path = $file->storeAs('sections', $filename, 'public');

        // Update the SystemSetting
        $images = \App\Models\SystemSetting::get('section_images', []);
        $images[$sectionId] = "/storage/{$path}";
        \App\Models\SystemSetting::set('section_images', $images);

        return response()->json([
            'success' => true,
            'url'     => "/storage/{$path}"
        ]);
    }

    private function latestSections(): array
    {
        // Get only the latest row per section_code using a subquery
        $latestIds = SectionLog::query()
            ->selectRaw('MAX(id) as id', [])
            ->groupBy('section_code')
            ->pluck('id');

        $rows = SectionLog::query()
            ->whereIn('id', $latestIds)
            ->orderBy('section_code')
            ->get();

        return $rows->map(fn($r) => [
            'id'       => $r->section_code,
            'name'     => $r->section_name,
            'total'    => $r->total_capacity,
            'occupied' => $r->occupied,
            'reserved' => $r->reserved,
        ])->values()->toArray();
    }
}
