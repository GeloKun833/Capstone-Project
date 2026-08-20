@extends('layouts.enrollment-registrar')

@section('title', 'Enrollment Analytics')

@section('topbar-actions')
<a href="{{ route('enrollment.registrar.index') }}" class="ep-btn ep-btn-outline ep-btn-sm"><i class="fas fa-list"></i> Applications</a>
<a href="{{ route('enrollment.portal.index') }}" target="_blank" class="ep-btn ep-btn-primary ep-btn-sm"><i class="fas fa-external-link"></i> Portal</a>
@endsection

@section('content')
@php
    $approvalRate = $stats['total_applications'] > 0 ? round(($stats['approved'] / $stats['total_applications']) * 100, 1) : 0;
    $thisMonth = \App\Models\EnrollmentApplication::whereMonth('created_at', now()->month)->count();
@endphp

<div class="mb-4">
    <h1 class="ep-page-title">Enrollment Analytics</h1>
    <p class="ep-page-subtitle">Track application volume, approval rates, and trends across grade levels.</p>
</div>

<div class="ep-stat-grid mb-4">
    <div class="ep-stat-card">
        <div class="ep-stat-icon blue"><i class="fas fa-file-alt"></i></div>
        <div class="ep-stat-value">{{ $stats['total_applications'] }}</div>
        <div class="ep-stat-label">Total Applications</div>
    </div>
    <div class="ep-stat-card">
        <div class="ep-stat-icon amber"><i class="fas fa-clock"></i></div>
        <div class="ep-stat-value">{{ $stats['pending'] }}</div>
        <div class="ep-stat-label">Pending Review</div>
    </div>
    <div class="ep-stat-card">
        <div class="ep-stat-icon blue"><i class="fas fa-search"></i></div>
        <div class="ep-stat-value">{{ $stats['under_review'] }}</div>
        <div class="ep-stat-label">Under Review</div>
    </div>
    <div class="ep-stat-card">
        <div class="ep-stat-icon green"><i class="fas fa-check-circle"></i></div>
        <div class="ep-stat-value">{{ $stats['approved'] }}</div>
        <div class="ep-stat-label">Approved</div>
    </div>
    <div class="ep-stat-card">
        <div class="ep-stat-icon red"><i class="fas fa-times-circle"></i></div>
        <div class="ep-stat-value">{{ $stats['rejected'] }}</div>
        <div class="ep-stat-label">Rejected</div>
    </div>
    <div class="ep-stat-card">
        <div class="ep-stat-icon amber"><i class="fas fa-file-circle-plus"></i></div>
        <div class="ep-stat-value">{{ $stats['needs_documents'] }}</div>
        <div class="ep-stat-label">Needs Documents</div>
    </div>
    <div class="ep-stat-card">
        <div class="ep-stat-icon green"><i class="fas fa-percentage"></i></div>
        <div class="ep-stat-value">{{ $approvalRate }}%</div>
        <div class="ep-stat-label">Approval Rate</div>
    </div>
    <div class="ep-stat-card">
        <div class="ep-stat-icon blue"><i class="fas fa-calendar"></i></div>
        <div class="ep-stat-value">{{ $thisMonth }}</div>
        <div class="ep-stat-label">This Month</div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="ep-card h-100">
            <div class="ep-card-header"><h3><i class="fas fa-chart-pie me-2"></i>Status Distribution</h3></div>
            <div class="ep-card-body">
                <div class="ep-chart-wrap"><canvas id="statusChart"></canvas></div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="ep-card h-100">
            <div class="ep-card-header"><h3><i class="fas fa-chart-line me-2"></i>Applications by Month</h3></div>
            <div class="ep-card-body">
                <div class="ep-chart-wrap"><canvas id="monthlyChart"></canvas></div>
            </div>
        </div>
    </div>
</div>

