            <!-- Filter & Search Controls Bar -->
            <form method="GET" 
                  action="{{ route('admin.students.index') }}" 
                  id="patron-filter-form"
                  x-data="{ 
                      search: @js(request('search', '')),
                      timer: null,
                      loading: false,
                      abortController: null,
                      submitDebounced() {
                          clearTimeout(this.timer);
                          this.timer = setTimeout(() => {
                              this.submitImmediate();
                          }, 300);
                      },
                      submitImmediate(targetUrl = null) {
                          clearTimeout(this.timer);
                          this.fetchTable(targetUrl);
                      },
                      async fetchTable(customUrl = null) {
                          if (this.abortController) {
                              this.abortController.abort();
                          }
                          this.abortController = new AbortController();

                          this.loading = true;
                          const container = document.getElementById('students-table-container');
                          if (container) {
                              container.classList.add('opacity-60', 'transition-opacity');
                          }

                          let url;
                          if (customUrl) {
                              url = customUrl;
                          } else {
                              const formData = new FormData($el);
                              const params = new URLSearchParams();
                              for (const [key, val] of formData.entries()) {
                                  if (val !== '' && val !== null && val !== undefined) {
                                      params.append(key, val);
                                  }
                              }
                              const qs = params.toString();
                              url = `${$el.action}${qs ? '?' + qs : ''}`;
                          }

                          try {
                              const res = await fetch(url, {
                                  headers: {
                                      'X-Requested-With': 'XMLHttpRequest',
                                      'X-LEMS-Table-Only': '1'
                                  },
                                  signal: this.abortController.signal
                              });

                              if (!res.ok) throw new Error('Network error: ' + res.status);

                              const html = await res.text();
                              if (container) {
                                  if (window.Alpine && typeof window.Alpine.destroyTree === 'function') {
                                      window.Alpine.destroyTree(container);
                                  }
                                  container.innerHTML = html;
                                  if (window.Alpine && typeof window.Alpine.initTree === 'function') {
                                      window.Alpine.initTree(container);
                                  }
                              }
                              window.history.replaceState(null, '', url);
                          } catch (err) {
                              if (err.name !== 'AbortError') {
                                  console.error('[LEMS] Live search error, falling back to full submit:', err);
                                  $el.submit();
                              }
                          } finally {
                              this.loading = false;
                              if (container) {
                                  container.classList.remove('opacity-60');
                              }
                          }
                      }
                  }"
                  x-init="
                      $el.__liveSearch = (url) => submitImmediate(url);
                  "
                  @submit.prevent="submitImmediate()"
                  class="mb-6 space-y-3 bg-gray-50/80 p-4 rounded-xl border border-gray-200">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
                    
                    <!-- Search Input -->
                    <div>
                        <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Search</label>
                        <div class="relative">
                            <input type="text" 
                                   name="search" 
                                   x-ref="searchInput"
                                   x-model="search"
                                   @input="submitDebounced()"
                                   @keydown.enter.prevent="submitImmediate()"
                                   placeholder="Search ID, Name, Dept..." 
                                   class="w-full pl-8 pr-12 p-2 text-sm bg-white border border-gray-300 rounded-lg focus:outline-none focus:border-[var(--cjc-navy)] focus:ring-1 focus:ring-[var(--cjc-navy)]">
                            <svg class="w-4 h-4 text-gray-400 absolute left-2.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0" />
                            </svg>
                            <div class="absolute right-2.5 top-1/2 -translate-y-1/2 flex items-center gap-1.5">
                                <div x-show="loading" style="display: none;">
                                    <svg class="animate-spin h-3.5 w-3.5 text-[var(--cjc-navy)]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                    </svg>
                                </div>
                                <button type="button" 
                                        x-show="search && search.length > 0" 
                                        @click="search = ''; submitImmediate(); $refs.searchInput.focus();"
                                        title="Clear Search"
                                        class="text-gray-400 hover:text-gray-600 transition-colors focus:outline-none"
                                        style="display: none;">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

@php
    $catOptions = array_merge([['value' => '', 'label' => 'All Categories']], collect($patronCategories)->map(fn($c) => ['value' => $c, 'label' => $c])->toArray());
    $deptOptions = array_merge([['value' => '', 'label' => 'All Departments']], collect($departmentsList)->map(fn($d) => ['value' => (string)$d->id, 'label' => $d->name])->toArray());
    $progOptions = array_merge(
        [['value' => '', 'label' => 'All Programs', 'department_id' => '']], 
        collect($programsList)->map(fn($p) => [
            'value' => (string)$p->id, 
            'label' => $p->name,
            'department_id' => (string)$p->department_id
        ])->toArray()
    );
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
                        <x-custom-select name="department_id" 
                                         :value="request('department_id')" 
                                         :options="$deptOptions" 
                                         placeholder="All Departments" 
                                         dispatch-event="department-changed" />
                    </div>

                    <!-- Program Filter -->
                    <div>
                        <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Program</label>
                        <x-custom-select name="program_id" 
                                         :value="request('program_id')" 
                                         :options="$progOptions" 
                                         placeholder="All Programs" 
                                         depends-on="department-changed" 
                                         filter-key="department_id" 
                                         :filter-val="request('department_id')" />
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
