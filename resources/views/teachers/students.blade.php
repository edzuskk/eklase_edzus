@php
use App\Models\User;
@endphp

<x-layout>
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0">Students Management</h1>
            <a href="{{ route('teacher.students.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Add New Student
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Add Filter Form -->
        <div class="card mb-4">
            <div class="card-body">
                <form action="{{ route('teacher.students.index') }}" method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label for="search" class="form-label">Search by Name/Email</label>
                        <input type="text" 
                            class="form-control" 
                            id="search" 
                            name="search" 
                            value="{{ $search ?? '' }}" 
                            placeholder="Enter name or email">
                    </div>
                    <div class="col-md-3">
                        <label for="class" class="form-label">Filter by Class</label>
                        <select class="form-select" id="class" name="class">
                            <option value="">All Classes</option>
                            @foreach(['10A', '10B', '10C'] as $class)
                                <option value="{{ $class }}" {{ request('class') == $class ? 'selected' : '' }}>
                                    {{ $class }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="subject" class="form-label">Filter by Subject</label>
                        <select class="form-select" id="subject" name="subject">
                            <option value="">All Subjects</option>
                            @foreach(App\Models\User::getSubjects() as $subject)
                                <option value="{{ $subject }}" {{ request('subject') == $subject ? 'selected' : '' }}>
                                    {{ $subject }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <div class="d-grid gap-2 w-100">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search"></i> Search
                            </button>
                            <a href="{{ route('teacher.students.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Clear
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th style="width: 60px">Photo</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Class</th>
                                <th>Grades</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($students as $student)
                                <tr>
                                    <td>
                                        <img src="{{ $student->profile_picture ? Storage::url($student->profile_picture) : asset('images/default-avatar.png') }}" 
                                             alt="Profile Picture" 
                                             class="rounded-circle"
                                             style="width: 40px; height: 40px; object-fit: cover;">
                                    </td>
                                    <td>{{ $student->name }}</td>
                                    <td>{{ $student->email }}</td>
                                    <td>{{ $student->student->class ?? 'Not assigned' }}</td>
                                    <td>
                                        @forelse($student->grades as $grade)
                                            <div class="d-inline-block position-relative me-2 mb-2">
                                                <button type="button" 
                                                    class="badge bg-primary border-0"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editGradeModal"
                                                    data-grade-id="{{ $grade->id }}"
                                                    data-student-name="{{ $student->name }}"
                                                    data-subject="{{ $grade->subject()->first()->name }}"
                                                    data-grade-value="{{ $grade->grade }}"
                                                    style="cursor: pointer;">
                                                    {{ $grade->subject()->first()->name }}: {{ $grade->grade }}
                                                    <i class="bi bi-pencil-fill ms-1"></i>
                                                </button>
                                                <button type="button" 
                                                    class="btn btn-sm btn-danger delete-grade"
                                                    data-grade-id="{{ $grade->id }}"
                                                    style="position: absolute; top: -8px; right: -8px; padding: 0.1rem 0.3rem; font-size: 0.6rem;">
                                                    <i class="bi bi-x">-</i>
                                                </button>
                                            </div>
                                        @empty
                                            <span class="text-muted">No grades yet</span>
                                        @endforelse
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('teacher.students.edit', $student) }}" 
                                               class="btn btn-sm btn-primary">
                                                <i class="bi bi-pencil"></i> Edit
                                            </a>
                                            <button type="button" 
                                                class="btn btn-primary btn-sm"
                                                data-bs-toggle="modal"
                                                data-bs-target="#addGradeModal"
                                                data-student-id="{{ $student->id }}"
                                                data-student-name="{{ $student->name }}">
                                                <i class="bi bi-plus-circle"></i> Add Grade
                                            </button>
                                            <form action="{{ route('teacher.students.destroy', $student) }}" 
                                                  method="POST" 
                                                  class="d-inline"
                                                  onsubmit="return confirm('Are you sure you want to delete this student?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="bi bi-trash"></i> Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <div class="text-muted">
                                            No students found
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($students->hasPages())
                    <div class="mt-4">
                        {{ $students->appends(request()->query())->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Grade Modal -->
    <div class="modal fade" id="editGradeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Grade</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editGradeForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_subject" class="form-label">Subject</label>
                            <input type="text" class="form-control" id="edit_subject" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="edit_grade" class="form-label">Grade</label>
                            <input type="number" 
                                class="form-control" 
                                id="edit_grade" 
                                name="grade"
                                min="1" 
                                max="10" 
                                required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Grade</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Grade Modal -->
    <div class="modal fade" id="addGradeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Grade</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="addGradeForm" method="POST">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="student_id" id="student_id">
                        
                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject</label>
                            <select class="form-select" id="subject" name="subject" required>
                                <option value="">Select Subject</option>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}" data-name="{{ $subject->name }}">
                                        {{ $subject->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="grade" class="form-label">Grade</label>
                            <input type="number" 
                                   class="form-control" 
                                   id="grade" 
                                   name="grade" 
                                   min="1" 
                                   max="10" 
                                   required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Grade</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Edit Grade Modal Handler
    const editGradeModal = document.getElementById('editGradeModal');
    if (editGradeModal) {
        initializeEditGradeModal(editGradeModal);
    }

    // Add Grade Modal Handler
    const addGradeModal = document.getElementById('addGradeModal');
    if (addGradeModal) {
        initializeAddGradeModal(addGradeModal);
    }

    // Delete Grade Handler
    initializeDeleteGradeHandlers();
});

function initializeAddGradeModal(modal) {
    const form = modal.querySelector('#addGradeForm');
    
    modal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const studentId = button.getAttribute('data-student-id');
        const studentName = button.getAttribute('data-student-name');
        
        form.action = '/teacher/grades';
        modal.querySelector('#student_id').value = studentId;
        modal.querySelector('.modal-title').textContent = `Add Grade for ${studentName}`;
        form.reset();
    });

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        try {
            const formData = new FormData(this);
            const subjectSelect = this.querySelector('#subject');
            const selectedOption = subjectSelect.options[subjectSelect.selectedIndex];
            
            // Add subject name to form data
            formData.append('subject_name', selectedOption.getAttribute('data-name'));

            const response = await fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            if (!response.ok) throw new Error(data.message || 'Failed to add grade');

            bootstrap.Modal.getInstance(modal).hide();
            showAlert('success', 'Grade added successfully');
            setTimeout(() => location.reload(), 1500);

        } catch (error) {
            showAlert('danger', error.message);
            console.error('Error:', error);
        }
    });
}

