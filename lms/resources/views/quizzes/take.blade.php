@extends('layouts.master')
@section('content')
@php
    $allowed = max(1, (int) $activity->attempts_allowed);
    $left = max(0, $allowed - $used);
    $due = $activity->dueAt();
@endphp
<div class="page-wrapper">
    <div class="content container-fluid dir-page">
        <div class="page-header">
            <div class="row align-items-start">
                <div class="col">
                    <h3 class="page-title mb-1">{{ $activity->title }}</h3>
                    <p class="text-muted mb-0">
                        {{ $lesson->section?->grade_level }} {{ $lesson->section?->name }}
                        · {{ $lesson->subject->subject_name ?? 'Subject' }}
                        · {{ $activity->points ?: 0 }} points
                        @if($due) · Due {{ $due->format('M j, Y g:i A') }} @endif
                        @if($activity->time_limit_minutes) · {{ $activity->time_limit_minutes }} minutes @endif
                    </p>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        @if($attempt)
            <div class="dir-card mb-3">
                <div class="p-3 p-md-4 d-flex flex-wrap justify-content-between gap-3">
                    <div>
                        <strong id="quizProgress">Question 1 of {{ $questions->count() }}</strong>
                        <div class="text-muted small">Attempt {{ $attempt->attempt_number }} of {{ $allowed }}</div>
                    </div>
                    @if($remaining !== null)
                        <div><span class="text-muted small d-block">Time left</span><strong id="quizTimer">--:--</strong></div>
                    @endif
                    <div class="text-muted small" id="quizSaveState">Answers are saved on this attempt.</div>
                </div>
            </div>

            <form id="quizTakeForm" action="{{ route('student.quizzes.submit', [$lesson, $activity]) }}" method="POST">
                @csrf
                <div id="quizSlides"></div>
                <div class="d-flex flex-wrap gap-2 mt-3">
                    <button type="button" class="btn btn-outline-secondary" id="quizPrev">Previous</button>
                    <button type="button" class="btn btn-outline-secondary" id="quizNext">Next</button>
                    <button type="button" class="btn btn-outline-primary" id="quizReview">Review answers</button>
                    <button type="submit" class="btn btn-primary" id="quizSubmit">Submit quiz</button>
                </div>
            </form>
        @else
            <div class="dir-card mb-3">
                <div class="p-3 p-md-4">
                    <h5>Instructions</h5>
                    <p style="white-space: pre-line;">{{ $activity->instructions }}</p>
                    <p class="mb-1">Passing score: {{ $activity->passing_score }}%</p>
                    <p class="mb-3">Attempts remaining: {{ $left }} of {{ $allowed }}</p>
                    @if($activity->available_at && $activity->available_at->gt(now('Asia/Manila')))
                        <div class="alert alert-warning" id="quizWait">
                            This quiz is posted. It starts at {{ $activity->available_at->timezone('Asia/Manila')->format('M j, Y g:i A') }}.
                            You can see it now. The questions stay closed until that time.
                        </div>
                        <p class="mb-3">Opens in <strong id="quizWaitClock">—</strong></p>
                        <form id="quizStartForm" action="{{ route('student.quizzes.start', [$lesson, $activity]) }}" method="POST" hidden>
                            @csrf
                            <button type="submit" class="btn btn-primary">Start quiz</button>
                        </form>
                    @elseif($closed)
                        <div class="alert alert-warning mb-0">{{ $closed }}</div>
                    @elseif($left > 0)
                        <form action="{{ route('student.quizzes.start', [$lesson, $activity]) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary">{{ $used > 0 ? 'Start another attempt' : 'Start quiz' }}</button>
                        </form>
                    @else
                        <div class="alert alert-info mb-0">You have completed the attempts allowed for this quiz.</div>
                    @endif
                </div>
            </div>

            @if($latest)
                <div class="dir-card">
                    <div class="p-3 p-md-4">
                        <h5>Submitted {{ $latest->submitted_at?->timezone('Asia/Manila')->format('M j, Y g:i A') }}</h5>
                        @if($latest->status === 'graded')
                            <p class="mb-1">Score: {{ $latest->score }} / {{ $latest->max_score }} ({{ number_format((float) $latest->percentage, 1) }}%)</p>
                            <p class="mb-3">
                                @if($latest->passed === null)
                                    Result recorded.
                                @elseif($latest->passed)
                                    Passed
                                @else
                                    Below the passing score
                                @endif
                                @if($latest->letter_grade) · {{ $latest->letter_grade }} @endif
                            </p>
                        @else
                            <p>Your answers were submitted. The score appears when grading is finished.</p>
                        @endif
                        @if($latest->feedback)
                            <p style="white-space: pre-line;"><strong>Teacher feedback.</strong> {{ $latest->feedback }}</p>
                        @endif

                        @php $responseMap = $latest->responses->keyBy('quiz_question_id'); @endphp
                        @foreach($activity->quizQuestionItems as $index => $question)
                            @php $response = $responseMap->get($question->id); @endphp
                            <div class="border rounded p-3 mb-2">
                                <strong>{{ $index + 1 }}. {{ $question->prompt }}</strong>
                                <div class="text-muted small mb-2">{{ $question->typeLabel() }} · {{ $response->points_earned ?? 'Pending' }} / {{ $question->points }}</div>
                                @if(in_array($question->question_type, ['short_answer', 'essay'], true))
                                    <p class="mb-0" style="white-space: pre-line;">{{ data_get($response, 'answer.text', 'No answer') }}</p>
                                @else
                                    @php $selected = data_get($response, 'answer.selected', []); @endphp
                                    <ul class="mb-0">
                                        @foreach($question->choices ?? [] as $choiceIndex => $choice)
                                            <li>
                                                {{ $choice }}
                                                @if(in_array($choiceIndex, $selected, false))
                                                    <span class="text-muted">— your answer</span>
                                                @endif
                                                @if($activity->show_correct_answers && $latest->status !== 'in_progress' && in_array($choiceIndex, $question->answer_key ?? [], false))
                                                    <strong>— correct</strong>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                                @if($activity->show_correct_answers && $question->explanation)
                                    <p class="mb-0 mt-2 text-muted">{{ $question->explanation }}</p>
                                @endif
                                @if($response?->feedback)
                                    <p class="mb-0 mt-2">{{ $response->feedback }}</p>
                                @endif
                            </div>
                        @endforeach
                        <p class="text-muted small mb-0 mt-3">This score stays on the quiz. It is not entered into the term grade until your teacher encodes it.</p>
                    </div>
                </div>
            @endif
        @endif
    </div>
