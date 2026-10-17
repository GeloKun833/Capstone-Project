<div class="modal fade" id="quizModal" tabindex="-1" aria-labelledby="quizModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h4 class="modal-title" id="quizModalTitle">Quiz</h4>
                    <p class="text-muted small mb-0" id="quizClassSummary">The class you selected is shown here.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @if($quizLocked)
                    <div class="alert alert-warning">Students have already started this quiz. You can update the wording, dates, and instructions. Questions, points, and the answer key stay as they are.</div>
                @endif
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="quizTitle">Quiz title</label>
                        <input type="text" class="form-control" id="quizTitle" maxlength="255">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Grade level and section</label>
                        <input type="text" class="form-control" id="quizClassLock" readonly>
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="quizInstructions">Quiz instructions</label>
                        <textarea class="form-control" id="quizInstructions" rows="3"></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Course and subject</label>
                        <input type="text" class="form-control" id="quizSubjectLock" readonly>
                    </div>
                    <div class="col-12">
                        <p class="text-muted small mb-0">Students can see the quiz after you publish it. They can start answering at this time.</p>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="available_date">Start of the Quiz</label>
                        <input type="date" class="form-control" id="available_date" name="available_date" min="{{ $schoolToday }}" value="{{ $availableDate }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="available_time">Time</label>
                        <input type="time" class="form-control" id="available_time" name="available_time" value="{{ $availableTime }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="quizDueDate">Due date</label>
                        <input type="date" class="form-control" id="quizDueDate" min="{{ $schoolToday }}">
                        <small class="text-muted">Today or a later date. The due time cannot be earlier than now.</small>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="quizDueTime">Due time</label>
                        <input type="time" class="form-control" id="quizDueTime">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="time_limit_minutes">Time limit in minutes</label>
                        <input type="number" class="form-control" id="time_limit_minutes" name="time_limit_minutes" min="1" max="180" placeholder="Optional"
                               value="{{ old('time_limit_minutes', $isEdit ? $activity->time_limit_minutes : '') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="attempts_allowed">Attempts allowed</label>
                        <input type="number" class="form-control" id="attempts_allowed" name="attempts_allowed" min="1" max="10"
                               value="{{ old('attempts_allowed', $isEdit ? ($activity->attempts_allowed ?: 1) : 1) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="passing_score">Passing score (%)</label>
                        <input type="number" class="form-control" id="passing_score" name="passing_score" min="1" max="100"
                               value="{{ old('passing_score', $isEdit ? $activity->passing_score : 75) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="quizTotalPoints">Total points</label>
                        <input type="text" class="form-control" id="quizTotalPoints" readonly value="0">
                    </div>
                    <div class="col-md-4">
                        <label class="act-toggle w-100">
                            <input type="checkbox" name="shuffle_questions" value="1" @checked(old('shuffle_questions', $isEdit && $activity->shuffle_questions))>
                            <span><strong>Shuffle questions</strong><small>Each attempt gets a new question order.</small></span>
                        </label>
                    </div>
                    <div class="col-md-4">
                        <label class="act-toggle w-100">
                            <input type="checkbox" name="shuffle_choices" value="1" @checked(old('shuffle_choices', $isEdit && $activity->shuffle_choices))>
                            <span><strong>Shuffle answer choices</strong><small>Choice order changes, the answer key does not.</small></span>
                        </label>
                    </div>
                    <div class="col-md-4">
                        <label class="act-toggle w-100">
                            <input type="checkbox" name="show_correct_answers" value="1" @checked(old('show_correct_answers', $isEdit && $activity->show_correct_answers))>
                            <span><strong>Show correct answers after submission</strong><small>Students see the key only after they submit.</small></span>
                        </label>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-4 mb-2">
                    <h5 class="mb-0">Questions <span class="text-muted" id="quizCount"></span></h5>
                    <strong id="quizTotal">Total: 0 points</strong>
                </div>
                <div id="quizQuestions" class="d-grid gap-3"></div>
                @unless($quizLocked)
                    <button type="button" class="btn btn-outline-primary mt-3" id="addQuestion">Add question</button>
                @endunless
                <div class="d-flex flex-wrap gap-2 mt-4">
                    <button type="button" class="btn btn-outline-secondary" id="previewQuiz">Preview quiz</button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-outline-secondary" name="intent" value="draft">Save as draft</button>
                <button type="submit" class="btn btn-primary" name="intent" value="publish">Publish quiz</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="quizPreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Student preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="quizPreviewBody"></div>
        </div>
    </div>
</div>