function initializeEditGradeModal(modal) {
    const form = modal.querySelector('#editGradeForm');
    
    modal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        form.action = `/teacher/grades/${button.getAttribute('data-grade-id')}`;
        modal.querySelector('#edit_subject').value = button.getAttribute('data-subject');
        modal.querySelector('#edit_grade').value = button.getAttribute('data-grade-value');
    });

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        try {
            const formData = new FormData(this);
            
            const response = await fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();
            if (!response.ok) throw new Error(data.message || 'Failed to update grade');

            bootstrap.Modal.getInstance(modal).hide();
            showAlert('success', 'Grade updated successfully');
            setTimeout(() => location.reload(), 1500);

        } catch (error) {
            showAlert('danger', error.message);
            console.error('Error:', error);
        }
    });
}

function initializeDeleteGradeHandlers() {
    document.querySelectorAll('.delete-grade').forEach(button => {
        button.addEventListener('click', async function() {
            if (!confirm('Are you sure you want to delete this grade?')) return;
            
            try {
                const response = await fetch(`/teacher/grades/${this.dataset.gradeId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();
                if (!response.ok) throw new Error(data.message);

                this.closest('.d-inline-block').remove();
                showAlert('success', 'Grade deleted successfully');

            } catch (error) {
                showAlert('danger', error.message);
                console.error('Error:', error);
            }
        });
    });
}

function showAlert(type, message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    const container = document.querySelector('.container');
    container.insertAdjacentElement('afterbegin', alertDiv);
    setTimeout(() => alertDiv.remove(), 3000);
}
</script>
@endpush
</x-layout>