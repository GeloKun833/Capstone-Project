<?php
    $isEdit = isset($activity) && $activity;
    $selectedLessonId = (int) old('lesson_id', $isEdit ? $activity->lesson_id : $lesson->id);
    $selectedType = old('activity_type', $isEdit ? $activity->activity_type : '');
    $allowsSubmission = $errors->any()
        ? (bool) old('allows_submission')
        : ($isEdit ? (bool) $activity->allows_submission : ! $descriptiveLevel);
    $pointsValue = old('points', $isEdit ? $activity->points : '');
    $existingFiles = $isEdit ? ($activity->attachments ?? []) : [];
    $quizSeed = old('questions');
    if (is_array($quizSeed)) {
        $quizSeed = collect($quizSeed)->map(function ($question) {
            return [
                'id' => $question['id'] ?? null,
                'type' => $question['type'] ?? 'multiple_choice',
                'prompt' => $question['prompt'] ?? '',
                'points' => (int) ($question['points'] ?? 1),
                'explanation' => $question['explanation'] ?? '',
                'choices' => array_values($question['choices'] ?? []),
                'correct' => array_map('intval', (array) ($question['correct'] ?? [])),
                'image' => $question['existing_image'] ?? null,
                'image_name' => $question['existing_name'] ?? null,
            ];
        })->values()->all();
    } elseif ($isEdit) {
        $quizSeed = app(\App\Services\QuizService::class)->formSeed($activity);
    } else {
        $quizSeed = [];
    }
    if ($quizSeed === []) {
        $quizSeed = [[
            'id' => null,
            'type' => 'multiple_choice',
            'prompt' => '',
            'points' => 1,
            'explanation' => '',
            'choices' => ['', ''],
            'correct' => [],
            'image' => null,
            'image_name' => null,
        ]];
    }
    $quizLocked = $isEdit && $activity->quizAttempts()->exists();
    $schoolToday = now('Asia/Manila')->toDateString();
    $availableDate = old('available_date', $isEdit && $activity->available_at ? $activity->available_at->timezone('Asia/Manila')->format('Y-m-d') : '');
    $availableTime = old('available_time', $isEdit && $activity->available_at ? $activity->available_at->timezone('Asia/Manila')->format('H:i') : '');
?>

