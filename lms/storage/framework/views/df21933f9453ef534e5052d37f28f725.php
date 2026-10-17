<?php $__env->startSection('content'); ?>
<?php
    $allowed = max(1, (int) $activity->attempts_allowed);
    $left = max(0, $allowed - $used);
    $due = $activity->dueAt();
?>
<div class="page-wrapper">
    <div class="content container-fluid dir-page">
        <div class="page-header">
            <div class="row align-items-start">
                <div class="col">
                    <h3 class="page-title mb-1"><?php echo e($activity->title); ?></h3>
                    <p class="text-muted mb-0">
                        <?php echo e($lesson->section?->grade_level); ?> <?php echo e($lesson->section?->name); ?>

                        · <?php echo e($lesson->subject->subject_name ?? 'Subject'); ?>

                        · <?php echo e($activity->points ?: 0); ?> points
                        <?php if($due): ?> · Due <?php echo e($due->format('M j, Y g:i A')); ?> <?php endif; ?>
                        <?php if($activity->time_limit_minutes): ?> · <?php echo e($activity->time_limit_minutes); ?> minutes <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>

        <?php if(session('success')): ?>
            <div class="alert alert-success"><?php echo e(session('success')); ?></div>
        <?php endif; ?>
        <?php if($errors->any()): ?>
            <div class="alert alert-danger"><?php echo e($errors->first()); ?></div>
        <?php endif; ?>

        <?php if($attempt): ?>
            <div class="dir-card mb-3">
                <div class="p-3 p-md-4 d-flex flex-wrap justify-content-between gap-3">
                    <div>
                        <strong id="quizProgress">Question 1 of <?php echo e($questions->count()); ?></strong>
                        <div class="text-muted small">Attempt <?php echo e($attempt->attempt_number); ?> of <?php echo e($allowed); ?></div>
                    </div>
                    <?php if($remaining !== null): ?>
                        <div><span class="text-muted small d-block">Time left</span><strong id="quizTimer">--:--</strong></div>
                    <?php endif; ?>
                    <div class="text-muted small" id="quizSaveState">Answers are saved on this attempt.</div>
                </div>
            </div>

            <form id="quizTakeForm" action="<?php echo e(route('student.quizzes.submit', [$lesson, $activity])); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <div id="quizSlides"></div>
                <div class="d-flex flex-wrap gap-2 mt-3">
                    <button type="button" class="btn btn-outline-secondary" id="quizPrev">Previous</button>
                    <button type="button" class="btn btn-outline-secondary" id="quizNext">Next</button>
                    <button type="button" class="btn btn-outline-primary" id="quizReview">Review answers</button>
                    <button type="submit" class="btn btn-primary" id="quizSubmit">Submit quiz</button>
                </div>
            </form>
        <?php else: ?>
            <div class="dir-card mb-3">
                <div class="p-3 p-md-4">
                    <h5>Instructions</h5>
                    <p style="white-space: pre-line;"><?php echo e($activity->instructions); ?></p>
                    <p class="mb-1">Passing score: <?php echo e($activity->passing_score); ?>%</p>
                    <p class="mb-3">Attempts remaining: <?php echo e($left); ?> of <?php echo e($allowed); ?></p>
                    <?php if($activity->available_at && $activity->available_at->gt(now('Asia/Manila'))): ?>
                        <div class="alert alert-warning" id="quizWait">
                            This quiz is posted. It starts at <?php echo e($activity->available_at->timezone('Asia/Manila')->format('M j, Y g:i A')); ?>.
                            You can see it now. The questions stay closed until that time.
                        </div>
                        <p class="mb-3">Opens in <strong id="quizWaitClock">—</strong></p>
                        <form id="quizStartForm" action="<?php echo e(route('student.quizzes.start', [$lesson, $activity])); ?>" method="POST" hidden>
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="btn btn-primary">Start quiz</button>
                        </form>
                    <?php elseif($closed): ?>
                        <div class="alert alert-warning mb-0"><?php echo e($closed); ?></div>
                    <?php elseif($left > 0): ?>
                        <form action="<?php echo e(route('student.quizzes.start', [$lesson, $activity])); ?>" method="POST">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="btn btn-primary"><?php echo e($used > 0 ? 'Start another attempt' : 'Start quiz'); ?></button>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-info mb-0">You have completed the attempts allowed for this quiz.</div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if($latest): ?>
                <div class="dir-card">
                    <div class="p-3 p-md-4">
                        <h5>Submitted <?php echo e($latest->submitted_at?->timezone('Asia/Manila')->format('M j, Y g:i A')); ?></h5>
                        <?php if($latest->status === 'graded'): ?>
                            <p class="mb-1">Score: <?php echo e($latest->score); ?> / <?php echo e($latest->max_score); ?> (<?php echo e(number_format((float) $latest->percentage, 1)); ?>%)</p>
                            <p class="mb-3">
                                <?php if($latest->passed === null): ?>
                                    Result recorded.
                                <?php elseif($latest->passed): ?>
                                    Passed
                                <?php else: ?>
                                    Below the passing score
                                <?php endif; ?>
                                <?php if($latest->letter_grade): ?> · <?php echo e($latest->letter_grade); ?> <?php endif; ?>
                            </p>
                        <?php else: ?>
                            <p>Your answers were submitted. The score appears when grading is finished.</p>
                        <?php endif; ?>
                        <?php if($latest->feedback): ?>
                            <p style="white-space: pre-line;"><strong>Teacher feedback.</strong> <?php echo e($latest->feedback); ?></p>
                        <?php endif; ?>

                        <?php $responseMap = $latest->responses->keyBy('quiz_question_id'); ?>
                        <?php $__currentLoopData = $activity->quizQuestionItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $question): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php $response = $responseMap->get($question->id); ?>
                            <div class="border rounded p-3 mb-2">
                                <strong><?php echo e($index + 1); ?>. <?php echo e($question->prompt); ?></strong>
                                <div class="text-muted small mb-2"><?php echo e($question->typeLabel()); ?> · <?php echo e($response->points_earned ?? 'Pending'); ?> / <?php echo e($question->points); ?></div>
                                <?php if(in_array($question->question_type, ['short_answer', 'essay'], true)): ?>
                                    <p class="mb-0" style="white-space: pre-line;"><?php echo e(data_get($response, 'answer.text', 'No answer')); ?></p>
                                <?php else: ?>
                                    <?php $selected = data_get($response, 'answer.selected', []); ?>
                                    <ul class="mb-0">
                                        <?php $__currentLoopData = $question->choices ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $choiceIndex => $choice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <li>
                                                <?php echo e($choice); ?>

                                                <?php if(in_array($choiceIndex, $selected, false)): ?>
                                                    <span class="text-muted">— your answer</span>
                                                <?php endif; ?>
                                                <?php if($activity->show_correct_answers && $latest->status !== 'in_progress' && in_array($choiceIndex, $question->answer_key ?? [], false)): ?>
                                                    <strong>— correct</strong>
                                                <?php endif; ?>
                                            </li>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </ul>
                                <?php endif; ?>
                                <?php if($activity->show_correct_answers && $question->explanation): ?>
                                    <p class="mb-0 mt-2 text-muted"><?php echo e($question->explanation); ?></p>
                                <?php endif; ?>
                                <?php if($response?->feedback): ?>
                                    <p class="mb-0 mt-2"><?php echo e($response->feedback); ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <p class="text-muted small mb-0 mt-3">This score stays on the quiz. It is not entered into the term grade until your teacher encodes it.</p>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php if($attempt): ?>
<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const questions = <?php echo json_encode($questions, 15, 512) ?>;
    const answers = <?php echo json_encode($answers, 15, 512) ?>;
    const saveUrl = <?php echo json_encode(route('student.quizzes.save', [$lesson, $activity])) ?>;
    const token = document.querySelector('meta[name="csrf-token"]').content;
    const slides = document.getElementById('quizSlides');
    const form = document.getElementById('quizTakeForm');
    let index = 0;
    let review = false;
    let remaining = <?php echo json_encode($remaining, 15, 512) ?>;
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
<?php $__env->stopPush(); ?>
<?php endif; ?>
<?php if(!$attempt && $activity->available_at && $activity->available_at->gt(now('Asia/Manila'))): ?>
<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const opens = new Date(<?php echo json_encode($activity->available_at->toIso8601String(), 15, 512) ?>).getTime();
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
<?php $__env->stopPush(); ?>
<?php endif; ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views/quizzes/take.blade.php ENDPATH**/ ?>