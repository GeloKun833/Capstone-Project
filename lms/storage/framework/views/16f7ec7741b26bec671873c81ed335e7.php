
<?php $__env->startSection('content'); ?>

    <div class="page-wrapper">
        <div class="content container-fluid">

            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title"><?php echo e(!empty($isEditing) ? 'Edit Grade' : 'Grade Submission'); ?></h3>
                        <p class="text-muted mb-0">
                            <?php echo e($activity->title); ?> · <?php echo e($submission->student->first_name); ?> <?php echo e($submission->student->last_name); ?>

                        </p>
                    </div>
                    <div class="col-auto text-end float-end ms-auto download-grp">
                        <a href="<?php echo e(route('lessons.activities.submissions', [$lesson, $activity])); ?>" class="btn btn-outline-primary me-2">
                            <i class="fas fa-arrow-left"></i> Back to Submissions
                        </a>
                        <button type="button" class="btn btn-success" onclick="saveGrade()">
                            <i class="fas fa-save"></i> <?php echo e(!empty($isEditing) ? 'Update Grade' : 'Save Grade'); ?>

                        </button>
                    </div>
                </div>
            </div>

            <!-- Student and Activity Info -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Student Information</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Name:</strong> <?php echo e($submission->student->first_name); ?> <?php echo e($submission->student->last_name); ?></p>
                                    <p><strong>Email:</strong> <?php echo e($submission->student->email); ?></p>
                                    <p><strong>Submitted:</strong> <?php echo e($submission->created_at->format('M d, Y H:i')); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Status:</strong> 
                                        <?php if($submission->status === 'submitted'): ?>
                                            <span class="badge bg-warning">Submitted</span>
                                        <?php elseif($submission->status === 'graded'): ?>
                                            <span class="badge bg-success">Graded</span>
                                        <?php endif; ?>
                                    </p>
                                    <?php if($submission->created_at->gt($activity->due_date)): ?>
                                        <p><strong>Late:</strong> <span class="badge bg-danger">Late Submission</span></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Activity Information</h5>
                            <p><strong>Activity:</strong> <?php echo e($activity->title); ?></p>
                            <p><strong>Due Date:</strong> <?php echo e($activity->due_date->format('M d, Y')); ?></p>
                            <p><strong>Instructions:</strong> <?php echo e(Str::limit($activity->instructions, 100)); ?></p>
                            <?php if($submission->file_path): ?>
                                <a href="<?php echo e(asset('storage/' . $submission->file_path)); ?>" target="_blank" class="btn btn-primary">
                                    <i class="fas fa-download"></i> Download Submission
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Grading Form -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <?php if($rubrics->count() > 0): ?>
                                <h5 class="card-title">Rubric Grading</h5>
                                <form id="gradingForm" action="<?php echo e(!empty($isEditing) ? route('lessons.activities.update-grade', [$lesson, $activity, $submission]) : route('lessons.activities.store-grade', [$lesson, $activity, $submission])); ?>" method="POST">
                                    <?php echo csrf_field(); ?>
                                    <?php if(!empty($isEditing)): ?>
                                        <?php echo method_field('PUT'); ?>
                                    <?php endif; ?>
                                    
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width: 25%;">Rubric Category</th>
                                                    <th style="width: 35%;">Description</th>
                                                    <th style="width: 15%;">Max Score</th>
                                                    <th style="width: 15%;">Weight</th>
                                                    <th style="width: 10%;">Score</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $__currentLoopData = $rubrics; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rubric): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <?php
                                                        $prefillScore = old(
                                                            'scores.' . $rubric->id,
                                                            ($existingScores[$rubric->id] ?? null) ?? 0
                                                        );
                                                    ?>
                                                    <tr>
                                                        <td>
                                                            <strong><?php echo e($rubric->category_name); ?></strong>
                                                        </td>
                                                        <td>
                                                            <?php echo e($rubric->description); ?>

                                                        </td>
                                                        <td class="text-center">
                                                            <strong><?php echo e($rubric->max_score); ?></strong>
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="badge bg-primary"><?php echo e($rubric->weight); ?>%</span>
                                                        </td>
                                                        <td>
                                                            <input type="number" 
                                                                   class="form-control score-input" 
                                                                   name="scores[<?php echo e($rubric->id); ?>]" 
                                                                   min="0" 
                                                                   max="<?php echo e($rubric->max_score); ?>" 
                                                                   value="<?php echo e($prefillScore); ?>"
                                                                   data-max="<?php echo e($rubric->max_score); ?>"
                                                                   data-weight="<?php echo e($rubric->weight); ?>"
                                                                   onchange="calculateTotal()">
                                                        </td>
                                                    </tr>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </tbody>
                                            <tfoot class="table-light">
                                                <tr>
                                                    <td colspan="3"></td>
                                                    <td class="text-center">
                                                        <strong>Total Weight: <span id="totalWeight">0</span>%</strong>
                                                    </td>
                                                    <td class="text-center">
                                                        <strong>Total Score: <span id="totalScore">0</span></strong>
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>

                                    <div class="row mt-4">
                                        <div class="col-md-6">
                                            <div class="card bg-light">
                                                <div class="card-body">
                                                    <h6 class="card-title">Grade Summary</h6>
                                                    <div class="row">
                                                        <div class="col-6">
                                                            <p><strong>Raw Score:</strong> <span id="rawScore">0</span></p>
                                                            <p><strong>Max Possible:</strong> <span id="maxPossible">0</span></p>
                                                        </div>
                                                        <div class="col-6">
                                                            <p><strong>Percentage:</strong> <span id="percentage">0%</span></p>
                                                            <p><strong>Letter Grade:</strong> <span id="letterGrade">-</span></p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="feedback">Teacher Feedback</label>
                                                <textarea class="form-control" id="feedback" name="feedback" rows="4" placeholder="Provide constructive feedback to the student..."><?php echo e(old('feedback', $submission->feedback)); ?></textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <input type="hidden" name="total_score" id="hiddenTotalScore" value="0">
                                    <input type="hidden" name="max_possible_score" id="hiddenMaxPossible" value="0">
                                    <input type="hidden" name="percentage" id="hiddenPercentage" value="0">
                                    <input type="hidden" name="letter_grade" id="hiddenLetterGrade" value="">
                                </form>
                            <?php else: ?>
                                <h5 class="card-title">Quick Grade</h5>
                                <p class="text-muted">Enter the score directly. Rubric setup is optional.</p>

                                <form id="gradingForm" action="<?php echo e(!empty($isEditing) ? route('lessons.activities.update-grade', [$lesson, $activity, $submission]) : route('lessons.activities.store-grade', [$lesson, $activity, $submission])); ?>" method="POST">
                                    <?php echo csrf_field(); ?>
                                    <?php if(!empty($isEditing)): ?>
                                        <?php echo method_field('PUT'); ?>
                                    <?php endif; ?>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group mb-3">
                                                <label class="form-label" for="total_score">Score <span class="text-danger">*</span></label>
                                                <input type="number"
                                                       step="0.01"
                                                       min="0"
                                                       class="form-control <?php $__errorArgs = ['total_score'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                                       id="total_score"
                                                       name="total_score"
                                                       value="<?php echo e(old('total_score', $submission->total_score ?? '')); ?>"
                                                       required
                                                       oninput="updateSimpleGrade()">
                                                <?php $__errorArgs = ['total_score'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group mb-3">
                                                <label class="form-label" for="max_possible_score">Max Score <span class="text-danger">*</span></label>
                                                <input type="number"
                                                       step="0.01"
                                                       min="1"
                                                       class="form-control <?php $__errorArgs = ['max_possible_score'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                                       id="max_possible_score"
                                                       name="max_possible_score"
                                                       value="<?php echo e(old('max_possible_score', $submission->max_possible_score ?? 100)); ?>"
                                                       required
                                                       oninput="updateSimpleGrade()">
                                                <?php $__errorArgs = ['max_possible_score'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="card bg-light h-100">
                                                <div class="card-body">
                                                    <p class="mb-1"><strong>Percentage:</strong> <span id="simplePercentage">0%</span></p>
                                                    <p class="mb-0"><strong>Letter Grade:</strong> <span id="simpleLetterGrade" class="badge bg-secondary">-</span></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group mt-3">
                                        <label class="form-label" for="feedback">Teacher Feedback (optional)</label>
                                        <textarea class="form-control" id="feedback" name="feedback" rows="4" placeholder="Provide constructive feedback to the student..."><?php echo e(old('feedback', $submission->feedback)); ?></textarea>
                                    </div>
                                    <div class="mt-2">
                                        <small class="text-muted">
                                            Want detailed criteria later?
                                            <a href="<?php echo e(route('lessons.activities.rubric', [$lesson, $activity])); ?>">Set up rubrics</a> (optional).
                                        </small>
                                    </div>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Grade History (if previously graded) -->
            <?php if($submission->status === 'graded' && $submission->max_possible_score): ?>
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Previous Grade</h5>
                                <div class="row">
                                    <div class="col-md-3">
                                        <p><strong>Previous Score:</strong> <?php echo e($submission->total_score); ?>/<?php echo e($submission->max_possible_score); ?></p>
                                    </div>
                                    <div class="col-md-3">
                                        <p><strong>Previous Percentage:</strong> <?php echo e(number_format($submission->percentage ?? 0, 1)); ?>%</p>
                                    </div>
                                    <div class="col-md-3">
                                        <p><strong>Previous Grade:</strong> <span class="badge bg-<?php echo e($submission->letter_grade_color); ?>"><?php echo e($submission->letter_grade); ?></span></p>
                                    </div>
                                    <div class="col-md-3">
                                        <p><strong>Graded On:</strong> <?php echo e(optional($submission->graded_at)->format('M d, Y H:i') ?? 'N/A'); ?></p>
                                    </div>
                                </div>
                                <?php if($submission->feedback): ?>
                                    <div class="mt-3">
                                        <strong>Previous Feedback:</strong>
                                        <p class="text-muted"><?php echo e($submission->feedback); ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
    .table th {
        background-color: #f8f9fa;
        border-color: #dee2e6;
    }
    
    .score-input {
        text-align: center;
        font-weight: 600;
    }
    
    .score-input:focus {
        border-color: #3d5ee1;
        box-shadow: 0 0 0 0.2rem rgba(61, 94, 225, 0.25);
    }
    
    .badge.bg-primary {
        background-color: #3d5ee1 !important;
    }
    
    .badge.bg-success {
        background-color: #7bb13c !important;
    }
    
    .badge.bg-warning {
        background-color: #ffc107 !important;
        color: #000 !important;
    }
    
    .badge.bg-danger {
        background-color: #dc3545 !important;
    }
    
    .card.bg-light {
        background-color: #f8f9fa !important;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
$(document).ready(function() {
    if ($('.score-input').length) {
        calculateTotal();
        $('.score-input').on('input', function() {
            let value = parseFloat($(this).val());
            let max = parseFloat($(this).data('max'));
            if (value > max) {
                $(this).val(max);
            } else if (value < 0) {
                $(this).val(0);
            }
            calculateTotal();
        });
    } else {
        updateSimpleGrade();
    }
});

function updateSimpleGrade() {
    let score = parseFloat($('#total_score').val()) || 0;
    let max = parseFloat($('#max_possible_score').val()) || 0;
    let percentage = max > 0 ? (score / max) * 100 : 0;
    let letterGrade = getLetterGrade(percentage);

    $('#simplePercentage').text(percentage.toFixed(1) + '%');
    $('#simpleLetterGrade').text(letterGrade).removeClass().addClass('badge bg-' + getLetterGradeColor(letterGrade));
}

function calculateTotal() {
    let totalScore = 0;
    let totalWeight = 0;
    let maxPossible = 0;
    let weightedPct = 0;

    $('.score-input').each(function() {
        let score = parseFloat($(this).val()) || 0;
        let max = parseFloat($(this).data('max')) || 0;
        let weight = parseFloat($(this).data('weight')) || 0;

        totalScore += score;
        totalWeight += weight;
        maxPossible += max;

        if (max > 0 && weight > 0) {
            weightedPct += (score / max) * weight;
        }
    });

    // If weights don't total 100, treat them as relative proportions
    let percentage;
    if (totalWeight > 0 && totalWeight !== 100) {
        percentage = (weightedPct / totalWeight) * 100;
    } else if (totalWeight === 100) {
        percentage = weightedPct;
    } else {
        percentage = maxPossible > 0 ? (totalScore / maxPossible) * 100 : 0;
    }

    let letterGrade = getLetterGrade(percentage);

    $('#totalScore').text(totalScore);
    $('#totalWeight').text(totalWeight);
    $('#rawScore').text(totalScore);
    $('#maxPossible').text(maxPossible);
    $('#percentage').text(percentage.toFixed(1) + '%');
    $('#letterGrade').text(letterGrade).removeClass().addClass('badge bg-' + getLetterGradeColor(letterGrade));

    let weightNote = totalWeight === 100
        ? ''
        : (totalWeight > 0
            ? ' <small class="text-muted">(normalized from ' + totalWeight + '%)</small>'
            : '');
    $('#totalWeight').closest('strong').find('small').remove();
    if (weightNote) {
        $('#totalWeight').parent().append(weightNote);
    }

    $('#hiddenTotalScore').val(totalScore);
    $('#hiddenMaxPossible').val(maxPossible);
    $('#hiddenPercentage').val(percentage.toFixed(1));
    $('#hiddenLetterGrade').val(letterGrade);
}

function getLetterGrade(percentage) {
    if (percentage >= 90) return 'A';
    if (percentage >= 85) return 'B+';
    if (percentage >= 80) return 'B';
    if (percentage >= 75) return 'C+';
    if (percentage >= 70) return 'C';
    if (percentage >= 65) return 'D+';
    if (percentage >= 60) return 'D';
    return 'F';
}

function getLetterGradeColor(letterGrade) {
    switch(letterGrade) {
        case 'A': return 'success';
        case 'B+': return 'success';
        case 'B': return 'primary';
        case 'C+': return 'primary';
        case 'C': return 'warning';
        case 'D+': return 'warning';
        case 'D': return 'danger';
        case 'F': return 'danger';
        default: return 'secondary';
    }
}

function saveGrade() {
    if ($('.score-input').length) {
        let hasScore = false;
        $('.score-input').each(function() {
            if ($(this).val() !== '' && $(this).val() !== null) {
                hasScore = true;
            }
        });
        if (!hasScore) {
            alert('Please enter at least one rubric score.');
            return;
        }
        calculateTotal();
    } else {
        let score = parseFloat($('#total_score').val());
        let max = parseFloat($('#max_possible_score').val());
        if (isNaN(score) || isNaN(max) || max < 1) {
            alert('Please enter a valid score and max score.');
            return;
        }
        if (score > max) {
            alert('Score cannot be higher than the maximum score.');
            return;
        }
    }

    let feedback = ($('#feedback').val() || '').trim();
    if (!feedback) {
        if (!confirm('No feedback provided. Continue anyway?')) {
            return;
        }
    }

    $('#gradingForm').submit();
}
</script>
<?php $__env->stopPush(); ?> 
<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views\activities\grade-submission.blade.php ENDPATH**/ ?>