<form action="<?php echo e($isEdit ? route('lessons.activities.update', [$lesson, $activity]) : route('lessons.activities.store', $lesson)); ?>"
      method="POST" enctype="multipart/form-data" id="activityForm">
    <?php echo csrf_field(); ?>
    <?php if($isEdit): ?>
        <?php echo method_field('PUT'); ?>
    <?php endif; ?>
    <input type="hidden" name="intent" id="intent" value="draft">

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="dir-card mb-3">
                <div class="p-3 p-md-4">
                    <h5 class="mb-1">Class</h5>
                    <p class="text-muted small mb-3">This activity stays on the lesson you opened. Students in this section are the only ones who can open it.</p>
                    <label class="form-label" for="lesson_label">Grade, section, and subject <span class="text-danger">*</span></label>
                    <?php
                        $lockedLesson = $classLessons->first();
                        $choiceGrade = $lockedLesson->section->grade_level ?? '';
                        $choiceDescriptive = \App\Support\DescriptiveScale::isDescriptiveGradeLevel($choiceGrade);
                        $choiceLabel = trim(($choiceGrade ? $choiceGrade.' · ' : '').($lockedLesson->section->name ?? 'Section').' · '.($lockedLesson->subject->subject_name ?? 'Subject'));
                    ?>
                    <input type="text" class="form-control" id="lesson_label" value="<?php echo e($choiceLabel); ?> — <?php echo e($lockedLesson->title); ?>" readonly>
                    <input type="hidden" name="lesson_id" value="<?php echo e($lockedLesson->id); ?>">
                    <select id="lesson_id" class="d-none" tabindex="-1" aria-hidden="true">
                        <option value="<?php echo e($lockedLesson->id); ?>"
                                data-grade="<?php echo e($choiceGrade); ?>"
                                data-descriptive="<?php echo e($choiceDescriptive ? '1' : '0'); ?>"
                                data-kinds='<?php echo json_encode(\App\Models\Activity::kindsFor($choiceGrade), 15, 512) ?>'
                                data-label="<?php echo e($choiceLabel); ?>"
                                selected>
                            <?php echo e($choiceLabel); ?> — <?php echo e($lockedLesson->title); ?>

                        </option>
                    </select>
                    <?php $__errorArgs = ['lesson_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    <p class="text-muted small mb-0 mt-2" id="classNote"></p>
                </div>
            </div>

            <div class="dir-card mb-3">
                <div class="p-3 p-md-4">
                    <h5 class="mb-3">Activity</h5>
                    <div class="form-group mb-3" id="titleGroup">
                        <label class="form-label" for="title">Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="title" name="title"
                               value="<?php echo e(old('title', $isEdit ? $activity->title : '')); ?>" placeholder="Title" required>
                        <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div class="form-group mb-3" id="instructionsGroup">
                        <label class="form-label" for="instructions">Instructions <span class="text-danger">*</span></label>
                        <textarea class="form-control <?php $__errorArgs = ['instructions'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="instructions" name="instructions"
                                  rows="6" placeholder="Tell students what to do."><?php echo e(old('instructions', $isEdit ? $activity->instructions : '')); ?></textarea>
                        <?php $__errorArgs = ['instructions'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback d-block"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div class="form-group mb-0">
                        <label class="form-label" for="activity_type">Activity type <span class="text-danger">*</span></label>
                        <select class="form-control <?php $__errorArgs = ['activity_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="activity_type" name="activity_type">
                            <option value="">Select an activity type</option>
                            <?php $__currentLoopData = $activityKinds; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($value); ?>" <?php if($selectedType === $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php if($selectedType && ! array_key_exists($selectedType, $activityKinds)): ?>
                                <option value="<?php echo e($selectedType); ?>" selected><?php echo e($activity->typeLabel()); ?></option>
                            <?php endif; ?>
                        </select>
                        <p class="text-muted small mb-0 mt-2" id="typeHelp">Choose a type to see what it is for.</p>
                        <?php $__errorArgs = ['activity_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback d-block"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                </div>
            </div>

            <div class="dir-card mb-3" id="quizBuilder" hidden>
                <div class="p-3 p-md-4 d-flex flex-wrap justify-content-between gap-3 align-items-center">
                    <div>
                        <h5 class="mb-1">Quiz</h5>
                        <p class="text-muted small mb-0" id="quizSummary">Open the quiz form to add questions, the deadline, and the score settings.</p>
                    </div>
                    <button type="button" class="btn btn-primary" id="openQuizForm">Open quiz form</button>
                </div>
            </div>

            <div class="dir-card mb-3" id="fileBuilder" hidden>
                <div class="p-3 p-md-4">
                    <h5 class="mb-1">File submission</h5>
                    <p class="text-muted small mb-0">Students turn in one file: PDF, Word, PowerPoint, or an image. Use Materials if you want to attach the exercise, project brief, or reflection guide.</p>
                </div>
            </div>

            <div class="dir-card mb-3" id="scheduleCard">
                <div class="p-3 p-md-4">
                    <h5 class="mb-3">Schedule and grading</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="due_date">Due date</label>
                            <input type="date" class="form-control <?php $__errorArgs = ['due_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="due_date" name="due_date"
                                   min="<?php echo e($schoolToday); ?>"
                                   value="<?php echo e(old('due_date', $isEdit && $activity->due_date ? $activity->due_date->format('Y-m-d') : '')); ?>">
                            <small class="text-muted">Choose today or a later date. The due time cannot be earlier than the time you create the activity.</small>
                            <?php $__errorArgs = ['due_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="due_time">Due time</label>
                            <input type="time" class="form-control <?php $__errorArgs = ['due_time'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="due_time" name="due_time"
                                   value="<?php echo e(old('due_time', $isEdit ? $activity->dueTimeInput() : '')); ?>">
                            <?php $__errorArgs = ['due_time'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="col-12" id="pointsWrap">
                            <label class="form-label" for="points">Points</label>
                            <input type="number" class="form-control <?php $__errorArgs = ['points'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="points" name="points"
                                   min="1" max="100" value="<?php echo e($pointsValue); ?>" placeholder="1–100, or leave blank">
                            <small class="text-muted">Used for Grade 4 through Grade 10. Leave blank when this activity is not scored.</small>
                            <?php $__errorArgs = ['points'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback d-block"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>
                    <label class="act-toggle mt-3 <?php echo e($allowsSubmission ? 'is-on' : ''); ?>" id="submissionToggle" hidden>
                        <input class="form-check-input" type="checkbox" id="allows_submission" name="allows_submission" value="1" <?php echo e($allowsSubmission ? 'checked' : ''); ?>>
                        <span>
                            <strong>Students can turn in a file</strong>
                            <small id="submissionHelp">Turn this on when you want a file from the student.</small>
                        </span>
                    </label>
                </div>
            </div>

            <div class="dir-card" id="materialsCard">
                <div class="p-3 p-md-4">
                    <h5 class="mb-1">Materials</h5>
                    <p class="text-muted small mb-3">PDF, Word, PowerPoint, or images. Up to 5 files, 10 MB each.</p>
                    <?php if($existingFiles): ?>
                        <div class="act-files mb-3">
                            <?php $__currentLoopData = $existingFiles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $file): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <label class="act-file">
                                    <input type="checkbox" name="remove_attachments[]" value="<?php echo e($file['path']); ?>">
                                    <span>
                                        <strong><?php echo e($file['name'] ?? 'File'); ?></strong>
                                        <small>Check to remove when you save</small>
                                        <?php if(\Illuminate\Support\Str::startsWith($file['mime'] ?? '', 'image/') && ! empty($file['path'])): ?>
                                            <img src="<?php echo e(asset('storage/'.$file['path'])); ?>" alt="">
                                        <?php endif; ?>
                                    </span>
                                </label>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php endif; ?>
                    <input type="file" class="form-control <?php $__errorArgs = ['attachments'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?> <?php $__errorArgs = ['attachments.*'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                           id="attachments" name="attachments[]" multiple
                           accept=".pdf,.doc,.docx,.ppt,.pptx,.jpg,.jpeg,.png,.webp">
                    <?php $__errorArgs = ['attachments'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback d-block"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    <?php $__errorArgs = ['attachments.*'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback d-block"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    <div class="act-preview" id="filePreview"></div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 align-self-start">
            <div class="dir-card act-review">
                <div class="p-3 p-md-4">
                    <h5 class="mb-3">Review before posting</h5>
                    <div class="act-fact"><span>Status</span><strong><?php echo e($isEdit && $activity->isPublished() ? 'Posted' : 'Draft'); ?></strong></div>
                    <div class="act-fact"><span>Class</span><strong id="reviewClass">—</strong></div>
                    <div class="act-fact"><span>Title</span><strong id="reviewTitle">—</strong></div>
                    <div class="act-fact"><span>Type</span><strong id="reviewType">—</strong></div>
                    <div class="act-fact"><span>Due</span><strong id="reviewDue">No due date</strong></div>
                    <div class="act-fact"><span>Points</span><strong id="reviewPoints">—</strong></div>
                    <div class="act-fact"><span>Turn in</span><strong id="reviewTurnIn">—</strong></div>
                    <p class="text-muted small mb-0" id="saveState">A draft stays hidden from students.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="act-actions">
        <button type="submit" class="btn btn-outline-secondary dir-btn" id="draftBtn" name="intent" value="draft">Save draft</button>
        <button type="submit" class="btn btn-primary dir-btn" id="postBtn" name="intent" value="publish">Post</button>
        <a href="<?php echo e(route('lessons.activities.index', $lesson)); ?>" class="btn btn-link">Cancel</a>
    </div>

    <?php echo $__env->make('activities.partials.quiz-modal', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
</form>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('assets/css/directory-modern.css')); ?>?v=20261014a">
<style>
.act-toggle { display: flex; gap: 0.75rem; align-items: flex-start; border: 1px solid #e5e7eb; border-radius: 12px; padding: 0.9rem 1rem; cursor: pointer; background: #fff7ed; }
.act-toggle.is-on { border-color: #fdba74; background: #ffedd5; }
.act-toggle strong { display: block; }
.act-toggle small, .act-file small { display: block; color: #6b7280; }
.act-fact { margin-bottom: 0.75rem; }
.act-fact span { display: block; font-size: 0.72rem; font-weight: 700; letter-spacing: 0.04em; text-transform: uppercase; color: #94a3b8; }
.act-review { position: sticky; top: 1rem; }
.act-actions { display: flex; flex-wrap: wrap; gap: 0.5rem; align-items: center; margin-top: 1rem; position: sticky; bottom: 0; background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 0.75rem; }
.act-files, .act-preview { display: grid; gap: 0.6rem; }
.act-preview { margin-top: 0.75rem; }
.act-file { display: flex; gap: 0.6rem; align-items: flex-start; border: 1px solid #e5e7eb; border-radius: 10px; padding: 0.7rem; }
.act-file img, .act-preview img, .act-preview iframe { display: block; max-width: 100%; margin-top: 0.4rem; border-radius: 8px; max-height: 180px; }
.quiz-q { border: 1px solid #e5e7eb; border-radius: 12px; padding: 0.9rem 1rem; background: #fff; }
.quiz-q h6 { margin: 0; }
.quiz-choice { display: flex; gap: 0.6rem; align-items: center; }
.quiz-choice input[type="radio"] { margin: 0; }
.dir-page .form-control { min-height: 42px; border-radius: 10px; }
@media (max-width: 991px) { .act-review { position: static; } }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('activityForm');
    const lessonSelect = document.getElementById('lesson_id');
    const typeSelect = document.getElementById('activity_type');
    const pointsWrap = document.getElementById('pointsWrap');
    const saveState = document.getElementById('saveState');
    const fileInput = document.getElementById('attachments');
    const preview = document.getElementById('filePreview');
    const storageKey = 'activity-draft-<?php echo e($lesson->id); ?>';
    const storeUrl = <?php echo json_encode(route('lessons.activities.store', $lesson), 512) ?>;
    const isEdit = <?php echo json_encode($isEdit, 15, 512) ?>;
    let updateUrl = <?php echo json_encode($isEdit ? route('lessons.activities.update', [$lesson, $activity]) : null) ?>;
    let timer = null;
    const typeHelpText = <?php echo json_encode(\App\Models\Activity::descriptions(), 15, 512) ?>;
    const fileTypes = ['practice_exercise', 'project', 'reflection'];
    let quizItems = <?php echo json_encode(array_values($quizSeed), 15, 512) ?>;

    const quizLocked = <?php echo json_encode($quizLocked, 15, 512) ?>;
    const schoolToday = <?php echo json_encode($schoolToday, 15, 512) ?>;
    const openedAt = Date.now();
    const schoolMinutes = <?php echo json_encode((int) now('Asia/Manila')->format('H') * 60 + (int) now('Asia/Manila')->format('i'), 15, 512) ?>;

    function currentSchoolTime() {
        const total = schoolMinutes + Math.floor((Date.now() - openedAt) / 60000);
        const hours = Math.floor(total / 60) % 24;
        const minutes = total % 60;
        return String(hours).padStart(2, '0') + ':' + String(minutes).padStart(2, '0');
    }

    function lockPastTime(dateId, timeId) {
        const dateField = document.getElementById(dateId);
        const timeField = document.getElementById(timeId);
        if (!dateField || !timeField) return;
        if (dateField.value && dateField.value < schoolToday) {
            dateField.value = schoolToday;
        }
        if (dateField.value === schoolToday) {
            timeField.min = currentSchoolTime();
        } else {
            timeField.removeAttribute('min');
        }
    }

    function lockActivityDates() {
        lockPastTime('due_date', 'due_time');
        lockPastTime('quizDueDate', 'quizDueTime');
        lockPastTime('available_date', 'available_time');
    }
    const questionTypes = {
        multiple_choice: 'Multiple Choice',
        true_false: 'True or False'
    };

    function blankQuestion() {
        return { id: null, type: 'multiple_choice', prompt: '', points: 1, explanation: '', choices: ['', ''], correct: [], image: null, image_name: null };
    }

    function renderQuiz() {
        const box = document.getElementById('quizQuestions');
        if (!box) return;
        box.innerHTML = '';
        quizItems.forEach(function (item, index) {
            const card = document.createElement('div');
            card.className = 'quiz-q';
            const head = document.createElement('div');
            head.className = 'd-flex flex-wrap justify-content-between align-items-center gap-2 mb-2';
            const title = document.createElement('h6');
            title.className = 'mb-0';
            if (item.type !== 'multiple_choice' && item.type !== 'true_false') item.type = 'multiple_choice';
            title.textContent = 'Question ' + (index + 1) + ' · ' + questionTypes[item.type];
            const actions = document.createElement('div');
            actions.className = 'd-flex gap-2';
            if (!quizLocked) {
                [['Up', function () { moveQuestion(index, -1); }], ['Down', function () { moveQuestion(index, 1); }], ['Duplicate', function () { duplicateQuestion(index); }], ['Delete', function () { deleteQuestion(index); }]].forEach(function (pair) {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'btn btn-link btn-sm p-0';
                    button.textContent = pair[0];
                    button.addEventListener('click', pair[1]);
                    actions.appendChild(button);
                });
            }
            head.appendChild(title);
            head.appendChild(actions);
            card.appendChild(head);

            if (item.id) hidden(card, 'questions[' + index + '][id]', item.id);
            const type = document.createElement('select');
            type.className = 'form-control mb-2';
            type.name = 'questions[' + index + '][type]';
            Object.keys(questionTypes).forEach(function (key) {
                const option = document.createElement('option');
                option.value = key;
                option.textContent = questionTypes[key];
                option.selected = item.type === key;
                type.appendChild(option);
            });
            type.disabled = quizLocked;
            if (quizLocked) hidden(card, 'questions[' + index + '][type]', item.type);
            type.addEventListener('change', function () {
                readQuiz();
                quizItems[index].type = type.value;
                if (type.value === 'true_false') quizItems[index].choices = ['True', 'False'];
                renderQuiz();
            });
            card.appendChild(type);

            const prompt = document.createElement('textarea');
            prompt.className = 'form-control mb-2';
            prompt.name = 'questions[' + index + '][prompt]';
            prompt.rows = 2;
            prompt.placeholder = 'Question';
            prompt.value = item.prompt || '';
            card.appendChild(prompt);

            if (item.image) {
                hidden(card, 'questions[' + index + '][existing_image]', item.image);
                hidden(card, 'questions[' + index + '][existing_name]', item.image_name || '');
                const current = document.createElement('p');
                current.className = 'small text-muted';
                current.textContent = 'Image attached' + (item.image_name ? ': ' + item.image_name : '.');
                card.appendChild(current);
            }
            const file = document.createElement('input');
            file.type = 'file';
            file.className = 'form-control mb-2';
            file.name = 'questions[' + index + '][image]';
            file.accept = '.jpg,.jpeg,.png,.webp';
            card.appendChild(file);

            const choiceBox = document.createElement('div');
            choiceBox.className = 'mb-2';
            if (item.type === 'true_false') {
                ['True', 'False'].forEach(function (label, choiceIndex) {
                    choiceBox.appendChild(choiceRow(index, choiceIndex, label, true, item, 'radio'));
                });
            } else {
                const choices = (item.choices || []).slice();
                if (choices.length < 2) choices.push('', '');
                choices.forEach(function (choice, choiceIndex) {
                    choiceBox.appendChild(choiceRow(index, choiceIndex, choice, false, item, 'radio'));
                });
                if (!quizLocked && choices.length < 6) {
                    const add = document.createElement('button');
                    add.type = 'button';
                    add.className = 'btn btn-link btn-sm p-0';
                    add.textContent = 'Add choice';
                    add.addEventListener('click', function () {
                        readQuiz();
                        quizItems[index].choices.push('');
                        renderQuiz();
                    });
                    choiceBox.appendChild(add);
                }
            }
            card.appendChild(choiceBox);

            const points = document.createElement('input');
            points.type = 'number';
            points.className = 'form-control mb-2';
            points.style.maxWidth = '8rem';
            points.min = '1';
            points.max = '100';
            points.name = 'questions[' + index + '][points]';
            points.value = item.points || 1;
            points.readOnly = quizLocked;
            points.addEventListener('input', paintReview);
            const pointsLabel = document.createElement('label');
            pointsLabel.className = 'form-label';
            pointsLabel.textContent = 'Points';
            card.appendChild(pointsLabel);
            card.appendChild(points);

            const explanation = document.createElement('textarea');
            explanation.className = 'form-control';
            explanation.name = 'questions[' + index + '][explanation]';
            explanation.rows = 2;
            explanation.placeholder = 'Optional explanation shown after submission when correct answers are released';
            explanation.value = item.explanation || '';
            card.appendChild(explanation);
            box.appendChild(card);
        });
        const count = document.getElementById('quizCount');
        if (count) count.textContent = '(' + quizItems.length + ')';
        paintReview();
    }

    function hidden(parent, name, value) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        input.value = value || '';
        parent.appendChild(input);
    }

    function choiceRow(questionIndex, choiceIndex, value, fixed, item, inputType) {
        const row = document.createElement('label');
        row.className = 'quiz-choice mb-2';
        const marker = document.createElement('input');
        marker.type = inputType;
        marker.name = 'questions[' + questionIndex + '][correct][]';
        marker.value = String(choiceIndex);
        marker.checked = (item.correct || []).map(Number).indexOf(choiceIndex) !== -1;
        marker.disabled = quizLocked;
        if (quizLocked && marker.checked) hidden(row, 'questions[' + questionIndex + '][correct][]', String(choiceIndex));
        const text = document.createElement('input');
        text.type = 'text';
        text.className = 'form-control';
        text.name = 'questions[' + questionIndex + '][choices][]';
        text.value = value || '';
        text.readOnly = fixed || quizLocked;
        if (fixed) text.tabIndex = -1;
        row.appendChild(marker);
        row.appendChild(text);
        return row;
    }

    function moveQuestion(index, direction) {
        const next = index + direction;
        if (next < 0 || next >= quizItems.length) return;
        readQuiz();
        const item = quizItems.splice(index, 1)[0];
        quizItems.splice(next, 0, item);
        renderQuiz();
    }

    function duplicateQuestion(index) {
        if (quizItems.length >= 30) return;
        readQuiz();
        const copy = JSON.parse(JSON.stringify(quizItems[index]));
        copy.id = null;
        copy.image = null;
        quizItems.splice(index + 1, 0, copy);
        renderQuiz();
    }

    function deleteQuestion(index) {
        if (!window.confirm('Delete this question?')) return;
        readQuiz();
        quizItems.splice(index, 1);
        if (!quizItems.length) quizItems.push(blankQuestion());
        renderQuiz();
    }

    function readQuiz() {
        quizItems = [...document.querySelectorAll('.quiz-q')].map(function (card) {
            const choices = [...card.querySelectorAll('input[type="text"]')].map(function (input) { return input.value; });
            const marked = [...card.querySelectorAll('input[name$="[correct][]"]:checked')].map(function (input) { return Number(input.value); });
            const points = card.querySelector('input[type="number"]');
            const id = card.querySelector('input[name$="[id]"]');
            return {
                id: id ? id.value : null,
                type: card.querySelector('select').value,
                prompt: card.querySelector('textarea').value,
                choices: choices,
                correct: marked,
                points: points ? Number(points.value) || 1 : 1,
                explanation: card.querySelectorAll('textarea')[1] ? card.querySelectorAll('textarea')[1].value : '',
                image: (card.querySelector('input[name$="[existing_image]"]') || {}).value || null,
                image_name: (card.querySelector('input[name$="[existing_name]"]') || {}).value || null
            };
        });
    }

    function quizPointTotal() {
        return [...document.querySelectorAll('#quizQuestions input[type="number"]')].reduce(function (sum, input) {
            return sum + (Number(input.value) || 0);
        }, 0);
    }

    function syncResponse() {
        const type = typeSelect.value;
        const quiz = type === 'quiz';
        const file = fileTypes.indexOf(type) !== -1;
        document.getElementById('quizBuilder').hidden = !quiz;
        document.getElementById('fileBuilder').hidden = !file;
        document.getElementById('titleGroup').hidden = quiz;
        document.getElementById('instructionsGroup').hidden = quiz;
        document.getElementById('scheduleCard').hidden = quiz;
        document.getElementById('materialsCard').hidden = quiz;
        document.getElementById('title').required = !quiz;
        const toggle = document.getElementById('submissionToggle');
        const checkbox = document.getElementById('allows_submission');
        toggle.hidden = quiz || file || type === '';
        if (file) checkbox.checked = true;
        if (quiz) checkbox.checked = false;
        if (quiz || file) checkbox.disabled = true;
        else checkbox.disabled = false;
        if (quiz && !quizOpened) {
            quizOpened = true;
            openQuizModal();
        }
        if (!quiz) quizOpened = false;
    }

    let quizOpened = false;

    function pushQuizFields() {
        document.getElementById('quizTitle').value = document.getElementById('title').value;
        document.getElementById('quizInstructions').value = document.getElementById('instructions').value;
        document.getElementById('quizDueDate').value = document.getElementById('due_date').value;
        document.getElementById('quizDueTime').value = document.getElementById('due_time').value;
        const option = selectedOption();
        const label = option ? option.dataset.label : '';
        document.getElementById('quizClassLock').value = label;
        document.getElementById('quizSubjectLock').value = label;
        document.getElementById('quizClassSummary').textContent = label || 'Choose the class on the activity page.';
    }

    function pullQuizFields() {
        document.getElementById('title').value = document.getElementById('quizTitle').value;
        document.getElementById('instructions').value = document.getElementById('instructions').value;
        document.getElementById('due_date').value = document.getElementById('quizDueDate').value;
        document.getElementById('due_time').value = document.getElementById('quizDueTime').value;
    }

    function openQuizModal() {
        pushQuizFields();
        const modal = document.getElementById('quizModal');
        if (modal && window.bootstrap) bootstrap.Modal.getOrCreateInstance(modal).show();
    }

    function previewQuiz() {
        readQuiz();
        pullQuizFields();
        const body = document.getElementById('quizPreviewBody');
        body.innerHTML = '';
        const intro = document.createElement('p');
        intro.style.whiteSpace = 'pre-line';
        intro.textContent = document.getElementById('quizInstructions').value || 'No instructions yet.';
        body.appendChild(intro);
        quizItems.forEach(function (item, index) {
            const block = document.createElement('div');
            block.className = 'border rounded p-3 mb-2';
            const title = document.createElement('strong');
            title.textContent = (index + 1) + '. ' + (item.prompt || 'Untitled question');
            block.appendChild(title);
            const meta = document.createElement('p');
            meta.className = 'text-muted small mb-2';
            meta.textContent = (questionTypes[item.type] || 'Question') + ' · ' + (item.points || 0) + ' points';
            block.appendChild(meta);
            (item.type === 'true_false' ? ['True', 'False'] : (item.choices || [])).forEach(function (choice) {
                if (!choice) return;
                const line = document.createElement('div');
                line.textContent = choice;
                block.appendChild(line);
            });
            body.appendChild(block);
        });
        const modal = document.getElementById('quizPreviewModal');
        if (modal && window.bootstrap) bootstrap.Modal.getOrCreateInstance(modal).show();
    }

    function bindUpdate(url) {
        if (!url) return;
        updateUrl = url;
        form.action = url;
        if (!form.querySelector('input[name="_method"]')) {
            const method = document.createElement('input');
            method.type = 'hidden';
            method.name = '_method';
            method.value = 'PUT';
            form.appendChild(method);
        }
    }

    function selectedOption() {
        return lessonSelect.options[lessonSelect.selectedIndex];
    }

    function syncClass(keepType) {
        const option = selectedOption();
        if (!option) return;
        const descriptive = option.dataset.descriptive === '1';
        const kinds = JSON.parse(option.dataset.kinds || '{}');
        const current = keepType ? typeSelect.value : '';
        typeSelect.innerHTML = '<option value="">Select an activity type</option>';
        Object.keys(kinds).forEach(function (key) {
            const item = document.createElement('option');
            item.value = key;
            item.textContent = kinds[key];
            if (key === current) item.selected = true;
            typeSelect.appendChild(item);
        });
        pointsWrap.style.display = descriptive ? 'none' : '';
        if (descriptive) document.getElementById('points').value = '';
        document.getElementById('classNote').textContent = descriptive
            ? (option.dataset.grade || 'This level') + ' does not use point scores.'
            : (option.dataset.grade || 'This level') + ' can use points from 1 to 100.';
        document.getElementById('submissionHelp').textContent = descriptive
            ? 'Optional. A photo or scan of the learner’s work can be turned in.'
            : 'Students can upload a PDF, Word file, PowerPoint, or image.';
        paintTypeHelp();
        paintReview();
    }

    function paintTypeHelp() {
        const help = document.getElementById('typeHelp');
        if (!help) return;
        help.textContent = typeHelpText[typeSelect.value] || 'Choose a type to see what it is for.';
        syncResponse();
    }

    function paintReview() {
        const option = selectedOption();
        const dueDate = document.getElementById('due_date').value;
        const dueTime = document.getElementById('due_time').value;
        const points = document.getElementById('points').value;
        const descriptive = option && option.dataset.descriptive === '1';
        const quiz = typeSelect.value === 'quiz';
        const file = fileTypes.indexOf(typeSelect.value) !== -1;
        const total = quizPointTotal();
        document.getElementById('reviewClass').textContent = option ? option.dataset.label : '—';
        document.getElementById('reviewTitle').textContent = document.getElementById('title').value || '—';
        document.getElementById('reviewType').textContent = typeSelect.selectedOptions[0] && typeSelect.value
            ? typeSelect.selectedOptions[0].textContent
            : '—';
        document.getElementById('reviewDue').textContent = dueDate ? (dueDate + (dueTime ? ' at ' + dueTime : '')) : 'No due date';
        document.getElementById('quizTotal').textContent = 'Total: ' + total + (total === 1 ? ' point' : ' points');
        const totalField = document.getElementById('quizTotalPoints');
        if (totalField) totalField.value = String(total);
        const summary = document.getElementById('quizSummary');
        if (summary) summary.textContent = quizItems.length + ' question' + (quizItems.length === 1 ? '' : 's') + ' · ' + total + ' points';
        document.getElementById('reviewPoints').textContent = quiz
            ? (total ? (total + ' from the questions') : 'Add question points')
            : (descriptive ? 'Descriptive, no points' : (points || 'Not scored'));
        document.getElementById('reviewTurnIn').textContent = quiz
            ? 'Answered in the quiz'
            : (file ? 'File submission' : (document.getElementById('allows_submission').checked ? 'File turn-in on' : 'No file turn-in'));
        if (pointsWrap) pointsWrap.style.display = (descriptive || quiz) ? 'none' : '';
        document.getElementById('submissionToggle').classList.toggle('is-on', document.getElementById('allows_submission').checked);
        paintTypeHelp();
    }

    function remember() {
        if (isEdit) return;
        const payload = {
            updateUrl: updateUrl,
            title: document.getElementById('title').value,
            instructions: document.getElementById('instructions').value,
            activity_type: typeSelect.value,
            due_date: document.getElementById('due_date').value,
            due_time: document.getElementById('due_time').value,
            points: document.getElementById('points').value,
            lesson_id: lessonSelect.value,
            allows_submission: document.getElementById('allows_submission').checked
        };
        sessionStorage.setItem(storageKey, JSON.stringify(payload));
    }

    function restore() {
        if (isEdit || <?php echo json_encode($errors->any(), 15, 512) ?>) return;
        const raw = sessionStorage.getItem(storageKey);
        if (!raw) return;
        const saved = JSON.parse(raw);
        ['title', 'instructions', 'due_date', 'due_time', 'points'].forEach(function (id) {
            if (saved[id]) document.getElementById(id).value = saved[id];
        });
        if (saved.lesson_id) lessonSelect.value = saved.lesson_id;
        syncClass(false);
        if (saved.activity_type) typeSelect.value = saved.activity_type;
        document.getElementById('allows_submission').checked = !!saved.allows_submission;
        if (saved.updateUrl) bindUpdate(saved.updateUrl);
        paintReview();
        saveState.textContent = 'Unfinished details were restored. Save the draft to keep attached files.';
    }

    let saveController = null;
    async function saveDraft() {
        if (form.dataset.submitting === '1') return;
        if (!document.getElementById('title').value.trim()) return;
        if (typeSelect.value === 'quiz') pullQuizFields();
        saveState.textContent = 'Saving draft…';
        const data = new FormData(form);
        data.delete('attachments[]');
        data.set('intent', 'draft');
        if (updateUrl) data.set('_method', 'PUT');
        if (saveController) saveController.abort();
        saveController = new AbortController();
        try {
            const response = await fetch(updateUrl || storeUrl, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
                body: data,
                signal: saveController.signal
            });
            const body = await response.json().catch(function () { return {}; });
            if (!response.ok) {
                const errors = body.errors || {};
                const first = Object.values(errors)[0];
                saveState.textContent = (first && first[0]) || body.message || 'Draft could not be saved.';
                return;
            }
            bindUpdate(body.update_url);
            saveState.textContent = 'Draft saved at ' + body.saved_at + '. Students cannot see it yet.';
            remember();
        } catch (error) {
            if (error && error.name === 'AbortError') return;
            saveState.textContent = 'Draft could not be saved. Check your connection and use Save draft.';
        }
    }

    lessonSelect.addEventListener('change', function () {
        const previous = typeSelect.value;
        syncClass(false);
        if ([...typeSelect.options].some(function (option) { return option.value === previous; })) {
            typeSelect.value = previous;
        }
        paintReview();
        remember();
    });
    ['due_date', 'quizDueDate', 'available_date', 'due_time', 'quizDueTime', 'available_time'].forEach(function (id) {
        const field = document.getElementById(id);
        if (!field) return;
        field.addEventListener('change', lockActivityDates);
        field.addEventListener('input', lockActivityDates);
    });
    lockActivityDates();
    ['title', 'instructions', 'due_date', 'due_time', 'points', 'activity_type'].forEach(function (id) {
        const field = document.getElementById(id);
        if (!field) return;
        field.addEventListener('input', function () { paintReview(); remember(); clearTimeout(timer); timer = setTimeout(saveDraft, 2500); });
        field.addEventListener('change', function () { paintReview(); remember(); clearTimeout(timer); timer = setTimeout(saveDraft, 800); });
    });
    const token = document.querySelector('meta[name="csrf-token"]').content;
    document.getElementById('allows_submission').addEventListener('change', function () { paintReview(); remember(); });
    const addQuestion = document.getElementById('addQuestion');
    if (addQuestion) {
        addQuestion.addEventListener('click', function () {
            readQuiz();
            if (quizItems.length >= 30) return;
            quizItems.push(blankQuestion());
            renderQuiz();
            remember();
        });
    }
    document.getElementById('openQuizForm').addEventListener('click', openQuizModal);
    document.getElementById('previewQuiz').addEventListener('click', previewQuiz);
    ['quizTitle', 'quizInstructions', 'quizDueDate', 'quizDueTime'].forEach(function (id) {
        document.getElementById(id).addEventListener('input', function () {
            pullQuizFields();
            paintReview();
            remember();
            form.dataset.dirty = '1';
        });
    });
    document.getElementById('quizQuestions').addEventListener('input', function () {
        paintReview();
        remember();
        form.dataset.dirty = '1';
        clearTimeout(timer);
        timer = setTimeout(saveDraft, 2500);
    });
    fileInput.addEventListener('change', function () {
        preview.innerHTML = '';
        [...fileInput.files].forEach(function (file) {
            const box = document.createElement('div');
            box.className = 'act-file';
            const name = document.createElement('strong');
            name.textContent = file.name;
            box.appendChild(name);
            const url = URL.createObjectURL(file);
            if (file.type.startsWith('image/')) {
                const img = document.createElement('img');
                img.src = url;
                img.alt = file.name;
                box.appendChild(img);
            } else if (file.type === 'application/pdf') {
                const frame = document.createElement('iframe');
                frame.src = url;
                frame.title = file.name;
                box.appendChild(frame);
            }
            preview.appendChild(box);
        });
    });
    form.addEventListener('submit', function (event) {
        if (typeSelect.value === 'quiz') pullQuizFields();
        const submitter = event.submitter;
        const chosen = submitter && submitter.name === 'intent' ? submitter.value : 'draft';
        document.getElementById('intent').value = chosen;
        if (submitter && submitter.name === 'intent') submitter.value = chosen;
        clearTimeout(timer);
        if (saveController) saveController.abort();
        form.dataset.submitting = '1';
        if (chosen === 'publish') sessionStorage.removeItem(storageKey);
    });

    renderQuiz();
    syncClass(true);
    restore();
    paintReview();
});
</script>
<?php $__env->stopPush(); ?>
<?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views/activities/partials/form.blade.php ENDPATH**/ ?>