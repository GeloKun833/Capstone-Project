@php
    $editing = isset($announcement) && $announcement;
    $selected = array_map('intval', (array) old('section_ids', $editing ? ($announcement->target_sections ?? []) : []));
    $tz = config('app.school_timezone', 'Asia/Manila');
    $scheduledValue = old('scheduled_at');
    $expiresValue = old('expires_at');
    if ($scheduledValue === null && $editing && $announcement->scheduled_at) {
        $scheduledValue = $announcement->scheduled_at->copy()->timezone($tz)->format('Y-m-d\TH:i');
    }
    if ($expiresValue === null && $editing && $announcement->expires_at) {
        $expiresValue = $announcement->expires_at->copy()->timezone($tz)->format('Y-m-d\TH:i');
    }
    $priorityValue = old('priority', $editing ? $announcement->priority : 'normal');
    $pinned = (bool) old('is_pinned', $editing ? $announcement->is_pinned : false);
    $optionsOpen = filled($scheduledValue) || filled($expiresValue) || $pinned || $priorityValue !== 'normal';
    $grouped = $sections->groupBy(fn ($section) => $section->grade_level ?: 'Section');
    $me = Auth::user();
@endphp
<form action="{{ $formAction }}" method="POST" id="announcementForm" enctype="multipart/form-data" class="gc-card gc-composer">
    @csrf
    @if($editing)
        @method('PUT')
    @endif
    <div class="gc-card-body">
        <div class="gc-head">
            <img class="gc-avatar" src="{{ $me->photoUrl() }}" alt="">
            <div class="gc-head-text">
                <div class="gc-name">{{ $me->name }}</div>
                <div class="gc-meta">{{ $editing ? 'Editing this announcement' : 'New announcement' }}</div>
            </div>
        </div>

        <div class="gc-field">
            <label for="gcTitle">Title</label>
            <input id="gcTitle" type="text" name="title" value="{{ old('title', $editing ? $announcement->title : '') }}" placeholder="What is this about?" required>
            @error('title')<div class="gc-error">{{ $message }}</div>@enderror
        </div>
        <div class="gc-field">
            <label for="gcBody">Message</label>
            <textarea id="gcBody" name="content" placeholder="Announce something to your class" required>{{ old('content', $editing ? $announcement->content : '') }}</textarea>
            @error('content')<div class="gc-error">{{ $message }}</div>@enderror
        </div>

        <section class="gc-classes" aria-labelledby="gcClassHeading">
            <div class="gc-classes-head">
                <div>
                    <h2 id="gcClassHeading">Who should see this?</h2>
                    <p>{{ $me->role_name === 'Teacher' ? 'Choose a grade and section assigned to you.' : 'Choose the grade and section for this announcement.' }}</p>
                </div>
                @if($sections->isNotEmpty())
                    <button type="button" class="gc-text-btn" id="gcSelectAll">Select all</button>
                @endif
            </div>
            @if($sections->isEmpty())
                <p class="gc-note mb-0">No grade or section is assigned to you yet.</p>
            @else
                @foreach($grouped as $grade => $group)
                    <div class="gc-grade">
                        <div class="gc-grade-name">{{ $grade }}</div>
                        <div class="gc-chips">
                            @foreach($group as $section)
                                <label class="gc-chip">
                                    <input type="checkbox" name="section_ids[]" value="{{ $section->id }}" {{ in_array((int) $section->id, $selected, true) ? 'checked' : '' }}>
                                    <span>{{ $section->name }}</span>
                                </label>
                            @endforeach
                        </div>
                        @if($group->count() > 1)
                            <button type="button" class="gc-text-btn gc-grade-all">All</button>
                        @endif
                    </div>
                @endforeach
            @endif
            <p class="gc-picked" id="gcPicked"></p>
            <div class="gc-error" id="gcClassError" hidden>Choose at least one grade and section.</div>
            @error('section_ids')<div class="gc-error">{{ $message }}</div>@enderror
        </section>

        <div class="gc-extra" id="gcExtra" @if(! $optionsOpen) hidden @endif>
            <div class="gc-extra-grid">
                <label class="gc-option">
                    <span>Priority</span>
                    <select name="priority">
                        @foreach(['low' => 'Low', 'normal' => 'Normal', 'high' => 'High', 'urgent' => 'Urgent'] as $value => $label)
                            <option value="{{ $value }}" {{ $priorityValue === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="gc-option">
                    <span>Publish later</span>
                    <input type="datetime-local" name="scheduled_at" value="{{ $scheduledValue }}">
                </label>
                <label class="gc-option">
                    <span>Remove after</span>
                    <input type="datetime-local" name="expires_at" value="{{ $expiresValue }}">
                </label>
                <label class="gc-option gc-option-check">
                    <input type="checkbox" name="is_pinned" value="1" {{ $pinned ? 'checked' : '' }}>
                    <span>Pin to the top of the stream</span>
                </label>
            </div>
            @error('expires_at')<div class="gc-error">{{ $message }}</div>@enderror
        </div>

        @if($editing)
            @php $files = $announcement->attachments ?? []; @endphp
            @if(!empty($files))
                <div class="gc-files">
                    @foreach($files as $file)
                        <label class="gc-file mb-0">
                            <input type="checkbox" name="remove_attachments[]" value="{{ $file['path'] ?? '' }}">
                            Remove {{ $file['original_name'] ?? basename($file['path'] ?? 'file') }}
                        </label>
                    @endforeach
                </div>
            @endif
        @endif
        <ul class="gc-file-list" id="gcFileList" hidden></ul>
        @error('attachments.*')<div class="gc-error">{{ $message }}</div>@enderror
    </div>
    <div class="gc-bar">
        <label class="gc-icon-btn" title="Attach files">
            <i class="fas fa-paperclip"></i>
            <input id="gcFiles" type="file" name="attachments[]" class="d-none" multiple accept=".pdf,.docx,.jpg,.jpeg,.png,.gif,.webp,.txt,.xls,.xlsx,.ppt,.pptx,image/*">
        </label>
        <button type="button" class="gc-text-btn" id="gcMoreToggle" aria-expanded="{{ $optionsOpen ? 'true' : 'false' }}" aria-controls="gcExtra">
            <i class="fas fa-sliders-h"></i> Options
        </button>
        <span class="gc-count" id="gcCount"></span>
        <input type="hidden" name="is_scheduled" value="0">
        <a class="gc-cancel" href="{{ $editing ? route('announcements.show', $announcement->id) : route('announcements.index') }}">Cancel</a>
        <button type="submit" class="gc-post-btn">{{ $editing ? 'Save' : 'Post' }}</button>
    </div>
</form>

@push('scripts')
<script>
    (function () {
        const form = document.getElementById('announcementForm');
        if (!form) return;
        const boxes = function () { return form.querySelectorAll('input[name="section_ids[]"]'); };
        const picked = document.getElementById('gcPicked');
        const count = document.getElementById('gcCount');
        const classError = document.getElementById('gcClassError');
        const selectAll = document.getElementById('gcSelectAll');

        function labels() {
            return Array.from(boxes()).filter(function (box) { return box.checked; }).map(function (box) {
                const grade = box.closest('.gc-grade')?.querySelector('.gc-grade-name')?.textContent?.trim() || '';
                const name = box.parentElement?.querySelector('span')?.textContent?.trim() || '';
                return grade && name && grade !== name ? grade + ' · ' + name : (name || grade);
            });
        }

        function refresh() {
            const names = labels();
            if (picked) picked.textContent = names.length ? names.join(', ') : 'No class selected yet.';
            if (count) count.textContent = names.length ? names.length + (names.length === 1 ? ' class' : ' classes') : '';
            if (selectAll) selectAll.textContent = names.length && names.length === boxes().length ? 'Clear all' : 'Select all';
            form.querySelectorAll('.gc-grade').forEach(function (grade) {
                const gradeBoxes = grade.querySelectorAll('input[name="section_ids[]"]');
                const button = grade.querySelector('.gc-grade-all');
                if (!button || !gradeBoxes.length) return;
                const allOn = Array.from(gradeBoxes).every(function (box) { return box.checked; });
                button.textContent = allOn ? 'Clear' : 'All';
            });
        }

        form.addEventListener('change', function (event) {
            if (event.target && event.target.name === 'section_ids[]') {
                if (classError) classError.hidden = true;
                refresh();
            }
        });

        selectAll?.addEventListener('click', function () {
            const all = boxes();
            const turnOn = Array.from(all).some(function (box) { return !box.checked; });
            all.forEach(function (box) { box.checked = turnOn; });
            if (classError) classError.hidden = true;
            refresh();
        });

        form.querySelectorAll('.gc-grade-all').forEach(function (button) {
            button.addEventListener('click', function () {
                const gradeBoxes = button.closest('.gc-grade').querySelectorAll('input[name="section_ids[]"]');
                const turnOn = Array.from(gradeBoxes).some(function (box) { return !box.checked; });
                gradeBoxes.forEach(function (box) { box.checked = turnOn; });
                if (classError) classError.hidden = true;
                refresh();
            });
        });

        document.getElementById('gcMoreToggle')?.addEventListener('click', function () {
            const panel = document.getElementById('gcExtra');
            if (!panel) return;
            const open = panel.hasAttribute('hidden');
            if (open) panel.removeAttribute('hidden');
            else panel.setAttribute('hidden', '');
            this.setAttribute('aria-expanded', open ? 'true' : 'false');
        });

        document.getElementById('gcFiles')?.addEventListener('change', function () {
            const list = document.getElementById('gcFileList');
            if (!list) return;
            list.innerHTML = '';
            Array.from(this.files || []).forEach(function (file) {
                const item = document.createElement('li');
                item.textContent = file.name;
                list.appendChild(item);
            });
            list.hidden = !this.files || this.files.length === 0;
        });

        form.addEventListener('submit', function (event) {
            const chosen = form.querySelectorAll('input[name="section_ids[]"]:checked');
            if (chosen.length === 0) {
                event.preventDefault();
                if (classError) classError.hidden = false;
                form.querySelector('.gc-classes')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }
            const scheduled = form.querySelector('input[name="scheduled_at"]');
            const flag = form.querySelector('input[name="is_scheduled"]');
            if (flag && scheduled && scheduled.value) flag.value = '1';
        });

        refresh();
    })();
</script>
@endpush