<div class="ep-card mb-4">
    <div class="ep-card-header"><h3><i class="fas fa-layer-group me-2"></i>Applications by Grade Level</h3></div>
    <div class="ep-card-body p-0">
        <div class="ep-table-wrap">
            <table class="ep-table">
                <thead>
                    <tr>
                        <th>Grade Level</th>
                        <th>Total</th>
                        <th>Approved</th>
                        <th>Pending</th>
                        <th>Rejected</th>
                        <th>Approval Rate</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $gradeLevels = \App\Models\EnrollmentApplication::select('grade_level_applying_for')
                            ->selectRaw('COUNT(*) as total')
                            ->selectRaw('SUM(CASE WHEN status = "approved" THEN 1 ELSE 0 END) as approved')
                            ->selectRaw('SUM(CASE WHEN status = "pending" OR status = "under_review" THEN 1 ELSE 0 END) as pending')
                            ->selectRaw('SUM(CASE WHEN status = "rejected" THEN 1 ELSE 0 END) as rejected')
                            ->groupBy('grade_level_applying_for')
                            ->orderBy('grade_level_applying_for')
                            ->get();
                    @endphp
                    @forelse($gradeLevels as $grade)
                        <tr>
                            <td><strong>{{ $grade->grade_level_applying_for }}</strong></td>
                            <td>{{ $grade->total }}</td>
                            <td><span class="ep-chip ep-chip-approved">{{ $grade->approved }}</span></td>
                            <td><span class="ep-chip ep-chip-pending">{{ $grade->pending }}</span></td>
                            <td><span class="ep-chip ep-chip-rejected">{{ $grade->rejected }}</span></td>
                            <td>
                                @if($grade->total > 0)
                                    <span class="ep-chip ep-chip-review">{{ round(($grade->approved / $grade->total) * 100, 1) }}%</span>
                                @else
                                    <span class="ep-chip ep-chip-docs">0%</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">No grade level data yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="ep-card">
    <div class="ep-card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h3 class="mb-0"><i class="fas fa-clock-rotate-left me-2"></i>Recent Applications</h3>
        <a href="{{ route('enrollment.registrar.index') }}" class="ep-btn ep-btn-outline ep-btn-sm">View All</a>
    </div>
    <div class="ep-card-body p-0">
        <div class="ep-table-wrap">
            <table class="ep-table">
                <thead>
                    <tr>
                        <th>Application #</th>
                        <th>Student</th>
                        <th>Grade</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $recentApplications = \App\Models\EnrollmentApplication::with('reviewer')
                            ->orderBy('created_at', 'desc')
                            ->limit(10)
                            ->get();
                        $chipMap = ['pending'=>'ep-chip-pending','under_review'=>'ep-chip-review','approved'=>'ep-chip-approved','rejected'=>'ep-chip-rejected','needs_documents'=>'ep-chip-docs'];
                    @endphp
                    @foreach($recentApplications as $app)
                        <tr>
                            <td><strong>{{ $app->application_number }}</strong></td>
                            <td>{{ $app->full_name }}</td>
                            <td>{{ $app->grade_level_applying_for }}</td>
                            <td><span class="ep-chip {{ $chipMap[$app->status] ?? 'ep-chip-docs' }}">{{ ucfirst(str_replace('_', ' ', $app->status)) }}</span></td>
                            <td>{{ $app->created_at->format('M d, Y') }}</td>
                            <td>
                                <a href="{{ route('enrollment.registrar.show', $app->id) }}" class="ep-btn ep-btn-primary ep-btn-sm">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const isDark = document.body.classList.contains('ep-dark');
    const gridColor = isDark ? 'rgba(148,163,184,.2)' : 'rgba(0,0,0,.06)';
    const textColor = isDark ? '#94A3B8' : '#64748B';

    new Chart(document.getElementById('statusChart').getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: ['Pending', 'Under Review', 'Approved', 'Rejected', 'Needs Documents'],
            datasets: [{
                data: [
                    {{ $stats['pending'] }},
                    {{ $stats['under_review'] }},
                    {{ $stats['approved'] }},
                    {{ $stats['rejected'] }},
                    {{ $stats['needs_documents'] }}
                ],
                backgroundColor: ['#F59E0B', '#3B82F6', '#22C55E', '#EF4444', '#94A3B8'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', labels: { color: textColor, padding: 16 } } }
        }
    });

    @php
        $monthlyData = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $count = \App\Models\EnrollmentApplication::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
            $monthlyData[] = ['month' => $date->format('M Y'), 'count' => $count];
        }
    @endphp

    new Chart(document.getElementById('monthlyChart').getContext('2d'), {
        type: 'line',
        data: {
            labels: [@foreach($monthlyData as $data)'{{ $data['month'] }}',@endforeach],
            datasets: [{
                label: 'Applications',
                data: [@foreach($monthlyData as $data){{ $data['count'] }},@endforeach],
                borderColor: '#2563EB',
                backgroundColor: 'rgba(37,99,235,.12)',
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointBackgroundColor: '#2563EB'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { color: gridColor }, ticks: { color: textColor } },
                y: { beginAtZero: true, grid: { color: gridColor }, ticks: { color: textColor, stepSize: 1 } }
            }
        }
    });
});
</script>
@endsection
