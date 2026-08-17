            <!-- Filter & Search Controls Bar -->
            <form method="GET" 
                  action="{{ route('admin.students.index') }}" 
                  x-data="{ 
                      timer: null,
                      submitDebounced() {
                          clearTimeout(this.timer);
                          this.timer = setTimeout(() => {
                              $el.submit();
                          }, 350);
                      },
                      submitImmediate() {
                          clearTimeout(this.timer);
                          $el.submit();
                      }
                  }"
                  class="mb-6 space-y-3 bg-gray-50/80 p-4 rounded-xl border border-gray-200">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
                    
                    <!-- Search Input -->
                    <div>
                        <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Search</label>
                        <div class="relative">
                            <input type="text" 
                                   name="search" 
                                   value="{{ request('search') }}" 
                                   @input="submitDebounced()"
                                   @keydown.enter.prevent="submitImmediate()"
                                   placeholder="Search ID, Name, Dept..." 
                                   class="w-full pl-8 pr-8 p-2 text-sm bg-white border border-gray-300 rounded-lg focus:outline-none focus:border-[var(--cjc-navy)] focus:ring-1 focus:ring-[var(--cjc-navy)]">
                            <svg class="w-4 h-4 text-gray-400 absolute left-2.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0" />
                            </svg>
                            @if(request('search'))
                                <a href="{{ request()->fullUrlWithQuery(['search' => null]) }}" 
                                   title="Clear Search"
                                   class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </a>
                            @endif
                        </div>
                    </div>

@php
    $catOptions = array_merge([['value' => '', 'label' => 'All Categories']], collect($patronCategories)->map(fn($c) => ['value' => $c, 'label' => $c])->toArray());
    $deptOptions = array_merge([['value' => '', 'label' => 'All Departments']], collect($departmentsList)->map(fn($d) => ['value' => (string)$d->id, 'label' => $d->name])->toArray());
    $progOptions = array_merge([['value' => '', 'label' => 'All Programs']], collect($programsList)->map(fn($p) => ['value' => (string)$p->id, 'label' => $p->name])->toArray());
    $ylOptions = array_merge([['value' => '', 'label' => 'All Year Levels']], collect($yearLevelsList)->map(fn($y) => ['value' => $y, 'label' => $y])->toArray());
    
    $sortByOptions = [
        ['value' => 'last_name', 'label' => 'Name (Last Name)'],
        ['value' => 'id', 'label' => 'ID Number'],
        ['value' => 'department_id', 'label' => 'Department'],
        ['value' => 'year_level', 'label' => 'Year Level'],
        ['value' => 'patron_category', 'label' => 'Patron Category'],
    ];
    $sortDirOptions = [
        ['value' => 'asc', 'label' => 'Ascending (A-Z, 1-9)'],
        ['value' => 'desc', 'label' => 'Descending (Z-A, 9-1)'],
    ];
@endphp

                    <!-- Category Filter -->
                    <div>
                        <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Patron Category</label>
                        <x-custom-select name="category" :value="request('category')" :options="$catOptions" placeholder="All Categories" />
                    </div>

                    <!-- Department Filter -->
                    <div>
                        <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Department</label>
                        <x-custom-select name="department_id" :value="request('department_id')" :options="$deptOptions" placeholder="All Departments" />
                    </div>

                    <!-- Program Filter -->
                    <div>
                        <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Program</label>
                        <x-custom-select name="program_id" :value="request('program_id')" :options="$progOptions" placeholder="All Programs" />
                    </div>

                    <!-- Year Level Filter -->
                    <div>
                        <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Year Level</label>
                        <x-custom-select name="year_level" :value="request('year_level')" :options="$ylOptions" placeholder="All Year Levels" />
                    </div>

                </div>

                <div class="flex flex-wrap items-center justify-between gap-3 pt-2 border-t border-gray-200/70">
                    <!-- Sort By Dropdowns -->
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-xs font-semibold text-gray-500">Sort By:</span>
                        <div class="w-48">
                            <x-custom-select name="sort_by" :value="request('sort_by', 'last_name')" :options="$sortByOptions" placeholder="Sort By" />
                        </div>
                        <div class="w-48">
                            <x-custom-select name="sort_dir" :value="request('sort_dir', 'asc')" :options="$sortDirOptions" placeholder="Order" />
                        </div>
                    </div>

                    <!-- Reset Filters Button -->
                    @if(request()->hasAny(['search', 'category', 'department_id', 'program_id', 'year_level', 'sort_by', 'sort_dir']))
                        <a href="{{ route('admin.students.index') }}" class="text-xs text-red-600 hover:text-red-800 font-semibold flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            Clear Filters
                        </a>
                    @endif
                </div>
            </form>
