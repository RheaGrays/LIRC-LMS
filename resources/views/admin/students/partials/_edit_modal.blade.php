    <!-- Edit Patron Modal -->
    <template x-teleport="body">
        <div x-show="showEditStudentModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm" style="display: none;" x-cloak>
            <div class="bg-white rounded-xl shadow-xl w-full max-w-lg mx-4 overflow-hidden" @click.away="showEditStudentModal = false">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                    <h3 class="font-bold text-[var(--cjc-navy)] text-lg">Edit Patron</h3>
                    <button @click="showEditStudentModal = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form :action="`/admin/students/${editStudentData.id}`" method="POST" class="p-6">
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Patron Category *</label>
                            <select name="patron_category" x-model="editStudentData.patron_category" required class="w-full p-2 border border-gray-300 rounded focus:border-[var(--cjc-navy)] outline-none text-sm bg-white">
                                @foreach($patronCategories as $cat)
                                    <option value="{{ $cat }}">{{ $cat }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">ID Number (Cannot be changed)</label>
                            <input type="text" x-model="editStudentData.id" disabled class="w-full p-2 border border-gray-300 rounded bg-gray-100 text-gray-500 outline-none text-sm font-mono cursor-not-allowed">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">First Name *</label>
                            <input type="text" name="first_name" x-model="editStudentData.first_name" required class="w-full p-2 border border-gray-300 rounded focus:border-[var(--cjc-navy)] outline-none text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Last Name *</label>
                            <input type="text" name="last_name" x-model="editStudentData.last_name" required class="w-full p-2 border border-gray-300 rounded focus:border-[var(--cjc-navy)] outline-none text-sm">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Middle Name (Optional)</label>
                            <input type="text" name="middle_name" x-model="editStudentData.middle_name" class="w-full p-2 border border-gray-300 rounded focus:border-[var(--cjc-navy)] outline-none text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Department</label>
                            <select name="department_id" x-model="editStudentData.department_id" class="w-full p-2 border border-gray-300 rounded focus:border-[var(--cjc-navy)] outline-none text-sm bg-white">
                                <option value="">None</option>
                                @foreach($departmentsList as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Program</label>
                            <select name="program_id" x-model="editStudentData.program_id" class="w-full p-2 border border-gray-300 rounded focus:border-[var(--cjc-navy)] outline-none text-sm bg-white">
                                <option value="">None</option>
                                @foreach($programsList as $prog)
                                    <option value="{{ $prog->id }}">{{ $prog->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Year Level (Optional)</label>
                            <input type="text" name="year_level" x-model="editStudentData.year_level" class="w-full p-2 border border-gray-300 rounded focus:border-[var(--cjc-navy)] outline-none text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Email Address (Optional)</label>
                            <input type="email" name="email" x-model="editStudentData.email" class="w-full p-2 border border-gray-300 rounded focus:border-[var(--cjc-navy)] outline-none text-sm">
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" @click="showEditStudentModal = false" class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-md">Cancel</button>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-[var(--cjc-red)] hover:bg-red-700 rounded-md">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </template>
