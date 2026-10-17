@extends('layouts.master')

@section('content')
<div class="page-wrapper">
    <div class="content container-fluid dir-page dir-report-view">
        <div class="page-header">
            <div class="row align-items-start">
                <div class="col">
                    <a class="dir-report-back" href="{{ route('reports.index') }}">
                        <i class="fas fa-arrow-left" aria-hidden="true"></i> Reports &amp; Documents
                    </a>
                    <h3 class="page-title mb-1">{{ $report['title'] }}</h3>
                    <p class="dir-subtitle">{{ $note }}</p>
                </div>
                <div class="col-auto text-end">
                    <ul class="breadcrumb justify-content-end mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
                        <li class="breadcrumb-item active">View Report</li>
                    </ul>
                    <div class="dir-report-year">
                        <span>Academic year</span>
                        <strong>{{ $year->name ?? 'No current academic year' }}</strong>
                    </div>
                </div>
            </div>
        </div>

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @if($loadError)
            <div class="alert alert-danger d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span>{{ $note }}</span>
                <a class="btn btn-sm btn-outline-danger" href="{{ request()->fullUrl() }}">Try again</a>
            </div>
        @endif

        @if($summary)
            <div class="dir-report-kpis">
                @foreach($summary as $label => $value)
                    <article class="dir-report-kpi">
                        <span>{{ $label }}</span>
                        <strong>{{ is_numeric($value) ? number_format($value) : $value }}</strong>
                    </article>
                @endforeach
            </div>
        @endif

        <form method="GET" action="{{ route('reports.school.show', $report['key']) }}" class="dir-card dir-report-toolbar">
            <div class="row g-3 align-items-end">
                <div class="col-lg-6">
                    <label class="form-label" for="reportSearch">Search</label>
                    <input id="reportSearch" class="form-control" type="search" name="q" value="{{ $search }}" maxlength="80" placeholder="Search names, grades, or sections">
                </div>
                <div class="col-md-6 col-lg-3">
                    <label class="form-label" for="reportSection">Section</label>
                    <select id="reportSection" class="form-select" name="section_id">
                        <option value="">All sections</option>
                        @foreach($sections as $section)
                            <option value="{{ $section->id }}" @selected((int) $sectionId === (int) $section->id)>
                                {{ $section->grade_level ? $section->grade_level.' — ' : '' }}{{ $section->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 col-lg-3 d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-primary dir-btn">
                        <i class="fas fa-search me-1"></i> Search
                    </button>
                    @unless($loadError)
                        @php
                            $downloadQuery = array_filter(
                                request()->only(['q', 'section_id']),
                                fn ($value) => $value !== null && $value !== ''
                            );
                        @endphp
                        <a class="btn btn-outline-primary dir-btn" href="{{ route('reports.school.download', ['report' => $report['key']]) }}{{ $downloadQuery ? '?'.http_build_query($downloadQuery) : '' }}">
                            <i class="fas fa-download me-1"></i> Download
                        </a>
                    @endunless
                    @if($search !== '' || $sectionId)
                        <a class="btn btn-outline-secondary dir-btn" href="{{ route('reports.school.show', $report['key']) }}">Clear</a>
                    @endif
                </div>
            </div>
            <p class="dir-report-meta">
                @if($rows->total())
                    Showing {{ number_format($rows->firstItem()) }}–{{ number_format($rows->lastItem()) }} of {{ number_format($rows->total()) }} {{ $rows->total() === 1 ? 'row' : 'rows' }}
                @else
                    0 rows
                @endif
            </p>
        </form>

        <section class="dir-card">
            <div class="table-responsive">
                <table class="table dir-table mb-0">
                    <thead>
                        <tr>
                            @forelse($columns as $column)
                                <th>{{ $column }}</th>
                            @empty
                                <th>Report</th>
                            @endforelse
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $row)
                            <tr>
                                @foreach($row as $cell)
                                    <td>{{ $cell }}</td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ max(1, count($columns)) }}">
                                    <div class="dir-empty">
                                        <i class="fas fa-folder-open d-block"></i>
                                        @if($search !== '' || $sectionId)
                                            <p class="mb-2">No rows match this search.</p>
                                            <a class="btn btn-outline-secondary dir-btn" href="{{ route('reports.school.show', $report['key']) }}">Clear filters</a>
                                        @else
                                            <p class="mb-0">No records for this report.</p>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($rows->hasPages())
                <div class="dir-report-pager">
                    {{ $rows->links('pagination.bootstrap-limited') }}
                </div>
            @endif
        </section>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/directory-modern.css') }}?v=20261018a">
@endpush

@push('scripts')
<script>
document.getElementById('reportSection')?.addEventListener('change', function () {
    this.form.submit();
});
</script>
@endpush
