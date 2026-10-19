@extends('layouts.master')
@section('content')
@php
    $allowed = max(1, (int) $activity->attempts_allowed);
    $left = max(0, $allowed - $used);
    $due = $activity->dueAt();
@endphp
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="sq">
        <div class="sq-head">
            <div>
                <p class="sq-kicker">Quiz</p>
                <h1 class="sq-title">{{ $activity->title }}</h1>
                <div class="sq-chips">
                    @if($lesson->section?->grade_level && $lesson->section?->name && $lesson->section->grade_level !== $lesson->section->name)
                        <span class="sq-chip">{{ $lesson->section->grade_level }}</span>
                        <span class="sq-chip">{{ $lesson->section->name }}</span>
                    @elseif($lesson->section?->grade_level || $lesson->section?->name)
                        <span class="sq-chip">{{ $lesson->section->grade_level ?: $lesson->section->name }}</span>
                    @endif
                    <span class="sq-chip">{{ $lesson->subject->subject_name ?? 'Subject' }}</span>
                    <span class="sq-chip">{{ $activity->points ?: 0 }} points</span>
                    @if($due)
                        <span class="sq-chip">Due {{ $due->format('M j, Y g:i A') }}</span>
                    @endif
                    @if($activity->time_limit_minutes)
                        <span class="sq-chip">{{ $activity->time_limit_minutes }} minutes</span>
                    @endif
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
            <div class="sq-status">
                <div class="sq-status-main">
                    <div class="sq-progress-copy">
                        <strong id="quizProgress">Question 1 of {{ $questions->count() }}</strong>
                        <span>Attempt {{ $attempt->attempt_number }} of {{ $allowed }}</span>
                    </div>
                    <div class="sq-bar" aria-hidden="true"><span id="quizProgressBar"></span></div>
                    <div class="sq-dots" id="quizDots" aria-label="Questions"></div>
                </div>
                <div class="sq-status-side">
                    @if($remaining !== null)
                        <div class="sq-timer" id="quizTimerWrap">
                            <span>Time left</span>
                            <strong id="quizTimer">--:--</strong>
                        </div>
                    @endif
                    <div class="sq-save" id="quizSaveState">Saved</div>
                </div>
            </div>

            <form id="quizTakeForm" action="{{ route('student.quizzes.submit', [$lesson, $activity]) }}" method="POST">
                @csrf
                <div id="quizSlides"></div>
                <div class="sq-actions">
                    <button type="button" class="sq-btn sq-btn-ghost" id="quizPrev">Previous</button>
                    <button type="button" class="sq-btn sq-btn-ghost" id="quizReview">Review</button>
                    <span class="sq-answered" id="quizAnswered"></span>
                    <button type="button" class="sq-btn sq-btn-next" id="quizNext">Next</button>
                    <button type="button" class="sq-btn sq-btn-submit" id="quizSubmit">Submit quiz</button>
                </div>
            </form>

            <div class="modal fade dir-modal" id="quizSubmitModal" tabindex="-1" aria-labelledby="quizSubmitTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content sq-submit-modal">
                        <div class="modal-header">
                            <h5 class="modal-title" id="quizSubmitTitle">Submit this quiz?</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p class="mb-2" id="quizSubmitSummary"></p>
                            <p class="mb-0 text-muted">You cannot change these answers afterward.</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="sq-btn sq-btn-ghost" data-bs-dismiss="modal">Keep working</button>
                            <button type="button" class="sq-btn sq-btn-submit" id="quizSubmitConfirm">Submit quiz</button>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="sq-panel">
                <div>
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
                            <button type="submit" class="sq-start">Start quiz</button>
                        </form>
                    @elseif($closed)
                        <div class="alert alert-warning mb-0">{{ $closed }}</div>
                    @elseif($left > 0)
                        <form action="{{ route('student.quizzes.start', [$lesson, $activity]) }}" method="POST">
                            @csrf
                            <button type="submit" class="sq-start">{{ $used > 0 ? 'Start another attempt' : 'Start quiz' }}</button>
                        </form>
                    @else
                        <div class="alert alert-info mb-0">You have completed the attempts allowed for this quiz.</div>
                    @endif
                </div>
            </div>

            @if($latest)
                <div class="sq-panel">
                    <div>
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
</div>
@endsection

