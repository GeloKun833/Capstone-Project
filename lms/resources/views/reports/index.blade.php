@extends('layouts.master')
@section('content')

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <h3 class="page-title">Reports & Document Generation</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Reports</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Transcript Report -->
            <div class="col-md-6 col-lg-3">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="fas fa-file-alt fa-3x text-primary"></i>
                        </div>
                        <h5 class="card-title">Student Transcript</h5>
                        <p class="text-muted">Generate comprehensive academic transcript</p>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#transcriptModal">
                            Generate Transcript
                        </button>
                    </div>
                </div>
            </div>

            <!-- Class List Report -->
            <div class="col-md-6 col-lg-3">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="fas fa-users fa-3x text-success"></i>
                        </div>
                        <h5 class="card-title">Class List</h5>
                        <p class="text-muted">Generate section roster/class list</p>
                        <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#classListModal">
                            Generate Class List
                        </button>
                    </div>
                </div>
            </div>

            <!-- Grade Slip Report -->
            <div class="col-md-6 col-lg-3">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="fas fa-clipboard-list fa-3x text-warning"></i>
                        </div>
                        <h5 class="card-title">Grade Slip</h5>
                        <p class="text-muted">Generate individual grade slip</p>
                        <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#gradeSlipModal">
                            Generate Grade Slip
                        </button>
                    </div>
                </div>
            </div>

            <!-- Progress Summary Report -->
            <div class="col-md-6 col-lg-3">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="fas fa-chart-line fa-3x text-info"></i>
                        </div>
                        <h5 class="card-title">Progress Summary</h5>
                        <p class="text-muted">Generate student progress summary</p>
                        <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#progressSummaryModal">
                            Generate Summary
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Transcript Modal -->
<div class="modal fade" id="transcriptModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Generate Student Transcript</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('reports.transcript', ['studentId' => 'STUDENT_ID']) }}" method="GET" id="transcriptForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Student</label>
                        <select name="student_id" class="form-select" required>
                            <option value="">Select Student</option>
                            @foreach($students as $student)
                                <option value="{{ $student->id }}">{{ $student->last_name }}, {{ $student->first_name }} - {{ $student->year_level }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Academic Year (Optional)</label>
                        <select name="academic_year_id" class="form-select">
                            <option value="">All Academic Years</option>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}">{{ $year->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Semester (Optional)</label>
                        <select name="semester_id" class="form-select">
                            <option value="">All Semesters</option>
                            @foreach($semesters as $semester)
                                <option value="{{ $semester->id }}">{{ $semester->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Format</label>
                        <select name="format" class="form-select">
                            <option value="pdf">PDF</option>
                            <option value="excel">Excel (Coming Soon)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Generate Transcript</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Class List Modal -->
<div class="modal fade" id="classListModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Generate Class List</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('reports.class-list', ['sectionId' => 'SECTION_ID']) }}" method="GET" id="classListForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Section</label>
                        <select name="section_id" class="form-select" required>
                            <option value="">Select Section</option>
                            @foreach($sections as $section)
                                <option value="{{ $section->id }}">{{ $section->name }} - {{ $section->grade_level }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Academic Year (Optional)</label>
                        <select name="academic_year_id" class="form-select">
                            <option value="">Current Academic Year</option>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}">{{ $year->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Semester (Optional)</label>
                        <select name="semester_id" class="form-select">
                            <option value="">Current Semester</option>
                            @foreach($semesters as $semester)
                                <option value="{{ $semester->id }}">{{ $semester->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Format</label>
                        <select name="format" class="form-select">
                            <option value="pdf">PDF</option>
                            <option value="excel">Excel (Coming Soon)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Generate Class List</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Grade Slip Modal -->
<div class="modal fade" id="gradeSlipModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Generate Grade Slip</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('reports.grade-slip', ['studentId' => 'STUDENT_ID']) }}" method="GET" id="gradeSlipForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Student</label>
                        <select name="student_id" class="form-select" required>
                            <option value="">Select Student</option>
                            @foreach($students as $student)
                                <option value="{{ $student->id }}">{{ $student->last_name }}, {{ $student->first_name }} - {{ $student->year_level }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Academic Year</label>
                        <select name="academic_year_id" class="form-select">
                            <option value="">Current Academic Year</option>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}">{{ $year->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Semester</label>
                        <select name="semester_id" class="form-select">
                            <option value="">Current Semester</option>
                            @foreach($semesters as $semester)
                                <option value="{{ $semester->id }}">{{ $semester->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Format</label>
                        <select name="format" class="form-select">
                            <option value="pdf">PDF</option>
                            <option value="excel">Excel (Coming Soon)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Generate Grade Slip</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Progress Summary Modal -->
<div class="modal fade" id="progressSummaryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Generate Progress Summary</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('reports.progress-summary', ['studentId' => 'STUDENT_ID']) }}" method="GET" id="progressSummaryForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Student</label>
                        <select name="student_id" class="form-select" required>
                            <option value="">Select Student</option>
                            @foreach($students as $student)
                                <option value="{{ $student->id }}">{{ $student->last_name }}, {{ $student->first_name }} - {{ $student->year_level }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Academic Year</label>
                        <select name="academic_year_id" class="form-select">
                            <option value="">Current Academic Year</option>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}">{{ $year->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Semester</label>
                        <select name="semester_id" class="form-select">
                            <option value="">Current Semester</option>
                            @foreach($semesters as $semester)
                                <option value="{{ $semester->id }}">{{ $semester->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Format</label>
                        <select name="format" class="form-select">
                            <option value="pdf">PDF</option>
                            <option value="excel">Excel (Coming Soon)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info">Generate Summary</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Transcript form handler
    document.getElementById('transcriptForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const studentId = this.querySelector('[name="student_id"]').value;
        if (!studentId) {
            alert('Please select a student');
            return;
        }
        const form = this.cloneNode(true);
        form.action = form.action.replace('STUDENT_ID', studentId);
        form.removeAttribute('id');
        form.style.display = 'none';
        document.body.appendChild(form);
        form.submit();
    });

    // Class list form handler
    document.getElementById('classListForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const sectionId = this.querySelector('[name="section_id"]').value;
        if (!sectionId) {
            alert('Please select a section');
            return;
        }
        const form = this.cloneNode(true);
        form.action = form.action.replace('SECTION_ID', sectionId);
        form.removeAttribute('id');
        form.style.display = 'none';
        document.body.appendChild(form);
        form.submit();
    });

    // Grade slip form handler
    document.getElementById('gradeSlipForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const studentId = this.querySelector('[name="student_id"]').value;
        if (!studentId) {
            alert('Please select a student');
            return;
        }
        const form = this.cloneNode(true);
        form.action = form.action.replace('STUDENT_ID', studentId);
        form.removeAttribute('id');
        form.style.display = 'none';
        document.body.appendChild(form);
        form.submit();
    });

    // Progress summary form handler
    document.getElementById('progressSummaryForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const studentId = this.querySelector('[name="student_id"]').value;
        if (!studentId) {
            alert('Please select a student');
            return;
        }
        const form = this.cloneNode(true);
        form.action = form.action.replace('STUDENT_ID', studentId);
        form.removeAttribute('id');
        form.style.display = 'none';
        document.body.appendChild(form);
        form.submit();
    });
});
</script>

@endsection


