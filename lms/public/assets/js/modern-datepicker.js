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
        if (!el || el.closest('.mdp-field')) return;
        var parent = el.parentElement;
        if (parent && (parent.classList.contains('form-group') || parent.classList.contains('mb-3') || parent.classList.contains('calendar-icon'))) {
            parent.classList.add('mdp-field', 'mdp-ready');
        } else {
            var wrap = document.createElement('div');
            wrap.className = 'mdp-field mdp-ready';
            el.parentNode.insertBefore(wrap, el);
            wrap.appendChild(el);
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

    function enhanceNativeDate(el) {
        if (el.dataset.mdpReady === '1') return;

        // Event datetimes use bootstrap picker (text), not native datetime-local
        if (el.classList.contains('js-event-datetime') || el.classList.contains('js-event-start') || el.classList.contains('js-event-end')) {
            if (el.type === 'datetime-local' || el.type === 'date') {
                convertEventDatetimeInput(el);
            }
            return;
        }

        el.dataset.mdpReady = '1';

        wrapField(el);
        blockManualTyping(el);

        if (isDobField(el)) {
            el.classList.add('js-dob');
            if (!el.getAttribute('max')) el.setAttribute('max', TODAY);
            if (!el.getAttribute('min')) el.setAttribute('min', '1950-01-01');
        }

        if (isEventField(el)) {
            el.classList.add('js-event-date');
        }

        el.addEventListener('focus', function () { openPicker(el); });
        el.addEventListener('click', function () { openPicker(el); });
    }

    function convertEventDatetimeInput(el) {
        if (!el || el.tagName !== 'INPUT') return;
        if (el.dataset.mdpEvt === '1') {
            if (!$(el).data('mdp-boot')) initOneEventDatetime($(el));
            return;
        }
        el.dataset.mdpEvt = '1';

        var raw = (el.value || '').trim();
        // Normalize datetime-local value to picker format
        if (/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}/.test(raw)) {
            raw = raw.replace('T', ' ').slice(0, 16);
        }

        el.setAttribute('type', 'text');
        el.classList.add('js-event-datetime', 'datetimepicker');
        el.readOnly = true;
        el.placeholder = 'YYYY-MM-DD HH:mm';
        el.value = raw;

        wrapField(el);
        blockManualTyping(el);
        initOneEventDatetime($(el));
    }

    function pickerIcons() {
        return {
            time: 'fas fa-clock',
            date: 'fas fa-calendar',
            up: 'fas fa-angle-up',
            down: 'fas fa-angle-down',
            previous: 'fas fa-angle-left',
            next: 'fas fa-angle-right',
            today: 'fas fa-crosshairs',
            clear: 'fas fa-trash',
            close: 'fas fa-times'
        };
    }

    function initOneEventDatetime($el) {
        if (!$ || !$.fn.datetimepicker || !$el.length) return;
        if ($el.data('mdp-boot')) return;
        $el.data('mdp-boot', 1);

        try {
            if ($el.data('DateTimePicker')) $el.data('DateTimePicker').destroy();
        } catch (e) {}

        var allDay = !!(document.getElementById('form_is_all_day') || document.getElementById('is_all_day'));
        var isAllDayChecked = false;
        var allDayEl = document.getElementById('form_is_all_day') || document.getElementById('is_all_day');
        if (allDayEl) isAllDayChecked = !!allDayEl.checked;

        $el.datetimepicker({
            format: isAllDayChecked ? 'YYYY-MM-DD' : 'YYYY-MM-DD HH:mm',
            sideBySide: !isAllDayChecked,
            stepping: 5,
            useCurrent: false,
            allowInputToggle: true,
            ignoreReadonly: true,
            showTodayButton: true,
            showClear: true,
            toolbarPlacement: 'bottom',
            icons: pickerIcons(),
            widgetPositioning: { horizontal: 'auto', vertical: 'auto' }
        });

        $el.off('focus.mdpEvt click.mdpEvt').on('focus.mdpEvt click.mdpEvt', function () {
            try { $el.data('DateTimePicker').show(); } catch (err) {}
        });

        $el.off('dp.change.mdpEvt').on('dp.change.mdpEvt', function () {
            $el.trigger('change');
        });
    }

    function setEventPickerMode(allDay) {
        $('.js-event-datetime, .js-event-start, .js-event-end').each(function () {
            var $el = $(this);
            if (!$el.data('DateTimePicker')) return;
            var current = $el.data('DateTimePicker').date();
            $el.data('DateTimePicker').destroy();
            $el.removeData('mdp-boot');
            $el.datetimepicker({
                format: allDay ? 'YYYY-MM-DD' : 'YYYY-MM-DD HH:mm',
                sideBySide: !allDay,
                stepping: 5,
                useCurrent: false,
                allowInputToggle: true,
                ignoreReadonly: true,
                showTodayButton: true,
                showClear: true,
                toolbarPlacement: 'bottom',
                icons: pickerIcons()
            });
            $el.data('mdp-boot', 1);
            if (current) {
                try {
                    $el.data('DateTimePicker').date(allDay ? current.clone().startOf('day') : current);
                } catch (e) {}
            }
        });
    }

    function initBootstrapDatepickers() {
        if (!$ || !$.fn.datetimepicker) return;

        // Convert any leftover native event datetime fields
        document.querySelectorAll('.js-event-datetime, .js-event-start, .js-event-end, #form_start_time, #form_end_time').forEach(function (el) {
            if (el.tagName === 'INPUT') convertEventDatetimeInput(el);
        });

        // DOB / date-only fields that still use .datetimepicker text inputs
        $('.datetimepicker').not('.js-event-datetime, .js-event-start, .js-event-end').each(function () {
            var $el = $(this);
            var el = this;
            if ($el.data('mdp-boot')) return;
            $el.data('mdp-boot', 1);

            // Qualification must NOT be a datepicker
            if ((el.getAttribute('name') || '') === 'qualification') {
                $el.removeClass('datetimepicker');
                el.placeholder = el.placeholder && el.placeholder.indexOf('DD-MM') !== -1 ? 'e.g. BSEd, MA Ed' : el.placeholder;
                return;
            }

            wrapField(el);
            blockManualTyping(el);
            el.readOnly = true;

            var opts = {
                format: 'YYYY-MM-DD',
                useCurrent: false,
                allowInputToggle: true,
                ignoreReadonly: true,
                showTodayButton: true,
                showClear: true,
                icons: pickerIcons()
            };

            if (isDobField(el)) {
                $el.addClass('js-dob');
                opts.maxDate = moment();
                opts.minDate = moment('1950-01-01');
                opts.viewMode = 'years';
                opts.widgetPositioning = { horizontal: 'auto', vertical: 'bottom' };
            }

            // Convert legacy DD-MM-YYYY values if present
            var raw = ($el.val() || '').trim();
            if (/^\d{2}-\d{2}-\d{4}$/.test(raw)) {
                var parts = raw.split('-');
                $el.val(parts[2] + '-' + parts[1] + '-' + parts[0]);
            }

            try {
                if ($el.data('DateTimePicker')) {
                    $el.data('DateTimePicker').destroy();
                }
            } catch (e) {}

            $el.datetimepicker(opts);

            $el.on('focus click', function () {
                try { $el.data('DateTimePicker').show(); } catch (err) {}
            });
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
        document.querySelectorAll('input[type="date"], input[type="datetime-local"]').forEach(enhanceNativeDate);
        initBootstrapDatepickers();
        bindEventSpanLimits();
        initAcademicYearPickers();
    }

    function boot() {
        scan();
        // Re-scan when modals open (dynamic forms)
        document.addEventListener('shown.bs.modal', function () {
            setTimeout(scan, 50);
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
            var $el = $(selector);
            if (!$el.length) return;
            var v = value || '';
            if (/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}/.test(v)) {
                v = v.replace('T', ' ').slice(0, 16);
            }
            if ($el.data('DateTimePicker')) {
                try {
                    $el.data('DateTimePicker').date(v ? moment(v, ['YYYY-MM-DD HH:mm', 'YYYY-MM-DD']) : null);
                    return;
                } catch (e) {}
            }
            $el.val(v);
        }
    };
})(window, document, window.jQuery);
