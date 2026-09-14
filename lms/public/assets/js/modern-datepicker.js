/**
 * Modern date fields — open on click/focus, enforce DOB / event limits,
 * and academic year range selectors.
 */
(function (window, document, $) {
    'use strict';

    var TODAY = (function () {
        var d = new Date();
        return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
    })();

    function isDobField(el) {
        if (!el) return false;
        if (el.classList.contains('js-dob')) return true;
        var name = (el.getAttribute('name') || '').toLowerCase();
        var id = (el.getAttribute('id') || '').toLowerCase();
        return name === 'date_of_birth' || id === 'date_of_birth' || name.indexOf('date_of_birth') !== -1;
    }

    function isEventField(el) {
        return el && (
            el.classList.contains('js-event-date') ||
            el.classList.contains('js-event-datetime') ||
            el.classList.contains('js-event-start') ||
            el.classList.contains('js-event-end') ||
            /^(start_time|end_time|recurrence_end_date)$/i.test(el.getAttribute('name') || '') ||
            /^(form_start_time|form_end_time|form_recurrence_end_date|slot_date)$/i.test(el.id || '')
        );
    }

    function wrapField(el) {
        if (!el || el.parentElement && el.parentElement.classList.contains('mdp-input-wrap')) return;
        var wrap = document.createElement('div');
        wrap.className = 'mdp-input-wrap';
        el.parentNode.insertBefore(wrap, el);
        wrap.appendChild(el);
        var group = wrap.parentElement;
        if (group && (group.classList.contains('form-group') || group.classList.contains('mb-3') || group.classList.contains('calendar-icon'))) {
            group.classList.add('mdp-field', 'mdp-ready');
        }
    }

    function blockManualTyping(el) {
        el.addEventListener('keydown', function (e) {
            // Allow tab/escape/arrows; block typing letters/numbers
            var allowed = ['Tab', 'Escape', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Home', 'End', 'Backspace', 'Delete'];
            if (allowed.indexOf(e.key) !== -1) return;
            if (e.ctrlKey || e.metaKey) return;
            e.preventDefault();
        });
        el.setAttribute('autocomplete', 'off');
        el.setAttribute('inputmode', 'none');
    }

    function openPicker(el) {
        try {
            if (typeof el.showPicker === 'function') {
                el.showPicker();
                return;
            }
        } catch (err) {}
        if ($ && $.fn.datetimepicker && $(el).data('DateTimePicker')) {
            $(el).data('DateTimePicker').show();
        }
    }

    function isDatetimeField(el) {
        if (!el) return false;
        if (el.type === 'datetime-local' || el.dataset.mdpOrig === 'datetime-local') return true;
        if (el.classList.contains('js-event-datetime') || el.classList.contains('js-event-start') || el.classList.contains('js-event-end')) {
            return !isAllDayMode();
        }
        var key = ((el.getAttribute('name') || '') + ' ' + (el.id || '')).toLowerCase();
        return /(start_time|end_time|scheduled_at|expires_at)/.test(key);
    }

    function fieldMinDate(el) {
        var min = el && el.getAttribute('min');
        if (min) return parseEventDate(min);
        if (isDobField(el)) return parseEventDate('1950-01-01');
        return null;
    }

    function fieldMaxDate(el) {
        var max = el && el.getAttribute('max');
        if (max) return parseEventDate(max);
        if (isDobField(el)) {
            var t = new Date();
            t.setHours(23, 59, 59, 999);
            return t;
        }
        return null;
    }

    function dateOutOfRange(el, year, month, day) {
        var dt = new Date(year, month, day, 12, 0, 0, 0);
        var min = fieldMinDate(el);
        var max = fieldMaxDate(el);
        if (min) {
            var a = new Date(min);
            a.setHours(0, 0, 0, 0);
            if (dt < a) return true;
        }
        if (max) {
            var b = new Date(max);
            b.setHours(23, 59, 59, 999);
            if (dt > b) return true;
        }
        return false;
    }

    function enhanceNativeDate(el) {
        bindDateInput(el);
    }

    function bindDateInput(el) {
        if (!el || el.tagName !== 'INPUT') return;
        if ((el.getAttribute('name') || '') === 'qualification') {
            el.classList.remove('datetimepicker');
            return;
        }

        var origType = el.dataset.mdpOrig || el.getAttribute('type') || 'text';
        var raw = (el.value || '').trim();
        if (/^\d{2}-\d{2}-\d{4}$/.test(raw)) {
            var parts = raw.split('-');
            raw = parts[2] + '-' + parts[1] + '-' + parts[0];
        }
        if (/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}/.test(raw)) {
            raw = raw.replace('T', ' ').slice(0, 16);
        }

        if (isDobField(el)) {
            el.classList.add('js-dob');
            if (!el.getAttribute('max')) el.setAttribute('max', TODAY);
            if (!el.getAttribute('min')) el.setAttribute('min', '1950-01-01');
        }

        var datetime = origType === 'datetime-local' || el.classList.contains('js-event-datetime') ||
            el.classList.contains('js-event-start') || el.classList.contains('js-event-end') ||
            isDatetimeField(el);

        el.dataset.mdpOrig = origType;
        el.dataset.mdpMode = datetime ? 'datetime' : 'date';
        el.setAttribute('type', 'text');
        el.classList.remove('datetimepicker');
        el.readOnly = true;
        el.placeholder = datetime && !isAllDayMode() ? 'YYYY-MM-DD HH:mm' : 'YYYY-MM-DD';
        if (raw) el.value = raw;

        wrapField(el);

        try {
            if ($ && $(el).data('DateTimePicker')) $(el).data('DateTimePicker').destroy();
        } catch (e) {}

        if (el.dataset.mdpBound === '1') return;
        el.dataset.mdpBound = '1';
        blockManualTyping(el);
        el.addEventListener('focus', function () { openEventPopover(el); });
        el.addEventListener('click', function () { openEventPopover(el); });
    }

    function convertEventDatetimeInput(el) {
        bindDateInput(el);
    }

    function pad2(n) {
        return String(n).padStart(2, '0');
    }

    function isAllDayMode() {
        var el = document.getElementById('form_is_all_day') || document.getElementById('is_all_day');
        return !!(el && el.checked);
    }

    function formatEventDate(d, allDay) {
        var out = d.getFullYear() + '-' + pad2(d.getMonth() + 1) + '-' + pad2(d.getDate());
        if (allDay) return out;
        return out + ' ' + pad2(d.getHours()) + ':' + pad2(d.getMinutes());
    }

    function parseEventDate(str) {
        var now = new Date();
        if (!str) return now;
        str = String(str).trim().replace('T', ' ');
        var m = str.match(/^(\d{4})-(\d{2})-(\d{2})(?:[ T](\d{2}):(\d{2}))?/);
        if (!m) return now;
        return new Date(
            parseInt(m[1], 10),
            parseInt(m[2], 10) - 1,
            parseInt(m[3], 10),
            parseInt(m[4] || '0', 10),
            parseInt(m[5] || '0', 10),
            0,
            0
        );
    }

    var eventPopover = null;
    var eventTarget = null;
    var eventCursor = new Date();
    var eventViewMonth = new Date();

    function hideOpenEventPickers() {
        if (eventPopover) {
            eventPopover.classList.remove('is-open');
        }
        eventTarget = null;
        if ($ && $.fn.datetimepicker) {
            $('.bootstrap-datetimepicker-widget').hide();
        }
    }

    function ensureEventPopover() {
        if (eventPopover) return eventPopover;

        var pop = document.createElement('div');
        pop.id = 'mdpEventPopover';
        pop.className = 'mdp-event-popover';
        pop.setAttribute('role', 'dialog');
        pop.innerHTML =
            '<div class="mdp-event-cal">' +
                '<div class="mdp-event-cal-head">' +
                    '<button type="button" class="mdp-event-nav" data-mdp="prev" aria-label="Previous month"><i class="fas fa-angle-left"></i></button>' +
                    '<div class="mdp-event-month" data-mdp="month-label"></div>' +
                    '<button type="button" class="mdp-event-nav" data-mdp="next" aria-label="Next month"><i class="fas fa-angle-right"></i></button>' +
                '</div>' +
                '<div class="mdp-event-weekdays"><span>Su</span><span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span>Sa</span></div>' +
                '<div class="mdp-event-days" data-mdp="days"></div>' +
                '<div class="mdp-event-cal-foot">' +
                    '<button type="button" data-mdp="today">Today</button>' +
                    '<button type="button" data-mdp="clear">Clear</button>' +
                '</div>' +
            '</div>' +
            '<div class="mdp-event-time" data-mdp="time">' +
                '<div class="mdp-event-spin">' +
                    '<button type="button" data-mdp="hour-up" aria-label="Hour up"><i class="fas fa-angle-up"></i></button>' +
                    '<div class="mdp-event-spin-val" data-mdp="hour">00</div>' +
                    '<button type="button" data-mdp="hour-down" aria-label="Hour down"><i class="fas fa-angle-down"></i></button>' +
                '</div>' +
                '<div class="mdp-event-colon">:</div>' +
                '<div class="mdp-event-spin">' +
                    '<button type="button" data-mdp="min-up" aria-label="Minute up"><i class="fas fa-angle-up"></i></button>' +
                    '<div class="mdp-event-spin-val" data-mdp="min">00</div>' +
                    '<button type="button" data-mdp="min-down" aria-label="Minute down"><i class="fas fa-angle-down"></i></button>' +
                '</div>' +
            '</div>';

        document.body.appendChild(pop);
        eventPopover = pop;

        pop.addEventListener('mousedown', function (e) {
            e.preventDefault();
        });

        pop.addEventListener('click', function (e) {
            var btn = e.target.closest('[data-mdp]');
            if (!btn) return;
            var action = btn.getAttribute('data-mdp');
            if (action === 'prev') {
                eventViewMonth.setMonth(eventViewMonth.getMonth() - 1);
                renderEventPopover();
            } else if (action === 'next') {
                eventViewMonth.setMonth(eventViewMonth.getMonth() + 1);
                renderEventPopover();
            } else if (action === 'today') {
                eventCursor = new Date();
                eventCursor.setSeconds(0, 0);
                eventCursor.setMinutes(Math.round(eventCursor.getMinutes() / 5) * 5);
                eventViewMonth = new Date(eventCursor);
                commitEventValue();
                renderEventPopover();
            } else if (action === 'clear') {
                if (eventTarget) {
                    eventTarget.value = '';
                    eventTarget.dispatchEvent(new Event('change', { bubbles: true }));
                    if ($) $(eventTarget).trigger('change');
                }
                hideOpenEventPickers();
            } else if (action === 'hour-up') {
                eventCursor.setHours(eventCursor.getHours() + 1);
                commitEventValue();
                renderEventPopover();
            } else if (action === 'hour-down') {
                eventCursor.setHours(eventCursor.getHours() - 1);
                commitEventValue();
                renderEventPopover();
            } else if (action === 'min-up') {
                eventCursor.setMinutes(eventCursor.getMinutes() + 5);
                commitEventValue();
                renderEventPopover();
            } else if (action === 'min-down') {
                eventCursor.setMinutes(eventCursor.getMinutes() - 5);
                commitEventValue();
                renderEventPopover();
            } else if (action === 'day') {
                var y = parseInt(btn.getAttribute('data-y'), 10);
                var mo = parseInt(btn.getAttribute('data-m'), 10);
                var d = parseInt(btn.getAttribute('data-d'), 10);
                eventCursor.setFullYear(y, mo, d);
                commitEventValue();
                renderEventPopover();
            }
        });

        document.addEventListener('mousedown', function (e) {
            if (!eventPopover || !eventPopover.classList.contains('is-open')) return;
            if (eventPopover.contains(e.target)) return;
            if (eventTarget && (e.target === eventTarget || eventTarget.contains(e.target))) return;
            hideOpenEventPickers();
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') hideOpenEventPickers();
        });

        window.addEventListener('scroll', function () {
            if (eventTarget) positionEventPopover(eventTarget);
        }, true);

        return pop;
    }

    function fieldUsesTime(el) {
        if (!el) return false;
        if (el.classList.contains('js-event-start') || el.classList.contains('js-event-end') || el.classList.contains('js-event-datetime') ||
            el.id === 'form_start_time' || el.id === 'form_end_time') {
            return !isAllDayMode();
        }
        return el.dataset.mdpMode === 'datetime' || el.dataset.mdpOrig === 'datetime-local';
    }

    function commitEventValue() {
        if (!eventTarget) return;
        var dateOnly = !fieldUsesTime(eventTarget);
        var val = formatEventDate(eventCursor, dateOnly);
        if (eventTarget.dataset.mdpOrig === 'datetime-local' && !dateOnly) {
            val = val.replace(' ', 'T');
        }
        eventTarget.value = val;
        eventTarget.dispatchEvent(new Event('input', { bubbles: true }));
        eventTarget.dispatchEvent(new Event('change', { bubbles: true }));
        if ($) $(eventTarget).trigger('change');
    }

    function renderEventPopover() {
        var pop = ensureEventPopover();
        var monthLabel = pop.querySelector('[data-mdp="month-label"]');
        var daysEl = pop.querySelector('[data-mdp="days"]');
        var timeEl = pop.querySelector('[data-mdp="time"]');
        var hourEl = pop.querySelector('[data-mdp="hour"]');
        var minEl = pop.querySelector('[data-mdp="min"]');
        var showTime = fieldUsesTime(eventTarget);

        var months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        monthLabel.textContent = months[eventViewMonth.getMonth()] + ' ' + eventViewMonth.getFullYear();
        timeEl.style.display = showTime ? 'flex' : 'none';
        hourEl.textContent = pad2(eventCursor.getHours());
        minEl.textContent = pad2(eventCursor.getMinutes());

        var year = eventViewMonth.getFullYear();
        var month = eventViewMonth.getMonth();
        var first = new Date(year, month, 1);
        var startDow = first.getDay();
        var daysInMonth = new Date(year, month + 1, 0).getDate();
        var today = new Date();
        var html = '';
        var i;
        for (i = 0; i < startDow; i++) html += '<span class="is-empty"></span>';
        for (i = 1; i <= daysInMonth; i++) {
            var isSel = eventCursor.getFullYear() === year && eventCursor.getMonth() === month && eventCursor.getDate() === i;
            var isToday = today.getFullYear() === year && today.getMonth() === month && today.getDate() === i;
            var disabled = eventTarget && dateOutOfRange(eventTarget, year, month, i);
            html += '<button type="button" data-mdp="day" data-y="' + year + '" data-m="' + month + '" data-d="' + i + '"' +
                (disabled ? ' disabled' : '') +
                ' class="' + (isSel ? 'is-selected' : '') + (isToday ? ' is-today' : '') + (disabled ? ' is-disabled' : '') + '">' + i + '</button>';
        }
        daysEl.innerHTML = html;
        if (eventTarget) positionEventPopover(eventTarget);
    }

    function positionEventPopover(input) {
        var pop = ensureEventPopover();
        pop.classList.add('is-open');
        var rect = input.getBoundingClientRect();
        var gap = 8;
        var pad = 12;
        var vw = window.innerWidth;
        var vh = window.innerHeight;
        var w = pop.offsetWidth || 360;
        var h = pop.offsetHeight || 320;

        var left = rect.left;
        if (left + w > vw - pad) left = rect.right - w;
        if (left < pad) left = pad;

        var top = rect.bottom + gap;
        if (top + h > vh - pad && rect.top - gap - h > pad) {
            top = rect.top - gap - h;
        }
        if (top < pad) top = pad;
        if (top + h > vh - pad) {
            top = Math.max(pad, vh - h - pad);
        }

        pop.style.left = Math.round(left) + 'px';
        pop.style.top = Math.round(top) + 'px';
    }

    function openEventPopover(el) {
        if (!el) return;
        try {
            if ($ && $(el).data('DateTimePicker')) $(el).data('DateTimePicker').destroy();
        } catch (e) {}

        eventTarget = el;
        eventCursor = parseEventDate(el.value);
        eventCursor.setSeconds(0, 0);
        eventViewMonth = new Date(eventCursor.getFullYear(), eventCursor.getMonth(), 1);
        var pop = ensureEventPopover();
        var modal = el.closest('.modal');
        var host = modal || document.body;
        if (pop.parentNode !== host) {
            host.appendChild(pop);
        }
        renderEventPopover();
        positionEventPopover(el);
    }

    function setEventPickerMode(allDay) {
        hideOpenEventPickers();
        document.querySelectorAll('.js-event-datetime, .js-event-start, .js-event-end, #form_start_time, #form_end_time').forEach(function (el) {
            if (el.tagName !== 'INPUT') return;
            el.placeholder = allDay ? 'YYYY-MM-DD' : 'YYYY-MM-DD HH:mm';
            if (!el.value) return;
            el.value = formatEventDate(parseEventDate(el.value), !!allDay);
        });
    }

    function initAllDateFields() {
        document.querySelectorAll(
            'input[type="date"], input[type="datetime-local"], input.datetimepicker, input.js-date, input.js-dob, input.js-event-date, input.js-event-datetime, input.js-event-start, input.js-event-end'
        ).forEach(function (el) {
            bindDateInput(el);
        });
    }

    function dayDiff(a, b) {
        var d1 = new Date(a);
        var d2 = new Date(b);
        if (isNaN(d1) || isNaN(d2)) return 0;
        var ms = Math.abs(d2.setHours(0, 0, 0, 0) - d1.setHours(0, 0, 0, 0));
        return Math.round(ms / 86400000);
    }

    function datePart(val) {
        if (!val) return '';
        return String(val).slice(0, 10);
    }

    function ensureHint(el, text, isError) {
        var host = el.closest('.mdp-field, .form-group, .mb-3') || el.parentElement;
        if (!host) return;
        var hint = host.querySelector('.mdp-hint');
        if (!hint) {
            hint = document.createElement('small');
            hint.className = 'mdp-hint';
            host.appendChild(hint);
        }
        hint.textContent = text || '';
        hint.classList.toggle('is-error', !!isError);
        hint.style.display = text ? 'block' : 'none';
    }

    function bindEventSpanLimits() {
        var starts = document.querySelectorAll(
            'input[name="start_time"], #form_start_time, .js-event-start'
        );
        var ends = document.querySelectorAll(
            'input[name="end_time"], #form_end_time, .js-event-end'
        );

        function checkPair(startEl, endEl) {
            if (!startEl || !endEl) return;
            var s = datePart(startEl.value);
            var e = datePart(endEl.value);
            if (!s || !e) {
                ensureHint(endEl, 'Normal events may span up to 3 days.', false);
                return;
            }
            var diff = dayDiff(s, e);
            if (diff > 2) {
                ensureHint(endEl, 'Event cannot span more than 3 days. Please shorten the range.', true);
                endEl.setCustomValidity('Event cannot span more than 3 days.');
            } else {
                ensureHint(endEl, 'Selected span: ' + (diff + 1) + ' day(s).', false);
                endEl.setCustomValidity('');
            }
        }

        starts.forEach(function (startEl) {
            if (startEl.dataset.mdpSpanBound === '1') return;
            var form = startEl.form || startEl.closest('form') || document;
            var endEl = form.querySelector('input[name="end_time"], #form_end_time, .js-event-end');
            if (!endEl) return;

            startEl.dataset.mdpSpanBound = '1';
            startEl.classList.add('js-event-start');
            endEl.classList.add('js-event-end');
            ensureHint(endEl, 'Normal events may span up to 3 days.', false);

            function run() { checkPair(startEl, endEl); }
            startEl.addEventListener('change', run);
            endEl.addEventListener('change', run);
            run();
        });
    }

    function initAcademicYearPickers() {
        document.querySelectorAll('[data-mdp-academic-year]').forEach(function (root) {
            var nameInput = root.querySelector('[data-mdp-year-name]');
            var startSel = root.querySelector('[data-mdp-year-start]');
            var endSel = root.querySelector('[data-mdp-year-end]');
            var preview = root.querySelector('[data-mdp-year-preview]');
            if (!nameInput || !startSel || !endSel) return;

            function applyFromName() {
                var existing = (nameInput.value || '').match(/(\d{4})\s*[–\-]\s*(\d{4})/);
                if (!existing) return;
                if ([].some.call(startSel.options, function (o) { return o.value === existing[1]; })) {
                    startSel.value = existing[1];
                }
                if ([].some.call(endSel.options, function (o) { return o.value === existing[2]; })) {
                    endSel.value = existing[2];
                } else {
                    endSel.value = String(parseInt(existing[1], 10) + 1);
                }
                nameInput.value = startSel.value + '–' + endSel.value;
                if (preview) preview.textContent = 'Academic Year: ' + nameInput.value;
            }

            if (root.dataset.mdpReady === '1') {
                applyFromName();
                return;
            }
            root.dataset.mdpReady = '1';

            var current = new Date().getFullYear();
            var existing = (nameInput.value || '').match(/(\d{4})\s*[–\-]\s*(\d{4})/);
            var startY = existing ? parseInt(existing[1], 10) : current;
            var endY = existing ? parseInt(existing[2], 10) : current + 1;

            startSel.innerHTML = '';
            endSel.innerHTML = '';
            for (var y = current - 5; y <= current + 8; y++) {
                startSel.appendChild(new Option(String(y), String(y), y === startY, y === startY));
                endSel.appendChild(new Option(String(y), String(y), y === endY, y === endY));
            }

            function sync() {
                var s = parseInt(startSel.value, 10);
                var e = parseInt(endSel.value, 10);
                if (e <= s) {
                    e = s + 1;
                    endSel.value = String(e);
                }
                nameInput.value = s + '–' + e;
                if (preview) preview.textContent = 'Academic Year: ' + nameInput.value;
            }

            startSel.addEventListener('change', function () {
                var s = parseInt(startSel.value, 10);
                endSel.value = String(s + 1);
                sync();
            });
            endSel.addEventListener('change', sync);
            sync();
        });
    }

    function scan() {
        initAllDateFields();
        bindEventSpanLimits();
        initAcademicYearPickers();
    }

    function boot() {
        scan();
        // Re-scan when modals open (dynamic forms)
        document.addEventListener('shown.bs.modal', function () {
            setTimeout(scan, 50);
        });
        document.addEventListener('hide.bs.modal', hideOpenEventPickers);
        window.addEventListener('resize', function () {
            if (eventTarget) positionEventPopover(eventTarget);
            else hideOpenEventPickers();
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }

    // Expose for pages that inject fields later
    window.ModernDatepicker = {
        refresh: scan,
        setEventPickerMode: setEventPickerMode,
        toApiValue: function (val) {
            if (!val) return '';
            val = String(val).trim();
            if (/^\d{4}-\d{2}-\d{2}$/.test(val)) return val + 'T00:00';
            if (/^\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}/.test(val)) return val.replace(' ', 'T').slice(0, 16);
            return val;
        },
        setValue: function (selector, value) {
            var el = typeof selector === 'string' ? document.querySelector(selector) : selector;
            if (!el) return;
            var v = value || '';
            if (/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}/.test(v)) {
                v = v.replace('T', ' ').slice(0, 16);
            }
            el.value = v;
        }
    };
})(window, document, window.jQuery);
