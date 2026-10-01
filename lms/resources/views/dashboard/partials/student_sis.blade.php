{{-- Student Information System — progressive disclosure profile --}}
@php
    $enrollments = $currentEnrollments ?? collect();
    $gradeRows = $grades ?? collect();
    $gpaRows = $gpaRecords ?? collect();
    $promotions = $promotionHistory ?? collect();
    $documents = collect($enrollmentDocuments ?? []);
    $documentChecklist = collect(\App\Models\EnrollmentDocument::REQUIRED_DOCUMENT_TYPES)
        ->map(function ($type) use ($documents) {
            return [
                'type' => $type,
                'document' => $documents->firstWhere('document_type', $type),
            ];
        });
    $additionalDocuments = $documents->whereNotIn('document_type', \App\Models\EnrollmentDocument::REQUIRED_DOCUMENT_TYPES);
    $lateCount = $lateCount ?? 0;
    $excusedCount = $excusedCount ?? 0;
    $sectionName = $sectionAssignment?->name ?? $student->sectionLabel();
    $gradeLevel = $sectionAssignment?->grade_level ?? ($student->year_level ?: $student->class);
    $academicYearName = $sectionAssignment?->academic_year_name ?? ($enrollments->first()?->academicYear->name);
    $semesterName = $sectionAssignment?->semester_name ?? ($enrollments->first()?->semester->name);
    $recentGrades = $gradeRows->take(5);
    $previewSubjects = $enrollments->take(4);
    $dobLabel = $student->date_of_birth ? \Carbon\Carbon::parse($student->date_of_birth)->format('M j, Y') : '—';
    $ageLabel = $student->date_of_birth ? \Carbon\Carbon::parse($student->date_of_birth)->age.' years' : '—';
@endphp

