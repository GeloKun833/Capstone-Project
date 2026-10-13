@php
    use App\Services\ReportCardService;
    $attendanceMonths = $attendanceMonths ?? ReportCardService::ATTENDANCE_MONTHS;
    $attendanceRows = $attendanceRows ?? [
        'school_days' => [],
        'present' => [],
        'late' => [],
        'excused' => [],
        'absent' => [],
    ];
    $fullName = trim(($student->last_name ?? '').', '.($student->first_name ?? '').' '.($student->middle_name ?? ''));
    $reportSection = $reportSection ?? $student->sections->first();
    $sectionName = optional($reportSection)->name;
    $gradeLevel = $gradeLevel ?? optional($reportSection)->grade_level;
    $descriptive = $descriptive ?? \App\Support\DescriptiveScale::isNurseryOrKindergarten($gradeLevel);
@endphp

<div class="report-sheet">
    <div class="school-header">
        <p class="school-name">Panorama Montessori School, Inc.</p>
        <p class="doc-title">Learner&rsquo;s Progress Report</p>
    </div>

    <div class="learner-meta">
        <div><strong>Name:</strong> {{ $fullName }}</div>
        <div><strong>School Year:</strong> {{ $academicYear->name ?? '—' }}</div>
        @if($gradeLevel)
            <div><strong>Grade:</strong> {{ $gradeLevel }}</div>
        @endif
        @if($sectionName)
            <div><strong>Section:</strong> {{ $sectionName }}</div>
        @endif
        @if(!empty($student->admission_id))
            <div><strong>Student ID:</strong> {{ $student->admission_id }}</div>
        @endif
    </div>

    <div class="two-col">
        {{-- LEFT: Observed Values + Attendance --}}
        <div class="col">
            <h3>Report on Learner&rsquo;s Observed Values</h3>
            <table class="rc">
                <thead>
                    <tr>
                        <th rowspan="2" style="width:16%">Core Values</th>
                        <th rowspan="2">Behavior Statements</th>
                        <th colspan="4">Quarter</th>
                    </tr>
                    <tr>
                        <th style="width:7%">1</th>
                        <th style="width:7%">2</th>
                        <th style="width:7%">3</th>
                        <th style="width:7%">4</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($observedGrouped as $core => $items)
                        @foreach($items as $i => $indicator)
                            @php $rating = $observedRatings->get($indicator->id); @endphp
                            <tr>
                                @if($i === 0)
                                    <td class="core" rowspan="{{ $items->count() }}">{{ $loop->parent->iteration }}. {{ $core }}</td>
                                @endif
                                <td class="left">{{ $indicator->statement }}</td>
                                @foreach(['quarter_1','quarter_2','quarter_3','quarter_4'] as $qf)
                                    <td class="center">{{ optional($rating)->{$qf} ?: '' }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    @empty
                        <tr><td colspan="6" class="center">No observed values recorded.</td></tr>
                    @endforelse
                </tbody>
            </table>

            <div class="marking">
                <div><strong>Marking:</strong></div>
                <div><strong>AO</strong> Always Observed</div>
                <div><strong>SO</strong> Sometimes Observed</div>
                <div><strong>RO</strong> Rarely Observed</div>
            </div>

            <div class="att-title">Attendance Record</div>
            <table class="rc">
                <thead>
                    <tr>
                        <th style="width:22%"></th>
                        @foreach($attendanceMonths as $label)
                            <th>{{ $label }}</th>
                        @endforeach
                        <th>TOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach([
                        'school_days' => 'No. of school days',
                        'present' => 'No. of days present',
                        'late' => 'No. of days late',
                        'excused' => 'No. of days excused',
                        'absent' => 'No. of days absent',
                    ] as $key => $label)
                        <tr>
                            <td class="left">{{ $label }}</td>
                            @foreach(array_keys($attendanceMonths) as $monthNum)
                                @php $val = (int) ($attendanceRows[$key][$monthNum] ?? 0); @endphp
                                <td class="center">{{ $val > 0 ? $val : '' }}</td>
                            @endforeach
                            @php $tot = (int) ($attendanceRows[$key]['total'] ?? 0); @endphp
                            <td class="center"><strong>{{ $tot > 0 ? $tot : '' }}</strong></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- RIGHT: Learning Progress + Scale --}}
        <div class="col">
            <h3>Report on Learning Progress and Achievement</h3>
            <table class="rc">
                <thead>
                    <tr>
                        <th rowspan="2" style="width:34%">Learning Areas</th>
                        <th colspan="4">Quarter</th>
                        <th rowspan="2" style="width:11%">Final Grade</th>
                        <th rowspan="2" style="width:12%">Remarks</th>
                    </tr>
                    <tr>
                        <th style="width:8%">1</th>
                        <th style="width:8%">2</th>
                        <th style="width:8%">3</th>
                        <th style="width:8%">4</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($learningRows as $row)
                        @php
                            $subjName = $row->subject->subject_name ?? 'N/A';
                            $isChild = ReportCardService::isMapehChild($subjName);
                            $isParent = strcasecmp(trim($subjName), 'MAPEH') === 0;
                            $remark = $row->remarks ?: ReportCardService::remarkForScore(
                                $row->final_grade !== null ? (float) $row->final_grade : null
                            );
                            $latestLetter = $descriptive ? $row->latestLevel() : null;
                            $latestMeta = $latestLetter ? \App\Support\DescriptiveScale::get($latestLetter) : null;
                        @endphp
                        <tr>
                            <td class="left {{ $isChild ? 'indent' : '' }}">
                                @if($isParent)<strong>{{ $subjName }}</strong>@else{{ $subjName }}@endif
                            </td>
                            @foreach([1, 2, 3, 4] as $quarter)
                                @php
                                    if ($descriptive) {
                                        $cell = $row->presentQuarter($quarter);
                                        $cell = $cell === '—' ? '' : $cell;
                                    } else {
                                        $value = $row->{'quarter_'.$quarter};
                                        $cell = ($value === null || $value === '') ? '' : number_format((float) $value, 0);
                                    }
                                @endphp
                                <td class="center" title="{{ $descriptive ? $row->quarterCaption($quarter) : '' }}">{{ $cell }}</td>
                            @endforeach
                            <td class="center">
                                @if($descriptive)
                                    {{ $latestLetter ?? '' }}
                                @else
                                    {{ $row->final_grade !== null ? number_format((float) $row->final_grade, 0) : '' }}
                                @endif
                            </td>
                            <td class="center">
                                @if($descriptive)
                                    {{ $latestMeta ? $latestMeta['english'].' ('.$latestMeta['filipino'].')' : '' }}
                                @else
                                    {{ $row->final_grade !== null ? $remark : '' }}
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="center">No grades recorded.</td></tr>
                    @endforelse

                    @if($learningRows->isNotEmpty() && ! $descriptive)
                        <tr>
                            <td class="ga-label">General Average</td>
                            @foreach(['q1','q2','q3','q4','final'] as $k)
                                <td class="center">
                                    <strong>{{ isset($generalAverages[$k]) && $generalAverages[$k] !== null ? number_format($generalAverages[$k], 0) : '' }}</strong>
                                </td>
                            @endforeach
                            <td class="center">
                                <strong>{{ ReportCardService::remarkForScore($generalAverages['final'] ?? null) }}</strong>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>

            @if($descriptive)
                <h3 style="margin-top:14px;">Descriptive Scale</h3>
                <table class="rc scale">
                    <thead>
                        <tr>
                            <th style="width:12%">Letter</th>
                            <th style="width:34%">Descriptor</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(\App\Support\DescriptiveScale::LEVELS as $letter => $meta)
                            <tr>
                                <td class="center"><strong>{{ $letter }}</strong></td>
                                <td>{{ $meta['english'] }} ({{ $meta['filipino'] }})</td>
                                <td class="left">{{ $meta['description'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <h3 style="margin-top:14px;">Report on Learning Progress and Achievement</h3>
                <table class="rc scale">
                    <thead>
                        <tr>
                            <th>Descriptors</th>
                            <th style="width:28%">Grading Scale</th>
                            <th style="width:22%">Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>Outstanding</td><td class="center">90 – 100</td><td class="center">Passed</td></tr>
                        <tr><td>Very Satisfactory</td><td class="center">85 – 89</td><td class="center">Passed</td></tr>
                        <tr><td>Satisfactory</td><td class="center">80 – 84</td><td class="center">Passed</td></tr>
                        <tr><td>Fairly Satisfactory</td><td class="center">75 – 79</td><td class="center">Passed</td></tr>
                        <tr><td>Did Not Meet Expectations</td><td class="center">Below 75</td><td class="center">Failed</td></tr>
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>