@push('styles')
<style>
    .sq { max-width: 820px; margin: 0 auto; }
    .sq-head { margin-bottom: 18px; }
    .sq-kicker { margin: 0 0 4px; font-size: 0.75rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: #3d5ee1; }
    .sq-title { margin: 0; font-size: 1.7rem; font-weight: 700; letter-spacing: -0.03em; color: #1f2430; }
    .sq-chips { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 12px; }
    .sq-chip { background: #fff; border: 1px solid #e6e8ee; color: #3c4456; border-radius: 999px; padding: 5px 10px; font-size: 0.78rem; font-weight: 600; }
    .sq-status, .sq-card, .sq-panel { background: #fff; border: 1px solid #e6e8ee; border-radius: 18px; box-shadow: 0 1px 2px rgba(31,36,48,.04); }
    .sq-status { display: flex; justify-content: space-between; gap: 16px; padding: 16px 18px; margin-bottom: 14px; }
    .sq-status-main { flex: 1; min-width: 0; }
    .sq-status-side { display: flex; align-items: center; gap: 10px; }
    .sq-progress-copy { display: flex; justify-content: space-between; gap: 12px; align-items: baseline; }
    .sq-progress-copy strong { color: #1f2430; }
    .sq-progress-copy span, .sq-answered, .sq-save { color: #6b7287; font-size: 0.8rem; }
    .sq-bar { height: 6px; background: #eef1f6; border-radius: 999px; margin: 10px 0; overflow: hidden; }
    .sq-bar span { display: block; height: 100%; width: 0; background: #3d5ee1; border-radius: inherit; transition: width .2s ease; }
    .sq-dots { display: flex; flex-wrap: wrap; gap: 6px; }
    .sq-dot { width: 32px; height: 32px; border-radius: 10px; border: 1px solid #e3e6ee; background: #fff; color: #3c4456; font-size: 0.78rem; font-weight: 700; }
    .sq-dot.is-done { background: #eef2ff; border-color: #c9d4f8; color: #24356e; }
    .sq-dot.is-current { background: #3d5ee1; border-color: #3d5ee1; color: #fff; }
    .sq-timer { min-width: 92px; text-align: right; }
    .sq-timer span { display: block; color: #6b7287; font-size: 0.72rem; font-weight: 600; }
    .sq-timer strong { font-size: 1.35rem; letter-spacing: -0.03em; color: #1f2430; font-variant-numeric: tabular-nums; }
    .sq-timer.is-low strong { color: #d93025; }
    .sq-save { background: #f4f6fb; border-radius: 999px; padding: 6px 10px; white-space: nowrap; }
    .sq-save.is-error { color: #d93025; background: #fdecea; }
    .sq-card { margin-bottom: 14px; padding: 22px 22px 18px; }
    .sq-q-label { margin: 0 0 8px; color: #3d5ee1; font-size: 0.78rem; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; }
    .sq-prompt { margin: 0 0 6px; font-size: 1.25rem; line-height: 1.45; font-weight: 600; color: #1f2430; white-space: pre-line; }
    .sq-meta { margin: 0 0 16px; color: #6b7287; font-size: 0.82rem; }
    .sq-choice { position: relative; display: flex; align-items: center; gap: 12px; width: 100%; margin: 0 0 10px; padding: 14px 16px; border: 1px solid #e6e8ee; border-radius: 14px; background: #fff; cursor: pointer; }
    .sq-choice:hover, .sq-choice:focus-within { border-color: #c9d4f8; background: #f8f9ff; }
    .sq-choice.is-selected { border-color: #3d5ee1; background: #eef2ff; box-shadow: 0 0 0 3px rgba(61,94,225,.12); }
    .sq-choice input { position: absolute; opacity: 0; width: 1px; height: 1px; }
    .sq-mark { width: 32px; height: 32px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; background: #f4f6fb; color: #3c4456; font-weight: 700; flex: 0 0 32px; }
    .sq-choice.is-selected .sq-mark { background: #3d5ee1; color: #fff; }
    .sq-choice-text { font-size: 1rem; color: #1f2430; }
    .sq-card textarea, .sq-card input.form-control { border-radius: 12px; border: 1px solid #e3e6ee; background: #f7f8fc; padding: 12px 14px; }
    .sq-actions { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; margin-top: 4px; }
    .sq-btn { border: 0; border-radius: 12px; padding: 11px 16px; font-weight: 700; }
    .sq-btn:disabled { opacity: .45; }
    .sq-btn-ghost { background: #fff; color: #3c4456; border: 1px solid #e3e6ee; }
    .sq-btn-next { background: #eef2ff; color: #24356e; }
    .sq-btn-submit { margin-left: auto; background: #3d5ee1; color: #fff; padding-inline: 20px; }
    .sq-btn-submit:hover { background: #314db8; color: #fff; }
    .sq-submit-modal .modal-footer .sq-btn-submit { margin-left: 0; }
    .sq-submit-modal .sq-warn { color: #b45309; font-weight: 600; }
    .sq-panel { padding: 22px; margin-bottom: 14px; }
    .sq-panel h5 { font-weight: 700; }
    .sq-start { background: #3d5ee1; color: #fff; border: 0; border-radius: 12px; padding: 12px 20px; font-weight: 700; }
    @media (max-width: 720px) {
        .sq-status, .sq-actions { flex-direction: column; align-items: stretch; }
        .sq-btn-submit { margin-left: 0; }
        .sq-timer { text-align: left; }
    }
</style>
@endpush

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

    const dots = document.getElementById('quizDots');
    questions.forEach(function (_, dotIndex) {
        const dot = document.createElement('button');
        dot.type = 'button';
        dot.className = 'sq-dot';
        dot.textContent = String(dotIndex + 1);
        dot.addEventListener('click', function () {
            review = false;
            index = dotIndex;
            paint();
        });
        dots.appendChild(dot);
    });

    function answeredCount() {
        return questions.filter(function (question) {
            const saved = state[question.id];
            return (saved.selected && saved.selected.length) || String(saved.text || '').trim() !== '';
        }).length;
    }

    function paint() {
        slides.innerHTML = '';
        questions.forEach(function (question, questionIndex) {
            const card = document.createElement('div');
            card.className = 'sq-card';
            card.hidden = !review && questionIndex !== index;
            const label = document.createElement('p');
            label.className = 'sq-q-label';
            label.textContent = 'Question ' + (questionIndex + 1) + ' of ' + questions.length;
            card.appendChild(label);
            const prompt = document.createElement('p');
            prompt.className = 'sq-prompt';
            prompt.textContent = question.prompt;
            card.appendChild(prompt);
            const meta = document.createElement('p');
            meta.className = 'sq-meta';
            meta.textContent = question.type_label + ' · ' + question.points + (question.points === 1 ? ' point' : ' points');
            card.appendChild(meta);
            if (question.image) {
                const img = document.createElement('img');
                img.src = question.image;
                img.alt = '';
                img.className = 'mb-3 rounded';
                img.style.maxWidth = '100%';
                img.style.maxHeight = '280px';
                card.appendChild(img);
            }
            if (question.type === 'short_answer' || question.type === 'essay') {
                const input = document.createElement(question.type === 'essay' ? 'textarea' : 'input');
                input.className = 'form-control';
                if (question.type === 'essay') input.rows = 6;
                input.value = state[question.id].text;
                input.addEventListener('input', function () {
                    state[question.id].text = input.value;
                    scheduleSave();
                    refreshChrome();
                });
                card.appendChild(input);
            } else {
                question.choices.forEach(function (choice, choiceIndex) {
                    const choiceLabel = document.createElement('label');
                    choiceLabel.className = 'sq-choice';
                    const input = document.createElement('input');
                    input.type = question.type === 'multiple_select' ? 'checkbox' : 'radio';
                    input.name = 'q' + question.id;
                    input.value = String(choice.index);
                    input.checked = state[question.id].selected.indexOf(choice.index) !== -1;
                    if (input.checked) choiceLabel.classList.add('is-selected');
                    input.addEventListener('change', function () {
                        if (question.type === 'multiple_select') {
                            const box = state[question.id].selected;
                            const at = box.indexOf(choice.index);
                            if (input.checked && at === -1) box.push(choice.index);
                            if (!input.checked && at !== -1) box.splice(at, 1);
                        } else {
                            state[question.id].selected = [choice.index];
                        }
                        card.querySelectorAll('.sq-choice').forEach(function (row) {
                            const box = row.querySelector('input');
                            row.classList.toggle('is-selected', !!(box && box.checked));
                        });
                        scheduleSave();
                        refreshChrome();
                    });
                    const mark = document.createElement('span');
                    mark.className = 'sq-mark';
                    mark.textContent = String.fromCharCode(65 + choiceIndex);
                    const text = document.createElement('span');
                    text.className = 'sq-choice-text';
                    text.textContent = choice.text;
                    choiceLabel.appendChild(input);
                    choiceLabel.appendChild(mark);
                    choiceLabel.appendChild(text);
                    card.appendChild(choiceLabel);
                });
            }
            slides.appendChild(card);
        });
        refreshChrome();
    }

    function refreshChrome() {
        const progress = document.getElementById('quizProgress');
        const bar = document.getElementById('quizProgressBar');
        const answered = document.getElementById('quizAnswered');
        progress.textContent = review
            ? 'Review your answers'
            : 'Question ' + (index + 1) + ' of ' + questions.length;
        if (bar) bar.style.width = (review ? 100 : ((index + 1) / questions.length) * 100) + '%';
        if (answered) answered.textContent = answeredCount() + ' of ' + questions.length + ' answered';
        document.getElementById('quizPrev').disabled = !review && index === 0;
        document.getElementById('quizNext').disabled = !review && index === questions.length - 1;
        dots.querySelectorAll('.sq-dot').forEach(function (dot, dotIndex) {
            const question = questions[dotIndex];
            const saved = state[question.id];
            const done = (saved.selected && saved.selected.length) || String(saved.text || '').trim() !== '';
            dot.classList.toggle('is-current', !review && dotIndex === index);
            dot.classList.toggle('is-done', !!done);
        });
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
        note.classList.toggle('is-error', !response.ok);
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
    let submitConfirmed = false;
    const submitModal = document.getElementById('quizSubmitModal');
    function openSubmitModal() {
        const answered = answeredCount();
        const blank = questions.length - answered;
        const summary = document.getElementById('quizSubmitSummary');
        summary.textContent = blank === 0
            ? 'All ' + questions.length + ' questions are answered.'
            : blank + (blank === 1 ? ' question is' : ' questions are') + ' still blank.';
        summary.classList.toggle('sq-warn', blank > 0);
        bootstrap.Modal.getOrCreateInstance(submitModal).show();
    }
    document.getElementById('quizSubmit').addEventListener('click', openSubmitModal);
    document.getElementById('quizSubmitConfirm').addEventListener('click', function () {
        this.disabled = true;
        submitConfirmed = true;
        bootstrap.Modal.getOrCreateInstance(submitModal).hide();
        form.requestSubmit();
    });
    form.addEventListener('submit', function (event) {
        if (!submitConfirmed) {
            event.preventDefault();
            openSubmitModal();
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
            clock.parentElement.classList.toggle('is-low', remaining > 0 && remaining < 300);
            if (remaining === 0) {
                clearInterval(tick);
                submitConfirmed = true;
                form.requestSubmit();
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