<div class="sis-profile">
    <ul class="nav sis-tabs" role="tablist">
        <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#sis_overview">Overview</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#sis_personal">Personal</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#sis_family">Family</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#sis_academics">Academics</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#sis_gpa">GPA history</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#sis_attendance">Attendance</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#sis_documents">Documents</a></li>
    </ul>

    <div class="tab-content sis-tab-content">
        {{-- Overview --}}
        <div class="tab-pane fade show active" id="sis_overview">
            <div class="sis-kpis">
                <article class="sis-kpi">
                    <span class="sis-kpi-label">GPA</span>
                    <strong>{{ $currentGPA ? number_format($currentGPA->gpa, 2) : '—' }}</strong>
                    <small>{{ $currentGPA?->academicYear->name ?? 'Latest recorded' }}</small>
                </article>
                <article class="sis-kpi">
                    <span class="sis-kpi-label">Attendance</span>
                    <strong>{{ number_format((float) $attendancePercentage, 1) }}%</strong>
                    <small>{{ $presentCount }} present · {{ $absentCount }} absent</small>
                </article>
                <article class="sis-kpi">
                    <span class="sis-kpi-label">Subjects</span>
                    <strong>{{ $enrollments->count() }}</strong>
                    <small>Currently enrolled</small>
                </article>
                <article class="sis-kpi">
                    <span class="sis-kpi-label">Documents</span>
                    <strong>{{ $documents->count() }}</strong>
                    <small>Enrollment files</small>
                </article>
            </div>

            <div class="sis-meta-strip">
                <div><span>Student number</span><strong>{{ $student->studentNumber() }}</strong></div>
                <div><span>Grade / section</span><strong>{{ trim($gradeLevel.' '.($sectionName ?: '')) ?: '—' }}</strong></div>
                <div><span>Academic year</span><strong>{{ $academicYearName ?: '—' }}</strong></div>
                <div><span>Term</span><strong>{{ $semesterName ?: '—' }}</strong></div>
            </div>

            <div class="sis-overview-grid">
                <section class="sis-card">
                    <div class="sis-card-head">
                        <h3>Current subjects</h3>
                    <a href="#sis_academics" class="sis-link js-sis-tab">View all</a>
                    </div>
                    @forelse($previewSubjects as $enrollment)
                        <div class="sis-row">
                            <div>
                                <strong>{{ $enrollment->subject->subject_name ?? 'Subject' }}</strong>
                                <p>{{ $enrollment->academicYear->name ?? '—' }} · {{ $enrollment->semester->name ?? '—' }}</p>
                            </div>
                            <span class="sis-badge is-ok">{{ ucfirst($enrollment->status) }}</span>
                        </div>
                    @empty
                        <p class="sis-empty">No active subject enrollments yet.</p>
                    @endforelse
                    @if($enrollments->count() > $previewSubjects->count())
                        <p class="sis-more">+{{ $enrollments->count() - $previewSubjects->count() }} more on Academics</p>
                    @endif
                </section>

                <section class="sis-card">
                    <div class="sis-card-head">
                        <h3>Recent grades</h3>
                        <a href="{{ route('student.grades') }}" class="sis-link">View details</a>
                    </div>
                    @forelse($recentGrades as $grade)
                        <div class="sis-row">
                            <div>
                                <strong>{{ $grade->subject->subject_name ?? 'Subject' }}</strong>
                                <p>{{ $grade->quarter ?: ($grade->academicYear->name ?? '—') }}</p>
                            </div>
                            <span class="sis-badge {{ ($grade->percentage ?? 0) >= 75 ? 'is-ok' : 'is-low' }}">
                                {{ number_format((float) $grade->percentage, 1) }}%
                            </span>
                        </div>
                    @empty
                        <p class="sis-empty">No grades posted yet.</p>
                    @endforelse
                </section>
            </div>

            <section class="sis-card">
                <div class="sis-card-head">
                    <h3>Attendance summary</h3>
                    <a href="{{ route('attendance.student') }}" class="sis-link">View details</a>
                </div>
                <div class="sis-mini-stats">
                    <div><strong>{{ $totalAttendance }}</strong><span>Records</span></div>
                    <div><strong>{{ $presentCount }}</strong><span>Present</span></div>
                    <div><strong>{{ $lateCount }}</strong><span>Late</span></div>
                    <div><strong>{{ $absentCount }}</strong><span>Absent</span></div>
                    <div><strong>{{ $excusedCount }}</strong><span>Excused</span></div>
                    <div><strong>{{ number_format((float) $attendancePercentage, 1) }}%</strong><span>Rate</span></div>
                </div>
            </section>
        </div>

        {{-- Personal --}}
        <div class="tab-pane fade" id="sis_personal">
            <section class="sis-card">
                <div class="sis-card-head">
                    <h3>Personal information</h3>
                </div>
                <dl class="sis-dl">
                    <div><dt>Full name</dt><dd>{{ $student->full_name }}</dd></div>
                    <div><dt>Student number</dt><dd>{{ $student->studentNumber() }}</dd></div>
                    <div><dt>Account ID</dt><dd>{{ $student->accountId() ?: '—' }}</dd></div>
                    <div><dt>Date of birth</dt><dd>{{ $dobLabel }}</dd></div>
                    <div><dt>Age</dt><dd>{{ $ageLabel }}</dd></div>
                    <div><dt>Gender</dt><dd>{{ $student->gender ?: '—' }}</dd></div>
                    <div><dt>Email</dt><dd>{{ $student->email ?: '—' }}</dd></div>
                    <div><dt>Phone</dt><dd>{{ $student->phone_number ?: '—' }}</dd></div>
                    <div><dt>Address</dt><dd>{{ $student->address ?: '—' }}</dd></div>
                    <div><dt>Blood group</dt><dd>{{ $student->blood_group ?: '—' }}</dd></div>
                    <div><dt>Religion</dt><dd>{{ $student->religion ?: '—' }}</dd></div>
                    <div><dt>Roll</dt><dd>{{ $student->roll ?: '—' }}</dd></div>
                    <div><dt>Previous school</dt><dd>{{ $student->previous_school ?: '—' }}</dd></div>
                    <div><dt>Enrollment status</dt>
                        <dd>
                            @if($student->enrollment_status === 'active')
                                <span class="sis-badge is-ok">Active</span>
                            @elseif($student->enrollment_status === 'graduated')
                                <span class="sis-badge is-info">Graduated</span>
                            @else
                                <span class="sis-badge">{{ $student->enrollment_status ? ucfirst($student->enrollment_status) : '—' }}</span>
                            @endif
                        </dd>
                    </div>
                    <div><dt>Year level</dt><dd>{{ $student->year_level ?: '—' }}</dd></div>
                </dl>
            </section>
        </div>

        {{-- Family --}}
        <div class="tab-pane fade" id="sis_family">
            <div class="sis-overview-grid">
                <section class="sis-card">
                    <div class="sis-card-head"><h3>Parent / guardian</h3></div>
                    <dl class="sis-dl">
                        <div><dt>Name</dt><dd>{{ $student->parent_name ?: '—' }}</dd></div>
                        <div><dt>Relationship</dt><dd>{{ $student->parent_relationship ?: '—' }}</dd></div>
                        <div><dt>Email</dt><dd>{{ $student->parent_email ?: '—' }}</dd></div>
                        <div><dt>Phone</dt><dd>{{ $student->parent_phone ?: '—' }}</dd></div>
                    </dl>
                </section>
                <section class="sis-card">
                    <div class="sis-card-head"><h3>Emergency contact</h3></div>
                    <dl class="sis-dl">
                        <div><dt>Name</dt><dd>{{ $student->emergency_contact_name ?: '—' }}</dd></div>
                        <div><dt>Phone</dt><dd>{{ $student->emergency_contact_phone ?: '—' }}</dd></div>
                    </dl>
                </section>
            </div>
        </div>

        {{-- Academics --}}
        <div class="tab-pane fade" id="sis_academics">
            <section class="sis-card">
                <div class="sis-card-head"><h3>Placement</h3></div>
                <dl class="sis-dl">
                    <div><dt>Section</dt><dd>{{ $sectionName ?: '—' }}</dd></div>
                    <div><dt>Grade level</dt><dd>{{ $gradeLevel ?: '—' }}</dd></div>
                    <div><dt>Academic year</dt><dd>{{ $academicYearName ?: '—' }}</dd></div>
                    <div><dt>Term</dt><dd>{{ $semesterName ?: '—' }}</dd></div>
                    <div><dt>Previous school</dt><dd>{{ $student->previous_school ?: '—' }}</dd></div>
                </dl>
            </section>

            <section class="sis-card">
                <div class="sis-card-head">
                    <h3>All current subjects ({{ $enrollments->count() }})</h3>
                    <a href="{{ route('dashboard') }}" class="sis-link">Open classes</a>
                </div>
                @if($enrollments->isEmpty())
                    <p class="sis-empty">No active subject enrollments found.</p>
                @else
                    <div class="table-responsive">
                        <table class="sis-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Subject</th>
                                    <th>Code</th>
                                    <th>Academic year</th>
                                    <th>Term</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($enrollments as $index => $enrollment)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $enrollment->subject->subject_name ?? '—' }}</td>
                                        <td>{{ $enrollment->subject->subject_code ?? $enrollment->subject->subject_id ?? '—' }}</td>
                                        <td>{{ $enrollment->academicYear->name ?? '—' }}</td>
                                        <td>{{ $enrollment->semester->name ?? '—' }}</td>
                                        <td><span class="sis-badge is-ok">{{ ucfirst($enrollment->status) }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>

            <section class="sis-card">
                <div class="sis-card-head"><h3>Promotion history</h3></div>
                @if($promotions->isEmpty())
                    <p class="sis-empty">No promotion records yet.</p>
                @else
                    <div class="table-responsive">
                        <table class="sis-table">
                            <thead>
                                <tr>
                                    <th>From</th>
                                    <th>To</th>
                                    <th>Status</th>
                                    <th>GPA</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($promotions as $promotion)
                                    <tr>
                                        <td>{{ $promotion->from_year_level }}</td>
                                        <td>{{ $promotion->to_year_level }}</td>
                                        <td>
                                            <span class="sis-badge">{{ ucfirst($promotion->promotion_status) }}</span>
                                        </td>
                                        <td>{{ $promotion->final_gpa ? number_format($promotion->final_gpa, 2) : '—' }}</td>
                                        <td>{{ $promotion->promotion_date ? \Carbon\Carbon::parse($promotion->promotion_date)->format('M j, Y') : '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
        </div>

        {{-- GPA --}}
        <div class="tab-pane fade" id="sis_gpa">
            <section class="sis-card">
                <div class="sis-card-head">
                    <h3>GPA history</h3>
                    <a href="{{ route('analytics.student-dashboard') }}" class="sis-link">View analytics</a>
                </div>
                @if($gpaRows->isEmpty())
                    <p class="sis-empty">No GPA records yet.</p>
                @else
                    <div class="table-responsive">
                        <table class="sis-table">
                            <thead>
                                <tr>
                                    <th>Academic year</th>
                                    <th>Term</th>
                                    <th>GPA</th>
                                    <th>Ranking</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($gpaRows as $gpa)
                                    <tr>
                                        <td>{{ $gpa->academicYear->name ?? '—' }}</td>
                                        <td>{{ $gpa->semester->name ?? '—' }}</td>
                                        <td><strong>{{ number_format((float) $gpa->gpa, 2) }}</strong></td>
                                        <td>{{ $gpa->ranking ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
        </div>

        {{-- Attendance --}}
        <div class="tab-pane fade" id="sis_attendance">
            <section class="sis-card">
                <div class="sis-card-head">
                    <h3>Detailed attendance</h3>
                    <a href="{{ route('attendance.student') }}" class="sis-link">Open attendance page</a>
                </div>
                <div class="sis-mini-stats">
                    <div><strong>{{ $totalAttendance }}</strong><span>Total records</span></div>
                    <div><strong>{{ $presentCount }}</strong><span>Present</span></div>
                    <div><strong>{{ $lateCount }}</strong><span>Late</span></div>
                    <div><strong>{{ $absentCount }}</strong><span>Absent</span></div>
                    <div><strong>{{ $excusedCount }}</strong><span>Excused</span></div>
                    <div><strong>{{ number_format((float) $attendancePercentage, 1) }}%</strong><span>Attendance rate</span></div>
                </div>
                <p class="sis-hint">Present and late count as attended. Excused days are not counted against the rate. Daily records are on Attendance.</p>
            </section>
        </div>

        {{-- Documents --}}
        <div class="tab-pane fade" id="sis_documents">
            <section class="sis-card">
                <div class="sis-card-head"><h3>Enrollment documents</h3></div>
                <div class="table-responsive">
                    <table class="sis-table">
                        <thead>
                            <tr>
                                <th>Document type</th>
                                <th>File name</th>
                                <th>Status</th>
                                <th>Uploaded</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($documentChecklist as $item)
                                @php
                                    $doc = $item['document'];
                                    $status = match($doc?->status) {
                                        'verified' => 'Approved',
                                        'rejected' => 'Not approved',
                                        'pending' => 'Pending review',
                                        default => 'Need documents',
                                    };
                                @endphp
                                <tr>
                                    <td>{{ \App\Models\EnrollmentDocument::DOCUMENT_TYPES[$item['type']] ?? $item['type'] }}</td>
                                    <td>{{ $doc?->file_name ?? '—' }}</td>
                                    <td>
                                        <span class="sis-badge">{{ $status }}</span>
                                        @if($doc?->verification_notes)
                                            <small class="d-block text-muted mt-1">{{ $doc->verification_notes }}</small>
                                        @endif
                                    </td>
                                    <td>{{ optional($doc?->created_at)->format('M j, Y') ?: '—' }}</td>
                                </tr>
                            @endforeach
                            @foreach($additionalDocuments as $doc)
                                @php
                                    $status = match($doc->status) {
                                        'verified' => 'Approved',
                                        'rejected' => 'Not approved',
                                        'pending' => 'Pending review',
                                        default => ucfirst($doc->status),
                                    };
                                @endphp
                                <tr>
                                    <td>{{ \App\Models\EnrollmentDocument::DOCUMENT_TYPES[$doc->document_type] ?? $doc->document_type }}</td>
                                    <td>{{ $doc->file_name }}</td>
                                    <td>
                                        <span class="sis-badge">{{ $status }}</span>
                                        @if($doc->verification_notes)
                                            <small class="d-block text-muted mt-1">{{ $doc->verification_notes }}</small>
                                        @endif
                                    </td>
                                    <td>{{ optional($doc->created_at)->format('M j, Y') ?: '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</div>

@push('styles')
<style>
.sis-profile { margin-top: .25rem; }
.sis-tabs {
    display: flex; flex-wrap: wrap; gap: .35rem; padding: 0; margin: 0 0 1rem;
    border-bottom: 1px solid #e8eaed; list-style: none;
}
.sis-tabs .nav-link {
    display: inline-flex; padding: .55rem .85rem; color: #6b7280; font-weight: 600; font-size: .88rem;
    border: none; border-bottom: 2px solid transparent; background: transparent; text-decoration: none;
}
.sis-tabs .nav-link:hover { color: #d35400; }
.sis-tabs .nav-link.active { color: #e67e22; border-bottom-color: #e67e22; }
.sis-kpis { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: .85rem; margin-bottom: 1rem; }
.sis-kpi {
    background: #fff; border: 1px solid #e8eaed; border-radius: 12px; padding: .9rem 1rem;
    box-shadow: 0 1px 3px rgba(16,24,40,.05);
}
.sis-kpi-label { display: block; font-size: .72rem; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; color: #6b7280; }
.sis-kpi strong { display: block; font-size: 1.45rem; margin-top: .2rem; color: #1f2937; }
.sis-kpi small { color: #6b7280; font-size: .78rem; }
.sis-meta-strip {
    display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: .75rem;
    background: #fff4eb; border: 1px solid #f0d3bb; border-radius: 12px; padding: .85rem 1rem; margin-bottom: 1rem;
}
.sis-meta-strip span { display: block; font-size: .72rem; color: #9a3412; font-weight: 600; }
.sis-meta-strip strong { color: #1f2937; }
.sis-overview-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem; }
.sis-card {
    background: #fff; border: 1px solid #e8eaed; border-radius: 12px; padding: 1rem 1.1rem;
    box-shadow: 0 1px 3px rgba(16,24,40,.05); margin-bottom: 1rem;
}
.sis-overview-grid .sis-card { margin-bottom: 0; }
.sis-card-head { display: flex; justify-content: space-between; align-items: center; gap: .75rem; margin-bottom: .75rem; }
.sis-card-head h3 { margin: 0; font-size: 1rem; font-weight: 700; }
.sis-link { color: #e67e22; font-size: .82rem; font-weight: 700; text-decoration: none; }
.sis-link:hover { color: #d35400; text-decoration: underline; }
.sis-row { display: flex; justify-content: space-between; gap: .75rem; align-items: center; padding: .55rem 0; border-bottom: 1px solid #f1f3f5; }
.sis-row:last-child { border-bottom: none; }
.sis-row p { margin: .15rem 0 0; font-size: .8rem; color: #6b7280; }
.sis-badge { display: inline-flex; border-radius: 999px; padding: .15rem .55rem; font-size: .72rem; font-weight: 700; background: #f3f4f6; color: #374151; }
.sis-badge.is-ok { background: #ecfdf5; color: #047857; }
.sis-badge.is-low { background: #fef2f2; color: #b91c1c; }
.sis-badge.is-info { background: #eff6ff; color: #1d4ed8; }
.sis-empty, .sis-more, .sis-hint { margin: 0; color: #6b7280; font-size: .9rem; }
.sis-more, .sis-hint { margin-top: .65rem; }
.sis-mini-stats { display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: .65rem; text-align: center; }
.sis-mini-stats strong { display: block; font-size: 1.15rem; }
.sis-mini-stats span { font-size: .75rem; color: #6b7280; }
.sis-dl { display: grid; grid-template-columns: 1fr 1fr; gap: .35rem 1.25rem; margin: 0; }
.sis-dl div { padding: .45rem 0; border-bottom: 1px solid #f1f3f5; }
.sis-dl dt { font-size: .72rem; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: .03em; }
.sis-dl dd { margin: .15rem 0 0; color: #1f2937; }
.sis-table { width: 100%; border-collapse: collapse; }
.sis-table th { font-size: .72rem; text-transform: uppercase; letter-spacing: .03em; color: #6b7280; text-align: left; padding: .5rem 0; border-bottom: 1px solid #e8eaed; }
.sis-table td { padding: .7rem 0; border-bottom: 1px solid #f1f3f5; font-size: .9rem; }
@media (max-width: 992px) {
    .sis-kpis, .sis-meta-strip, .sis-overview-grid, .sis-dl, .sis-mini-stats { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 640px) {
    .sis-kpis, .sis-meta-strip, .sis-overview-grid, .sis-dl, .sis-mini-stats { grid-template-columns: 1fr; }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('click', function (event) {
    const link = event.target.closest('.js-sis-tab');
    if (!link) return;
    event.preventDefault();
    const id = (link.getAttribute('href') || '').replace('#', '');
    const trigger = document.querySelector('.sis-tabs a[href="#' + id + '"]');
    if (trigger && window.bootstrap) {
        window.bootstrap.Tab.getOrCreateInstance(trigger).show();
    }
});
</script>
@endpush
