@extends('layouts.admin')

@section('title', ' | Patron Directory')
@section('header_title', 'Patron Directory & Violations')

@section('admin_content')
<div class="space-y-6" x-data="{
    showAddStudentModal: false,
    showEditStudentModal: false,
    showImportModal: false,
    showViolationModal: false,
    showConfirmModal: false,
    confirmActionUrl: '',
    confirmTitle: '',
    confirmMessage: '',
    confirmButtonText: 'Confirm',
    selectedStudent: null,
    editStudentData: {},
    
    openViolationModal(student) {
        this.selectedStudent = student;
        this.showViolationModal = true;
    },
    
    openEditModal(student) {
        this.editStudentData = { ...student };
        this.showEditStudentModal = true;
    },

    confirmSettleViolation(violation) {
        this.confirmActionUrl = `/admin/violations/${violation.id}`;
        this.confirmTitle = 'Settle / Remove Violation?';
        this.confirmMessage = `Are you sure you want to remove the '${violation.violation_type?.name || 'violation'}' record for ${this.selectedStudent?.first_name} ${this.selectedStudent?.last_name}? This will clear it from their active record.`;
        this.confirmButtonText = 'Yes, Settle Violation';
        this.showConfirmModal = true;
    },

    confirmDeletePatron(student) {
        this.confirmActionUrl = `/admin/students/${student.id}`;
        this.confirmTitle = 'Delete Patron?';
        this.confirmMessage = `Are you sure you want to delete patron ${student.first_name} ${student.last_name} (${student.id})? This action cannot be undone.`;
        this.confirmButtonText = 'Yes, Delete Patron';
        this.showConfirmModal = true;
    }
}">

    <div class="card bg-white border-t-4 border-t-[var(--cjc-red)]">
        <div class="p-6">
            
@include('admin.students.partials._header_actions')

@include('admin.students.partials._filters')

@include('admin.students.partials._table')

        </div>
    </div>

@include('admin.students.partials._add_modal')

@include('admin.students.partials._edit_modal')

@include('admin.students.partials._violation_modal')

@include('admin.students.partials._confirm_modal')

@include('admin.students.partials._import_modal')

</div>
@endsection
