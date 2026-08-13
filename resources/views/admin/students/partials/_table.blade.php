            @php
                function sortLink($col, $label) {
                    $currentSort = request('sort_by', 'last_name');
                    $currentDir = request('sort_dir', 'asc');
                    $newDir = ($currentSort === $col && $currentDir === 'asc') ? 'desc' : 'asc';
                    $params = array_merge(request()->except(['sort_by', 'sort_dir']), ['sort_by' => $col, 'sort_dir' => $newDir]);
                    $url = route('admin.students.index', $params);
                    $icon = '';
                    if ($currentSort === $col) {
                        $icon = $currentDir === 'asc' ? ' ▲' : ' ▼';
                    }
                    return '<a href="' . e($url) . '" class="inline-flex items-center gap-1 hover:text-[var(--cjc-navy)] font-bold">' . e($label) . '<span class="text-xs text-[var(--cjc-red)]">' . $icon . '</span></a>';
                }
            @endphp

            <!-- Table Directory -->
            <div class="overflow-x-auto border border-gray-200 rounded-lg">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 text-gray-600 text-xs uppercase tracking-wider border-b border-gray-200 select-none">
                            <th class="p-4">{!! sortLink('name', 'Patron Name') !!}</th>
                            <th class="p-4">{!! sortLink('id', 'ID Number') !!}</th>
                            <th class="p-4">{!! sortLink('patron_category', 'Category') !!}</th>
                            <th class="p-4">{!! sortLink('department_id', 'Department & Program') !!}</th>
                            <th class="p-4">{!! sortLink('year_level', 'Year Level') !!}</th>
                            <th class="p-4 text-center">{!! sortLink('violations_count', 'Violations') !!}</th>
                            <th class="p-4 text-right font-bold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($students as $student)
                            <tr class="hover:bg-gray-50/80 transition-colors">
                                <td class="p-4">
                                    <div class="font-bold text-[var(--cjc-navy)]">{{ $student->full_name }}</div>
                                    @if($student->email)
                                        <div class="text-xs text-gray-500 font-mono">{{ $student->email }}</div>
                                    @endif
                                </td>
                                <td class="p-4 text-sm font-mono font-semibold text-gray-700">{{ $student->id }}</td>
                                <td class="p-4 text-sm">
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-blue-50 text-blue-700 border border-blue-200">
                                        {{ $student->patron_category ?: 'Student' }}
                                    </span>
                                </td>
                                <td class="p-4 text-sm text-gray-700 font-medium">
                                    <div class="font-bold">{{ $student->academicDepartment?->name ?: '—' }}</div>
                                    <div class="text-xs text-gray-500">{{ $student->academicProgram?->name ?: '—' }}</div>
                                </td>
                                <td class="p-4 text-sm text-gray-600">
                                    {{ $student->year_level ?: '—' }}
                                </td>
                                <td class="p-4 text-center">
                                    @php
                                        $vCount = (int) ($student->violations_count ?? ($student->relationLoaded('violations') ? $student->violations->count() : $student->violations()->count()));
                                    @endphp
                                    @if($vCount > 0)
                                        <span class="inline-flex items-center justify-center px-2.5 py-1 text-xs font-bold leading-none text-white bg-red-600 rounded-full shadow-sm">
                                            {{ $vCount }} {{ $vCount === 1 ? 'Violation' : 'Violations' }}
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-400">None</span>
                                    @endif
                                </td>
                                <td class="p-4 text-right whitespace-nowrap">
                                    <button @click="openEditModal({{ json_encode($student) }})" class="text-xs font-semibold px-3 py-1.5 rounded bg-blue-100 hover:bg-blue-200 text-blue-700 transition-colors mr-1">
                                        Edit
                                    </button>
                                    @if(auth('admin')->user()->isSuperAdmin())
                                    <button type="button" @click="confirmDeletePatron({{ json_encode($student) }})" class="text-xs font-semibold px-3 py-1.5 rounded bg-red-100 hover:bg-red-200 text-red-700 transition-colors mr-1">
                                        Delete
                                    </button>
                                    @endif
                                    <button @click="openViolationModal({{ json_encode($student) }})" class="text-xs font-semibold px-3 py-1.5 rounded bg-gray-100 hover:bg-gray-200 text-gray-700 transition-colors">
                                        Manage Violations
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-12 text-center text-gray-400">
                                    No patrons found matching the selected filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="mt-4">
                {{ $students->links() }}
            </div>
