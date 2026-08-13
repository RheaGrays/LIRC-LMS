    <!-- Import Excel Modal -->
    <template x-teleport="body">
        <div x-show="showImportModal" class="fixed inset-0 z-50 overflow-y-auto" style="z-index: 9999;" x-cloak>
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" @click="showImportModal = false">
                    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm"></div>
                </div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>&#8203;
                <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100">
                    <div class="p-6">
                        <div class="flex items-center justify-between border-b pb-4 mb-4">
                            <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                Import Patrons via Excel / CSV
                            </h3>
                            <button @click="showImportModal = false" class="text-gray-400 hover:text-gray-600">&times;</button>
                        </div>
                        <form action="{{ route('admin.students.import') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <p class="text-sm text-gray-600 mb-4">Upload an Excel Spreadsheet (<code>.xlsx</code>, <code>.xls</code>, or <code>.csv</code>) matching the exact export format. The columns must be in this exact order: <br>
                            <strong>1. ID, 2. Last Name, 3. First Name, 4. Middle Name, 5. Category (Student/Alumni), 6. Department, 7. Program, 8. Year Level, 9. Email</strong>.</p>
                            <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-blue-500 transition-colors bg-gray-50/50 mb-4">
                                <input type="file" name="file" accept=".xlsx,.xls,.csv" required class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            </div>
                            <div class="flex justify-end gap-3">
                                <button type="button" @click="showImportModal = false" class="btn-secondary">Cancel</button>
                                <button type="submit" class="btn-primary">Upload & Import</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </template>
