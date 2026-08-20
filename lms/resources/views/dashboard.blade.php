@extends('layouts.master')
@section('content')


<div class="page-wrapper">
    <div class="content container-fluid">
        @if($user->role_name === 'Admin')
            @include('partials.admin_dashboard')
        @elseif($user->role_name === 'Teacher')
            @include('partials.teacher_dashboard')
        @elseif($user->role_name === 'Student')
            @include('partials.student_dashboard')
        @elseif($user->role_name === 'Parent')
            @include('partials.parent_dashboard')
        @elseif($user->role_name === 'Registrar')
            @include('partials.registrar_dashboard')
        @else
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <div class="mb-4">
                                <i class="fas fa-exclamation-triangle text-warning" style="font-size: 4rem; opacity: 0.6;"></i>
                            </div>
                            <h4 class="fw-bold text-dark mb-3">Role Not Recognized</h4>
                            <p class="text-muted mb-4">Your user role is not recognized. Please contact the administrator.</p>
                            <a href="{{ route('home') }}" class="btn btn-primary">
                                <i class="fas fa-home me-2"></i>Return to Home
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

@endsection

{{-- Must load after jQuery + ApexCharts in layouts/master --}}
@section('script')
@if($user->role_name === 'Admin' && isset($admin['performanceData'], $admin['studentsChartData']))
<script>
(function () {
    'use strict';

    if (typeof ApexCharts === 'undefined') {
        console.warn('ApexCharts is not loaded; admin dashboard charts skipped.');
        return;
    }

    var performanceEl = document.querySelector('#apexcharts-area');
    if (performanceEl) {
        new ApexCharts(performanceEl, {
            chart: {
                height: 350,
                type: 'line',
                toolbar: { show: false }
            },
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 3 },
            series: [
                {
                    name: 'Expected Performance',
                    color: '#3D5EE1',
                    data: {!! json_encode($admin['performanceData']['teacherData'] ?? []) !!}
                },
                {
                    name: 'Student Average',
                    color: '#70C4CF',
                    data: {!! json_encode($admin['performanceData']['studentData'] ?? []) !!}
                }
            ],
            xaxis: {
                categories: {!! json_encode($admin['performanceData']['months'] ?? []) !!}
            },
            yaxis: {
                title: { text: 'Average Grade (%)' },
                min: 0,
                max: 100
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        var n = (val === null || val === undefined || isNaN(val)) ? 0 : Number(val);
                        return n.toFixed(1) + '%';
                    }
                }
            },
            legend: {
                position: 'top',
                horizontalAlign: 'right'
            }
        }).render();
    }

    var studentsEl = document.querySelector('#bar');
    if (studentsEl) {
        new ApexCharts(studentsEl, {
            chart: {
                type: 'bar',
                height: 350,
                width: '100%',
                stacked: false,
                toolbar: { show: false }
            },
            dataLabels: { enabled: true },
            plotOptions: {
                bar: {
                    columnWidth: '55%',
                    endingShape: 'rounded',
                    dataLabels: { position: 'top' }
                }
            },
            stroke: {
                show: true,
                width: 2,
                colors: ['transparent']
            },
            series: [
                {
                    name: 'Boys',
                    color: '#70C4CF',
                    data: {!! json_encode($admin['studentsChartData']['boysData'] ?? []) !!}
                },
                {
                    name: 'Girls',
                    color: '#3D5EE1',
                    data: {!! json_encode($admin['studentsChartData']['girlsData'] ?? []) !!}
                }
            ],
            xaxis: {
                categories: {!! json_encode($admin['studentsChartData']['labels'] ?? []) !!},
                labels: { rotate: -45, rotateAlways: false }
            },
            yaxis: {
                title: { text: 'Number of Students' },
                labels: {
                    formatter: function (val) {
                        return Math.floor(val || 0);
                    }
                }
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return (val || 0) + ' students';
                    }
                }
            },
            legend: {
                position: 'top',
                horizontalAlign: 'right'
            },
            fill: { opacity: 1 }
        }).render();
    }
})();
</script>
@endif
@endsection
