<?php

namespace App\Http\Controllers;

use App\Models\LibraryCollection;
use Illuminate\Http\Request;

class LibraryCollectionController extends Controller
{
    public function index()
    {
        $collections = LibraryCollection::query()->orderBy('sort_order', 'asc')->get();
        return view('admin.library-collections.index', compact('collections'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'badge'       => ['required', 'string', 'max:60'],
            'badge_color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'title'       => ['required', 'string', 'max:120'],
            'description' => ['required', 'string', 'max:400'],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
            'is_active'   => ['boolean'],
        ]);

        $data['is_active']  = $request->boolean('is_active', true);
        $data['sort_order'] = $data['sort_order'] ?? LibraryCollection::max('sort_order') + 1;

        LibraryCollection::create($data);

        return back()->with('success', 'Collection slide added successfully.');
    }

    public function update(Request $request, LibraryCollection $libraryCollection)
    {
        $data = $request->validate([
            'badge'       => ['required', 'string', 'max:60'],
            'badge_color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'title'       => ['required', 'string', 'max:120'],
            'description' => ['required', 'string', 'max:400'],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
            'is_active'   => ['boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        $libraryCollection->update($data);

        return back()->with('success', 'Collection slide updated.');
    }

    /**
     * @param \App\Models\LibraryCollection $libraryCollection
     */
    public function destroy(LibraryCollection $libraryCollection)
    {
        $libraryCollection->delete(null);

        return back()->with('success', 'Collection slide deleted.');
    }
}