</div>
@endsection

@if($attempt)
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const questions = @json($questions);
    const answers = @json($answers);
    const saveUrl = @json(route('student.quizzes.save', [$lesson, $activity]));
    const token = document.querySelector('meta[name="csrf-token"]').content;
    const slides = document.getElementById('quizSlides');
    const form = document.getElementById('quizTakeForm');
    let index = 0;
    let review = false;
    let remaining = @json($remaining);
    const state = {};
    questions.forEach(function (question) {
        const saved = answers[question.id] || {};
        state[question.id] = {
            selected: (saved.selected || []).map(Number),
            text: saved.text || ''
        };
    });

    function paint() {
        slides.innerHTML = '';
        questions.forEach(function (question, questionIndex) {
            const card = document.createElement('div');
            card.className = 'dir-card mb-3';
            card.hidden = !review && questionIndex !== index;
            const body = document.createElement('div');
            body.className = 'p-3 p-md-4';
            const title = document.createElement('h5');
            title.textContent = 'Question ' + (questionIndex + 1) + ' of ' + questions.length;
            body.appendChild(title);
            const prompt = document.createElement('p');
            prompt.style.whiteSpace = 'pre-line';
            prompt.textContent = question.prompt;
            body.appendChild(prompt);
            const meta = document.createElement('p');
            meta.className = 'text-muted small';
            meta.textContent = question.type_label + ' · ' + question.points + ' points';
            body.appendChild(meta);
            if (question.image) {
                const img = document.createElement('img');
                img.src = question.image;
                img.alt = '';
                img.className = 'mb-3 rounded';
                img.style.maxWidth = '100%';
                img.style.maxHeight = '240px';
                body.appendChild(img);
            }
            if (question.type === 'short_answer' || question.type === 'essay') {
                const input = document.createElement(question.type === 'essay' ? 'textarea' : 'input');
                input.className = 'form-control';
                if (question.type === 'essay') input.rows = 6;
                input.value = state[question.id].text;
                input.addEventListener('input', function () {
                    state[question.id].text = input.value;
                    scheduleSave();
                });
                body.appendChild(input);
            } else {
                question.choices.forEach(function (choice) {
                    const label = document.createElement('label');
                    label.className = 'd-flex gap-2 align-items-start mb-2';
                    const input = document.createElement('input');
                    input.type = question.type === 'multiple_select' ? 'checkbox' : 'radio';
                    input.name = 'q' + question.id;
                    input.value = String(choice.index);
                    input.checked = state[question.id].selected.indexOf(choice.index) !== -1;
                    input.addEventListener('change', function () {
                        if (question.type === 'multiple_select') {
                            const box = state[question.id].selected;
                            const at = box.indexOf(choice.index);
                            if (input.checked && at === -1) box.push(choice.index);
                            if (!input.checked && at !== -1) box.splice(at, 1);
                        } else {
                            state[question.id].selected = [choice.index];
                        }
                        scheduleSave();
                    });
                    const text = document.createElement('span');
                    text.textContent = choice.text;
                    label.appendChild(input);
                    label.appendChild(text);
                    body.appendChild(label);
                });
            }
            card.appendChild(body);
            slides.appendChild(card);
        });
        document.getElementById('quizProgress').textContent = review
            ? 'Review your answers'
            : 'Question ' + (index + 1) + ' of ' + questions.length;
    }

    let timer = null;
    function scheduleSave() {
        clearTimeout(timer);
        timer = setTimeout(saveNow, 400);
    }
    function payload() {
        const answers = {};
        questions.forEach(function (question) {
            answers[question.id] = state[question.id];
        });
        return answers;
    }
    async function saveNow() {
        const note = document.getElementById('quizSaveState');
        note.textContent = 'Saving…';
        const response = await fetch(saveUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
            body: JSON.stringify({ answers: payload() })
        });
        const body = await response.json().catch(function () { return {}; });
        if (body.submitted) {
            window.location.reload();
            return;
        }
        note.textContent = response.ok ? 'Saved' : 'Could not save. Stay on this page and try again.';
    }
    document.getElementById('quizPrev').addEventListener('click', function () {
        review = false;
        index = Math.max(0, index - 1);
        paint();
    });
    document.getElementById('quizNext').addEventListener('click', function () {
        review = false;
        index = Math.min(questions.length - 1, index + 1);
        paint();
    });
    document.getElementById('quizReview').addEventListener('click', function () {
        review = true;
        paint();
    });
    form.addEventListener('submit', function (event) {
        if (!window.confirm('Submit this quiz? You cannot change these answers afterward.')) {
            event.preventDefault();
            return;
        }
        const holder = document.createElement('input');
        holder.type = 'hidden';
        holder.name = 'answers_json';
        form.appendChild(holder);
        questions.forEach(function (question) {
            const selected = document.createElement('input');
            selected.type = 'hidden';
            selected.name = 'answers[' + question.id + '][text]';
            selected.value = state[question.id].text || '';
            form.appendChild(selected);
            state[question.id].selected.forEach(function (choice) {
                const item = document.createElement('input');
                item.type = 'hidden';
                item.name = 'answers[' + question.id + '][selected][]';
                item.value = String(choice);
                form.appendChild(item);
            });
        });
    });
    if (remaining !== null) {
        const clock = document.getElementById('quizTimer');
        const tick = setInterval(function () {
            remaining = Math.max(0, remaining - 1);
            const minutes = String(Math.floor(remaining / 60)).padStart(2, '0');
            const seconds = String(remaining % 60).padStart(2, '0');
            clock.textContent = minutes + ':' + seconds;
            if (remaining === 0) {
                clearInterval(tick);
                form.requestSubmit ? form.requestSubmit() : form.submit();
            }
        }, 1000);
    }
    window.addEventListener('beforeunload', function (event) {
        saveNow();
    });
    paint();
    saveNow();
});
</script>
@endpush
@endif
@if(!$attempt && $activity->available_at && $activity->available_at->gt(now('Asia/Manila')))
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const opens = new Date(@json($activity->available_at->toIso8601String())).getTime();
    const clock = document.getElementById('quizWaitClock');
    const wait = document.getElementById('quizWait');
    const form = document.getElementById('quizStartForm');
    function tick() {
        const left = opens - Date.now();
        if (left <= 0) {
            if (wait) wait.hidden = true;
            if (clock && clock.parentElement) clock.parentElement.hidden = true;
            if (form) form.hidden = false;
            return;
        }
        const total = Math.ceil(left / 1000);
        const hours = Math.floor(total / 3600);
        const minutes = Math.floor((total % 3600) / 60);
        const seconds = total % 60;
        if (clock) clock.textContent = (hours ? hours + 'h ' : '') + minutes + 'm ' + seconds + 's';
        setTimeout(tick, 1000);
    }
    tick();
});
</script>
@endpush
@endif
