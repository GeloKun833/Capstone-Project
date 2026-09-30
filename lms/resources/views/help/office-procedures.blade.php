@extends('layouts.master')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Office Procedures</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('help') }}">Help</a></li>
                        <li class="breadcrumb-item active">Office Procedures</li>
                    </ul>
                </div>
                <div class="col-auto">
                    <a href="{{ route('help') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Help Center</a>
                </div>
            </div>
        </div>

        <div class="alert alert-info" role="note">
            For official fees, office hours, required documents, or deadlines not shown here, confirm the current information with the Registrar.
        </div>

        <div class="row g-3">
            <div class="col-lg-6">
                <section class="card h-100">
                    <div class="card-header"><h5 class="mb-0">Enrollment</h5></div>
                    <div class="card-body">
                        <ol class="mb-3">
                            <li>Open the Enrollment Portal and choose the application type that matches your situation.</li>
                            <li>Complete the application and submit the requested information and documents.</li>
                            <li>Keep the application number shown after submission.</li>
                            <li>Use <strong>Track Application</strong> with the application number and application email to check its status.</li>
                            <li>If the status says <strong>Needs Documents</strong>, review the application status page and contact the Registrar if you need help.</li>
                        </ol>
                        <div class="d-flex flex-wrap gap-2">
                            <a class="btn btn-primary btn-sm" href="{{ route('enrollment.portal.index') }}"><i class="fas fa-file-signature me-1"></i> Enrollment Portal</a>
                            <a class="btn btn-outline-primary btn-sm" href="{{ route('enrollment.portal.status') }}"><i class="fas fa-magnifying-glass me-1"></i> Track Application</a>
                        </div>
                    </div>
                </section>
            </div>

            <div class="col-lg-6">
                <section class="card h-100">
                    <div class="card-header"><h5 class="mb-0">Student Records</h5></div>
                    <div class="card-body">
                        @if($role === 'Parent')
                            <p>Select a linked child from <strong>My Children</strong> to review their academic information.</p>
                            <ul class="mb-3">
                                <li>Open the child profile for current section and enrollment details.</li>
                                <li>Use the child’s Grades, Attendance, Report Card, and Class Schedule pages to review records.</li>
                            </ul>
                        @else
                            <p>Use your student account to review your active classes, grades, attendance, and academic calendar.</p>
                            <ul class="mb-3">
                                <li><strong>Classes and subjects:</strong> open My Classes.</li>
                                <li><strong>Grades and report cards:</strong> open Academic Records.</li>
                                <li><strong>Attendance:</strong> open Attendance to review recorded dates and summaries.</li>
                                <li><strong>Schedule:</strong> open My Schedule or Calendar &amp; Events.</li>
                            </ul>
                        @endif
                        <p class="mb-0">If a class, section, or record appears incorrect, contact the Registrar or school administrator so the record can be reviewed.</p>
                    </div>
                </section>
            </div>

            <div class="col-lg-6">
                <section class="card h-100">
                    <div class="card-header"><h5 class="mb-0">Attendance and Schedules</h5></div>
                    <div class="card-body">
                        <ul class="mb-0">
                            <li>Teachers record attendance for their assigned subject and section.</li>
                            <li>Students can review their own attendance; parents can review attendance for linked children.</li>
                            <li>Use the published class schedule and calendar for current meeting times and school events.</li>
                            <li>If an attendance entry or schedule looks wrong, contact the teacher or Registrar with the subject and date.</li>
                        </ul>
                    </div>
                </section>
            </div>

            <div class="col-lg-6">
                <section class="card h-100">
                    <div class="card-header"><h5 class="mb-0">Announcements and Teacher Consultations</h5></div>
                    <div class="card-body">
                        <ul class="mb-3">
                            <li>Check Announcements for school and class updates.</li>
                            @if($role === 'Student')
                                <li>To request a teacher consultation, open Consultations, choose an enrolled subject and assigned teacher, then suggest a date and time.</li>
                                <li>Watch the request status. The teacher will confirm, reschedule, or decline it.</li>
                            @elseif($role === 'Parent')
                                <li>For a question about a linked child, use Chat to contact the teacher or Registrar.</li>
                            @endif
                        </ul>
                        <div class="d-flex flex-wrap gap-2">
                            <a class="btn btn-outline-primary btn-sm" href="{{ route('announcements.index') }}"><i class="fas fa-bullhorn me-1"></i> Announcements</a>
                            @if($role === 'Student')
                                <a class="btn btn-outline-primary btn-sm" href="{{ route('student.consultations.index') }}"><i class="fas fa-comments me-1"></i> Consultations</a>
                            @elseif($role === 'Parent')
                                <a class="btn btn-outline-primary btn-sm" href="{{ route('chat.index') }}"><i class="fas fa-comments me-1"></i> Chat</a>
                            @endif
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>
@endsection